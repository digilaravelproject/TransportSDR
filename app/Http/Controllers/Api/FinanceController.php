<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\CashBookEntry;
use Dompdf\Dompdf;
use Illuminate\Support\Str;

class FinanceController extends Controller
{
    private array $allowedPaymentModes = ['cash','upi','bank_transfer','cheque','other'];

    public function __construct()
    {
        // middleware can be applied as needed
    }

    public function index_old(Request $request)
    {
        // permission checks are handled at middleware level (if configured)

        $entries = CashBookEntry::latest()
            ->when($request->entry_type, fn($q,$v)=>$q->where('entry_type',$v))
            ->paginate($request->per_page ?? 15);

        return response()->json(['success' => true, 'data' => $entries]);
    }

    public function index(Request $request)
    {
        // Base query (with tenant safety)
        $query = CashBookEntry::query()
            ->where('tenant_id', auth()->user()->tenant_id)
            ->when($request->entry_type, fn ($q, $v) => $q->where('entry_type', $v));

        // Paginated entries
        $entries = $query->latest()->paginate($request->per_page ?? 15);

        // Totals calculation (same tenant)
        $totalIn = CashBookEntry::where('tenant_id', auth()->user()->tenant_id)
            ->where('entry_type', 'income')
            ->sum('amount');

        $totalOut = CashBookEntry::where('tenant_id', auth()->user()->tenant_id)
            ->where('entry_type', 'expense')
            ->sum('amount');

        $currentBalance = $totalIn - $totalOut;

        return response()->json([
            'success' => true,

            'summary' => [
                'total_in' => (float) $totalIn,
                'total_out' => (float) $totalOut,
                'current_balance' => (float) $currentBalance,
            ],

            'data' => $entries
        ]);
    }

    public function storeIncome(Request $request)
    {
        return $this->storeEntry($request, 'income');
    }

    public function storeExpense(Request $request)
    {
        return $this->storeEntry($request, 'expense');
    }

    protected function storeEntry(Request $request, string $type)
    {
        // permission checks are handled at middleware level (if configured)

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'entry_date' => 'required|date',
            'category' => 'required|string|max:191',
            'payment_method' => 'required|string',
            'reference_number' => 'nullable|string|max:191',
            'description' => 'nullable|string|max:1000',
        ]);

        if (!in_array($data['payment_method'], $this->allowedPaymentModes)) {
            $data['payment_method'] = 'other';
        }

        $entry = CashBookEntry::create([
            'created_by' => auth()->user()->id ?? null,
            'tenant_id' => auth()->user()->tenant_id ?? null,
            'entry_type' => $type,
            'payment_mode' => $data['payment_method'],
            'category' => $data['category'],
            'amount' => $data['amount'],
            'description' => $data['description'] ?? null,
            'entry_date' => $data['entry_date'],
            'reference_number' => $data['reference_number'] ?? null,
        ]);

        // generate pdf receipt and save path
        try {
            $path = $this->generateReceiptPdf($entry);
            if ($path) {
                $entry->update(['receipt_path' => $path]);
            }
        } catch (\Exception $e) {
            // ignore pdf errors but log if needed
        }

        return response()->json(['success' => true, 'data' => $entry], 201);
    }

    public function show($id)
    {
        try {
            $entry = CashBookEntry::with('creator')
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $entry
            ], 200);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Cash book entry not found'
            ], 404);

        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    protected function generateReceiptPdf(CashBookEntry $entry): ?string
    {
        $html = view('pdf.receipt', ['entry' => $entry])->render();

        $dompdf = new Dompdf();
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->loadHtml($html);
        $dompdf->render();

        $tenant = $entry->tenant_id ?? 'tenant';
        $dir = storage_path('app/public/receipts/' . $tenant);
        if (!is_dir($dir)) mkdir($dir, 0755, true);

        $filename = 'receipt_' . $entry->id . '_' . Str::random(6) . '.pdf';
        $full = $dir . DIRECTORY_SEPARATOR . $filename;

        file_put_contents($full, $dompdf->output());

        // return storage relative path
        return 'receipts/' . $tenant . '/' . $filename;
    }
}
