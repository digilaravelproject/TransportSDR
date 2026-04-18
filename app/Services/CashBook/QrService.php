<?php

namespace App\Services\CashBook;

use App\Models\PaymentQr;
use Illuminate\Support\Facades\{File, Log};

class QrService
{
    // ── Generate UPI QR ────────────────────────────────
    public function generate(array $data, int $tenantId): PaymentQr
    {
        // Build UPI deep link
        $upiLink = $this->buildUpiLink(
            $data['upi_id'],
            $data['payee_name'],
            $data['amount'] ?? null,
            $data['transaction_note'] ?? null
        );

        // Generate QR image
        $qrPath = $this->generateQrImage($upiLink, $tenantId, $data);

        // Create DB record
        $qr = PaymentQr::create(array_merge($data, [
            'upi_deep_link'  => $upiLink,
            'qr_image_path'  => $qrPath,
            'tenant_id'      => $tenantId,
        ]));

        // Send alert if requested
        if (!empty($data['send_alert']) && !empty($data['alert_contact'])) {
            $this->sendAlert($qr);
        }

        return $qr;
    }

    // ── Build UPI deep link ────────────────────────────
    public function buildUpiLink(
        string  $upiId,
        string  $payeeName,
        ?float  $amount = null,
        ?string $note   = null
    ): string {
        $params = [
            'pa' => $upiId,
            'pn' => urlencode($payeeName),
            'cu' => 'INR',
        ];

        if ($amount) {
            $params['am'] = $amount;
        }

        if ($note) {
            $params['tn'] = urlencode($note);
        }

        return 'upi://pay?' . http_build_query($params);
    }

    // ── Generate QR image ──────────────────────────────
    private function generateQrImage(string $upiLink, int $tenantId, array $data): ?string
    {
        $dir = storage_path(
            'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR .
                'tenants' . DIRECTORY_SEPARATOR . $tenantId . DIRECTORY_SEPARATOR . 'payment-qrs'
        );

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        $fileName = 'qr-' . now()->format('YmdHis') . '-' . uniqid() . '.png';
        $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;
        $storagePath = "tenants/{$tenantId}/payment-qrs/{$fileName}";

        // Method 1: Google Charts API (no package needed)
        $qrUrl   = "https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=" . urlencode($upiLink) . "&choe=UTF-8";
        $content = @file_get_contents($qrUrl);

        if ($content) {
            file_put_contents($filePath, $content);
            return $storagePath;
        }

        // Method 2: If simplesoftwareio/simple-qrcode installed
        // \SimpleSoftwareIO\QrCode\Facades\QrCode::format('png')->size(300)->generate($upiLink, $filePath);

        Log::warning("QR image generation failed for UPI: {$upiLink}");
        return null;
    }

    // ── Send alert ─────────────────────────────────────
    public function sendAlert(PaymentQr $qr): void
    {
        // TODO: Connect SMS API (Fast2SMS, MSG91 etc.)
        // Example with Fast2SMS:
        // Http::post('https://www.fast2sms.com/dev/bulkV2', [
        //     'authorization' => config('services.fast2sms.key'),
        //     'message'       => "Payment QR ready. Amount: ₹{$qr->amount}. Link: {$qr->upi_deep_link}",
        //     'language'      => 'english',
        //     'route'         => 'q',
        //     'numbers'       => $qr->alert_contact,
        // ]);

        $qr->update([
            'alert_sent'    => true,
            // 'alert_sent_at' => now(),
        ]);

        Log::info("QR alert sent to {$qr->alert_contact} for ₹{$qr->amount}");
    }

    // ── Deactivate QR ──────────────────────────────────
    public function deactivate(PaymentQr $qr): PaymentQr
    {
        $qr->update(['is_active' => false]);
        return $qr->fresh();
    }
}
