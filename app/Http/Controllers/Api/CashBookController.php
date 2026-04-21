<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{CashBookEntry, OnlinePayment, PaymentQr};
use App\Services\CashBook\CashBookService;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Exception;

class CashBookController extends Controller
{
    public function __construct(private CashBookService $cashBook) {}

    // ─────────────────────────────────────────────────
    // GET /api/v1/cashbook
    // All entries list
    // ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $request->validate([
            'from'         => 'nullable|date',
            'to'           => 'nullable|date|after_or_equal:from',
            'entry_type'   => 'nullable|in:income,expense',
            'payment_mode' => 'nullable|string',
            'category'     => 'nullable|string',
            'search'       => 'nullable|string|max:100',
        ]);

        try {
            $ledger = $this->cashBook->getLedger($request->only([
                'from',
                'to',
                'entry_type',
                'payment_mode',
                'category',
                'search',
            ]));

            return response()->json([
                'success' => true,
                'data'    => $ledger,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching the cashbook entries.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/cashbook
    // Create cash book entry
    // ─────────────────────────────────────────────────
    public function store(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $data = $request->validate([
            'entry_type'       => 'required|in:income,expense',
            'payment_mode'     => 'required|in:cash,online,cheque,upi,bank_transfer,neft,rtgs,imps',
            'category'         => 'required|string',
            'amount'           => 'required|numeric|min:0.01',
            'description'      => 'required|string|max:255',
            'entry_date'       => 'required|date',
            'party_name'       => 'nullable|string|max:255',
            'party_contact'    => 'nullable|string|max:15',
            'reference_type'   => 'nullable|string|max:100',
            'reference_id'     => 'nullable|integer',
            'reference_number' => 'nullable|string|max:100',
            'transaction_id'   => 'nullable|string|max:100',
            'bank_name'        => 'nullable|string|max:100',
            'cheque_number'    => 'nullable|string|max:50',
            'cheque_date'      => 'nullable|date',
            'status'           => 'nullable|in:confirmed,pending,bounced,cancelled',
            'notes'            => 'nullable|string',
        ], [
            'entry_type.required'  => 'Entry type is required.',
            'payment_mode.required' => 'Payment mode is required.',
            'category.required'    => 'Category is required.',
            'amount.required'      => 'Amount is required.',
            'description.required' => 'Description is required.',
            'entry_date.required'  => 'Entry date is required.',
        ]);

        try {
            $entry = $this->cashBook->createEntry($data);

            return response()->json([
                'success' => true,
                'message' => 'Cash book entry added successfully.',
                'data'    => [
                    'entry'           => $entry,
                    'current_balance' => $this->cashBook->getCurrentBalance(),
                ],
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while creating the entry.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/cashbook/{id}
    // Single entry
    // ─────────────────────────────────────────────────
    public function show(CashBookEntry $entry)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            return response()->json([
                'success' => true,
                'data'    => $entry->load('creator'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching the entry details.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // PUT /api/v1/cashbook/{id}
    // Update entry
    // ─────────────────────────────────────────────────
    public function update(Request $request, CashBookEntry $entry)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        if ($entry->status === 'cancelled') {
            return response()->json([
                'success' => false,
                'message' => 'Cancelled entry cannot be updated.',
            ], 422);
        }

        $data = $request->validate([
            'description'   => 'sometimes|string|max:255',
            'party_name'    => 'nullable|string|max:255',
            'party_contact' => 'nullable|string|max:15',
            'transaction_id' => 'nullable|string|max:100',
            'cheque_number' => 'nullable|string|max:50',
            'cheque_date'   => 'nullable|date',
            'status'        => 'sometimes|in:confirmed,pending,bounced,cancelled',
            'notes'         => 'nullable|string',
        ]);

        try {
            $entry->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Entry updated successfully.',
                'data'    => $entry->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating the entry.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // DELETE /api/v1/cashbook/{id}
    // Soft delete
    // ─────────────────────────────────────────────────
    public function destroy(CashBookEntry $entry)
    {
        $this->checkRole(['superadmin', 'admin']);

        try {
            $entry->update(['status' => 'cancelled']);
            $entry->delete();

            return response()->json([
                'success' => true,
                'message' => 'Entry cancelled and deleted.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while deleting the entry.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/cashbook/ledger
    // Full ledger view with balance
    // ─────────────────────────────────────────────────
    public function ledger(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        try {
            return response()->json([
                'success' => true,
                'data'    => [
                    'ledger'          => $this->cashBook->getLedger(['from' => $from, 'to' => $to]),
                    'monthly_summary' => $this->cashBook->getMonthlySummary(6),
                    'category_wise'   => $this->cashBook->getSummaryByCategory($from, $to),
                    'current_balance' => $this->cashBook->getCurrentBalance(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching the ledger.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/cashbook/balance
    // Current balance
    // ─────────────────────────────────────────────────
    public function balance()
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            return response()->json([
                'success' => true,
                'data'    => [
                    'current_balance' => $this->cashBook->getCurrentBalance(),
                    'as_of'           => now()->format('d-m-Y H:i'),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching the balance.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/cashbook/{id}/receipt
    // Upload receipt image
    // ─────────────────────────────────────────────────
    public function uploadReceipt(Request $request, CashBookEntry $entry)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $request->validate([
            'receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ], [
            'receipt.required' => 'Receipt file is required.',
            'receipt.max'      => 'File size must not exceed 5MB.',
        ]);

        try {
            $path = $this->cashBook->uploadReceipt(
                $entry,
                $request->file('receipt'),
                auth()->user()->tenant_id
            );

            return response()->json([
                'success'     => true,
                'message'     => 'Receipt uploaded successfully.',
                'receipt_url' => asset("storage/{$path}"),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while uploading the receipt.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/cashbook/online-payments
    // Online payment tracker
    // ─────────────────────────────────────────────────
    public function onlinePayments(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $request->validate([
            'gateway' => 'nullable|string',
            'status'  => 'nullable|in:pending,success,failed,refunded,partially_refunded',
            'from'    => 'nullable|date',
            'to'      => 'nullable|date',
            'search'  => 'nullable|string|max:100',
        ]);

        $from = $request->from ?? now()->startOfMonth()->toDateString();
        $to   = $request->to   ?? now()->toDateString();

        try {
            $payments = $this->cashBook->getOnlinePayments(
                $request->only(['gateway', 'status', 'from', 'to', 'search']),
                $request->per_page ?? 20
            );

            return response()->json([
                'success' => true,
                'data'    => [
                    'summary'  => $this->cashBook->getGatewaySummary($from, $to),
                    'payments' => $payments,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching online payments.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/cashbook/online-payments
    // Record new online payment
    // ─────────────────────────────────────────────────
    public function recordOnlinePayment(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $data = $request->validate([
            'gateway'          => 'required|in:razorpay,paytm,phonepe,googlepay,upi_direct,neft,rtgs,imps,bank_transfer,other',
            'amount'           => 'required|numeric|min:0.01',
            'reference_type'   => 'nullable|string|max:100',
            'reference_id'     => 'nullable|integer',
            'reference_number' => 'nullable|string|max:100',
            'transaction_id'   => 'nullable|string|max:100',
            'gateway_order_id' => 'nullable|string|max:100',
            'payer_name'       => 'nullable|string|max:255',
            'payer_contact'    => 'nullable|string|max:15',
            'payer_upi_id'     => 'nullable|string|max:100',
            'payer_bank'       => 'nullable|string|max:100',
            'status'           => 'required|in:pending,success,failed',
            'paid_at'          => 'nullable|date',
            'notes'            => 'nullable|string',
        ], [
            'gateway.required' => 'Payment gateway is required.',
            'amount.required'  => 'Amount is required.',
            'status.required'  => 'Status is required.',
        ]);

        try {
            $payment = $this->cashBook->recordOnlinePayment($data);

            return response()->json([
                'success' => true,
                'message' => "Online payment of ₹{$payment->amount} recorded via {$payment->gateway}.",
                'data'    => $payment,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while recording the payment.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // PATCH /api/v1/cashbook/online-payments/{id}/status
    // Update online payment status
    // ─────────────────────────────────────────────────
    public function updateOnlinePaymentStatus(Request $request, OnlinePayment $payment)
    {

        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $data = $request->validate([
            'status'             => 'required|in:pending,success,failed,refunded',
            'transaction_id'     => 'nullable|string|max:100',
            'gateway_payment_id' => 'nullable|string|max:100',
            'paid_at'            => 'nullable|date',
            'failure_reason'     => 'nullable|string',
            'gateway_response'   => 'nullable|string',
        ]);

        try {
            $payment = $this->cashBook->updatePaymentStatus(
                $payment,
                $data['status'],
                Arr::except($data ?? [], ['status'])
            );
            // print_r($payment);
            // die;
            return response()->json([
                'success' => true,
                'message' => "Payment status updated to: {$payment->status}.",
                'data'    => $payment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while updating the payment status.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/cashbook/online-payments/{id}/refund
    // Refund payment
    // ─────────────────────────────────────────────────
    public function refundOnlinePayment(Request $request, OnlinePayment $payment)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        $data = $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $payment->amount,
        ], [
            'amount.max' => "Refund cannot exceed ₹{$payment->amount}.",
        ]);

        try {
            $payment = $this->cashBook->refundPayment($payment, $data['amount']);

            return response()->json([
                'success' => true,
                'message' => "Refund of ₹{$data['amount']} processed.",
                'data'    => $payment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while processing the refund.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/cashbook/qr/generate
    // Generate UPI QR code
    // ─────────────────────────────────────────────────
    // public function generateQr(Request $request)
    // {
    //     $this->checkRole(['superadmin', 'admin', 'accountant', 'operator']);

    //     $data = $request->validate([
    //         'qr_type'          => 'required|in:trip_payment,advance_collection,corporate_payment,general',
    //         'upi_id'           => 'required|string|max:100',
    //         'payee_name'       => 'required|string|max:255',
    //         'amount'           => 'nullable|numeric|min:1',
    //         'transaction_note' => 'nullable|string|max:255',
    //         'reference_type'   => 'nullable|string|max:100',
    //         'reference_id'     => 'nullable|integer',
    //         'reference_number' => 'nullable|string|max:100',
    //         'expires_at'       => 'nullable|date|after:now',
    //         'send_alert'       => 'boolean',
    //         'alert_contact'    => 'nullable|string|max:15|required_if:send_alert,true',
    //     ], [
    //         'upi_id.required'       => 'UPI ID is required.',
    //         'payee_name.required'   => 'Payee name is required.',
    //         'qr_type.required'      => 'QR type is required.',
    //         'alert_contact.required_if' => 'Alert contact is required when send alert is enabled.',
    //     ]);

    //     $qr = $this->cashBook->generateQr($data, auth()->user()->tenant_id);

    //     return response()->json([
    //         'success'    => true,
    //         'message'    => 'QR generated successfully.',
    //         'data'       => [
    //             'id'           => $qr->id,
    //             'upi_deep_link' => $qr->upi_deep_link,
    //             'qr_url'       => $qr->qr_url,
    //             'amount'       => $qr->amount,
    //             'expires_at'   => $qr->expires_at?->format('d-m-Y H:i'),
    //             'is_active'    => $qr->is_active,
    //             'alert_sent'   => $qr->alert_sent,
    //         ],
    //     ], 201);
    // }
    public function generateQr(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant', 'operator']);

        try {

            $data = $request->validate([
                'qr_type'          => 'required|in:trip_payment,advance_collection,corporate_payment,general',
                'upi_id'           => 'required|string|max:100',
                'payee_name'       => 'required|string|max:255',
                'amount'           => 'nullable|numeric|min:1',
                'transaction_note' => 'nullable|string|max:255',
                'reference_type'   => 'nullable|string|max:100',
                'reference_id'     => 'nullable|integer',
                'reference_number' => 'nullable|string|max:100',
                'expires_at'       => 'nullable|date|after:now',
                'send_alert'       => 'boolean',
                'alert_contact'    => 'nullable|string|max:15|required_if:send_alert,true',
            ], [
                'upi_id.required'       => 'UPI ID is required.',
                'payee_name.required'   => 'Payee name is required.',
                'qr_type.required'      => 'QR type is required.',
                'expires_at.after'      => 'Expiry must be a future date.',
                'alert_contact.required_if' => 'Alert contact is required when send alert is enabled.',
            ]);

            $qr = $this->cashBook->generateQr($data, auth()->user()->tenant_id);

            return response()->json([
                'success' => true,
                'message' => 'QR generated successfully.',
                'data'    => [
                    'id'            => $qr->id,
                    'upi_deep_link' => $qr->upi_deep_link,
                    'qr_url'        => $qr->qr_url,
                    'amount'        => $qr->amount,
                    'expires_at'    => $qr->expires_at?->format('d-m-Y H:i'),
                    'is_active'     => $qr->is_active,
                    'alert_sent'    => $qr->alert_sent,
                ],
            ], 201);
        } catch (ValidationException $e) {

            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors'  => $e->errors(),
            ], 422);
        } catch (Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Something went wrong.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }
    // ─────────────────────────────────────────────────
    // GET /api/v1/cashbook/qr
    // List QR codes
    // ─────────────────────────────────────────────────
    public function listQr(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant', 'operator']);

        try {
            $qrs = PaymentQr::when($request->is_active, fn($q, $v) => $q->where('is_active', (bool)$v))
                ->when($request->qr_type, fn($q, $v) => $q->where('qr_type', $v))
                ->latest()
                ->paginate($request->per_page ?? 20);

            return response()->json([
                'success' => true,
                'data'    => $qrs,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching QRs.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/cashbook/qr/{id}
    // Single QR details
    // ─────────────────────────────────────────────────
    public function showQr(PaymentQr $qr)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant', 'operator']);

        try {
            return response()->json([
                'success' => true,
                'data'    => [
                    'qr'           => $qr,
                    'qr_url'       => $qr->qr_url,
                    'is_expired'   => $qr->isExpired(),
                    'upi_deep_link' => $qr->upi_deep_link,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while fetching QR details.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // PATCH /api/v1/cashbook/qr/{id}/deactivate
    // Deactivate QR
    // ─────────────────────────────────────────────────
    public function deactivateQr(PaymentQr $qr)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        try {
            $qr = $this->cashBook->deactivateQr($qr);

            return response()->json([
                'success' => true,
                'message' => 'QR deactivated successfully.',
                'data'    => $qr,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while deactivating QR.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/cashbook/qr/{id}/send-alert
    // Resend QR alert
    // ─────────────────────────────────────────────────
    public function sendQrAlert(Request $request, PaymentQr $qr)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant', 'operator']);

        $request->validate([
            'contact' => 'nullable|string|max:15',
        ]);

        try {
            if ($request->contact) {
                $qr->update(['alert_contact' => $request->contact]);
            }

            $this->cashBook->sendQrAlert($qr->fresh());

            return response()->json([
                'success' => true,
                'message' => "QR alert sent to {$qr->fresh()->alert_contact}.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while sending the QR alert.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/cashbook/upi-link
    // Generate UPI deep link (no QR image)
    // ─────────────────────────────────────────────────
    public function generateUpiLink(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'accountant', 'operator']);

        $data = $request->validate([
            'upi_id'   => 'required|string',
            'name'     => 'required|string',
            'amount'   => 'nullable|numeric|min:1',
            'note'     => 'nullable|string|max:255',
        ]);

        try {
            $link = $this->cashBook->buildUpiLink(
                $data['upi_id'],
                $data['name'],
                $data['amount'] ?? null,
                $data['note']   ?? null
            );

            return response()->json([
                'success'       => true,
                'data'          => [
                    'upi_deep_link' => $link,
                    'qr_api_url'    => "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($link),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An unexpected error occurred while generating the UPI link.',
                'error'   => $e->getMessage(),
            ], 500);
        }
    }

    private function checkRole(array $roles): void
    {
        if (!auth()->user()->hasRole($roles)) {
            abort(403, 'You do not have permission for this action.');
        }
    }
}
