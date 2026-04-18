<?php

namespace App\Services\CashBook;

use App\Models\{OnlinePayment, CashBookEntry};
use Illuminate\Support\Facades\{DB, Log};

class OnlinePaymentService
{
    public function __construct(private LedgerService $ledger) {}

    // ── Record online payment ──────────────────────────
    public function record(array $data): OnlinePayment
    {
        return DB::transaction(function () use ($data) {
            $payment = OnlinePayment::create($data);

            // If success — auto create cash book entry
            if ($data['status'] === 'success') {
                $this->ledger->createEntry([
                    'entry_type'       => 'income',
                    'payment_mode'     => $this->mapGatewayToMode($data['gateway']),
                    'category'         => $this->mapReferenceToCategory($data['reference_type'] ?? ''),
                    'reference_type'   => $data['reference_type']   ?? null,
                    'reference_id'     => $data['reference_id']     ?? null,
                    'reference_number' => $data['reference_number'] ?? null,
                    'amount'           => $data['amount'],
                    'description'      => "Online payment via {$data['gateway']}: {$data['reference_number']}",
                    'entry_date'       => now()->toDateString(),
                    'party_name'       => $data['payer_name']    ?? null,
                    'party_contact'    => $data['payer_contact'] ?? null,
                    'transaction_id'   => $data['transaction_id'] ?? null,
                    'status'           => 'confirmed',
                ]);
            }

            return $payment;
        });
    }

    // ── Update payment status ──────────────────────────
    public function updateStatus(OnlinePayment $payment, string $status, array $extra = []): OnlinePayment
    {
        return DB::transaction(function () use ($payment, $status, $extra) {
            $old = $payment->status;
            $payment->update(array_merge(['status' => $status], $extra));

            // If changed to success — create ledger entry
            if ($old !== 'success' && $status === 'success') {
                $this->ledger->createEntry([
                    'entry_type'       => 'income',
                    'payment_mode'     => $this->mapGatewayToMode($payment->gateway),
                    'category'         => $this->mapReferenceToCategory($payment->reference_type ?? ''),
                    'reference_type'   => $payment->reference_type,
                    'reference_id'     => $payment->reference_id,
                    'reference_number' => $payment->reference_number,
                    'amount'           => $payment->amount,
                    'description'      => "Online payment confirmed: {$payment->reference_number}",
                    'entry_date'       => now()->toDateString(),
                    'party_name'       => $payment->payer_name,
                    'party_contact'    => $payment->payer_contact,
                    'transaction_id'   => $payment->transaction_id,
                    'status'           => 'confirmed',
                ]);
            }

            return $payment->fresh();
        });
    }

    // ── Refund ─────────────────────────────────────────
    public function refund(OnlinePayment $payment, float $amount): OnlinePayment
    {
        if ($payment->status !== 'success') {
            abort(422, 'Only successful payments can be refunded.');
        }

        if ($amount > $payment->amount) {
            abort(422, 'Refund amount cannot exceed payment amount.');
        }

        return DB::transaction(function () use ($payment, $amount) {
            $newStatus = $amount == $payment->amount ? 'refunded' : 'partially_refunded';

            $payment->update([
                'status'        => $newStatus,
                'refund_amount' => $amount,
            ]);

            // Expense entry for refund
            $this->ledger->createEntry([
                'entry_type'       => 'expense',
                'payment_mode'     => $this->mapGatewayToMode($payment->gateway),
                'category'         => 'other_expense',
                'reference_type'   => $payment->reference_type,
                'reference_id'     => $payment->reference_id,
                'reference_number' => $payment->reference_number,
                'amount'           => $amount,
                'description'      => "Refund: {$payment->reference_number} via {$payment->gateway}",
                'entry_date'       => now()->toDateString(),
                'party_name'       => $payment->payer_name,
                'transaction_id'   => $payment->transaction_id,
                'status'           => 'confirmed',
            ]);

            return $payment->fresh();
        });
    }

    // ── Get tracker with filters ───────────────────────
    public function getTracker(array $filters, int $perPage = 20)
    {
        return OnlinePayment::when($filters['gateway'] ?? null, fn($q, $v) => $q->where('gateway', $v))
            ->when($filters['status']  ?? null, fn($q, $v) => $q->where('status', $v))
            ->when($filters['from']    ?? null, fn($q, $v) => $q->whereDate('created_at', '>=', $v))
            ->when($filters['to']      ?? null, fn($q, $v) => $q->whereDate('created_at', '<=', $v))
            ->when($filters['search']  ?? null, fn($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('transaction_id', 'like', "%{$v}%")
                    ->orWhere('payer_name', 'like', "%{$v}%")
                    ->orWhere('payer_contact', 'like', "%{$v}%")
                    ->orWhere('reference_number', 'like', "%{$v}%");
            }))
            ->latest()
            ->paginate($perPage);
    }

    // ── Gateway summary ────────────────────────────────
    public function getGatewaySummary(string $from, string $to): array
    {
        return OnlinePayment::where('status', 'success')
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw('gateway, COUNT(*) as count, SUM(amount) as total')
            ->groupBy('gateway')
            ->get()
            ->toArray();
    }

    // ── Helpers ────────────────────────────────────────
    private function mapGatewayToMode(string $gateway): string
    {
        return match ($gateway) {
            'razorpay', 'paytm', 'phonepe', 'googlepay' => 'online',
            'upi_direct'    => 'upi',
            'neft'          => 'neft',
            'rtgs'          => 'rtgs',
            'imps'          => 'imps',
            'bank_transfer' => 'bank_transfer',
            default         => 'online',
        };
    }

    private function mapReferenceToCategory(string $refType): string
    {
        return match ($refType) {
            'Trip'      => 'trip_payment',
            'Lead'      => 'advance_received',
            'Corporate' => 'corporate_payment',
            default     => 'other_income',
        };
    }
}
