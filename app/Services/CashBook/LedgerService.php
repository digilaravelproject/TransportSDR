<?php

namespace App\Services\CashBook;

use App\Models\CashBookEntry;
use Illuminate\Support\Facades\{DB, File};
use Carbon\Carbon;

class LedgerService
{
    // ── Get current balance ────────────────────────────
    public function getCurrentBalance(): float
    {
        $income  = CashBookEntry::where('entry_type', 'income')
            ->where('status', 'confirmed')
            ->sum('amount');

        $expense = CashBookEntry::where('entry_type', 'expense')
            ->where('status', 'confirmed')
            ->sum('amount');

        return round($income - $expense, 2);
    }

    // ── Create entry with auto balance ─────────────────
    public function createEntry(array $data): CashBookEntry
    {
        return DB::transaction(function () use ($data) {
            $openingBalance = $this->getCurrentBalance();

            $closingBalance = $data['entry_type'] === 'income'
                ? $openingBalance + $data['amount']
                : $openingBalance - $data['amount'];

            return CashBookEntry::create(array_merge($data, [
                'opening_balance' => $openingBalance,
                'closing_balance' => $closingBalance,
            ]));
        });
    }

    // ── Get ledger with date filter ────────────────────
    public function getLedger(array $filters): array
    {
        $from = $filters['from'] ?? now()->startOfMonth()->toDateString();
        $to   = $filters['to']   ?? now()->toDateString();

        $entries = CashBookEntry::where('status', '!=', 'cancelled')
            ->whereBetween('entry_date', [$from, $to])
            ->when($filters['entry_type']   ?? null, fn($q, $v) => $q->where('entry_type', $v))
            ->when($filters['payment_mode'] ?? null, fn($q, $v) => $q->where('payment_mode', $v))
            ->when($filters['category']     ?? null, fn($q, $v) => $q->where('category', $v))
            ->when($filters['search']       ?? null, fn($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('description', 'like', "%{$v}%")
                    ->orWhere('party_name', 'like', "%{$v}%")
                    ->orWhere('reference_number', 'like', "%{$v}%")
                    ->orWhere('transaction_id', 'like', "%{$v}%");
            }))
            ->with('creator')
            ->orderBy('entry_date')
            ->orderBy('id')
            ->get();

        $totalIncome  = $entries->where('entry_type', 'income')->sum('amount');
        $totalExpense = $entries->where('entry_type', 'expense')->sum('amount');

        // Opening balance before this period
        $openingBalance = $this->getBalanceUpTo(
            Carbon::parse($from)->subDay()->toDateString()
        );

        return [
            'period'          => ['from' => $from, 'to' => $to],
            'opening_balance' => round($openingBalance, 2),
            'total_income'    => round($totalIncome, 2),
            'total_expense'   => round($totalExpense, 2),
            'net'             => round($totalIncome - $totalExpense, 2),
            'closing_balance' => round($openingBalance + $totalIncome - $totalExpense, 2),
            'entries'         => $entries,
        ];
    }

    // ── Balance up to a specific date ──────────────────
    public function getBalanceUpTo(string $date): float
    {
        $income  = CashBookEntry::where('entry_type', 'income')
            ->where('status', 'confirmed')
            ->whereDate('entry_date', '<=', $date)
            ->sum('amount');

        $expense = CashBookEntry::where('entry_type', 'expense')
            ->where('status', 'confirmed')
            ->whereDate('entry_date', '<=', $date)
            ->sum('amount');

        return round($income - $expense, 2);
    }

    // ── Summary by category ────────────────────────────
    public function getSummaryByCategory(string $from, string $to): array
    {
        $income = CashBookEntry::where('entry_type', 'income')
            ->whereBetween('entry_date', [$from, $to])
            ->where('status', 'confirmed')
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get();

        $expense = CashBookEntry::where('entry_type', 'expense')
            ->whereBetween('entry_date', [$from, $to])
            ->where('status', 'confirmed')
            ->select('category', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('category')
            ->get();

        return [
            'income_by_category'  => $income,
            'expense_by_category' => $expense,
        ];
    }

    // ── Monthly summary ────────────────────────────────
    public function getMonthlySummary(int $months = 6): array
    {
        $result = [];

        for ($i = $months - 1; $i >= 0; $i--) {
            $date    = now()->subMonths($i);
            $from    = $date->copy()->startOfMonth()->toDateString();
            $to      = $date->copy()->endOfMonth()->toDateString();
            $income  = CashBookEntry::where('entry_type', 'income')->whereBetween('entry_date', [$from, $to])->where('status', 'confirmed')->sum('amount');
            $expense = CashBookEntry::where('entry_type', 'expense')->whereBetween('entry_date', [$from, $to])->where('status', 'confirmed')->sum('amount');

            $result[] = [
                'month'   => $date->format('M Y'),
                'income'  => round($income, 2),
                'expense' => round($expense, 2),
                'net'     => round($income - $expense, 2),
            ];
        }

        return $result;
    }

    // ── Upload receipt ─────────────────────────────────
    public function uploadReceipt(CashBookEntry $entry, $file, int $tenantId): string
    {
        $dir = storage_path(
            'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR .
                'tenants' . DIRECTORY_SEPARATOR . $tenantId . DIRECTORY_SEPARATOR . 'receipts'
        );

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        $fileName = "receipt-{$entry->id}-" . time() . '.' . $file->extension();
        $path     = "tenants/{$tenantId}/receipts/{$fileName}";
        $file->storeAs("public/tenants/{$tenantId}/receipts", $fileName);

        $entry->update(['receipt_path' => $path]);

        return $path;
    }
}
