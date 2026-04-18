<?php

namespace App\Services\CashBook;

use App\Models\{CashBookEntry, OnlinePayment, PaymentQr};

class CashBookService
{
    public function __construct(
        private LedgerService        $ledger,
        private OnlinePaymentService $onlinePayment,
        private QrService            $qr,
    ) {}

    // Ledger
    public function createEntry(array $data): CashBookEntry
    {
        return $this->ledger->createEntry($data);
    }
    public function getLedger(array $filters): array
    {
        return $this->ledger->getLedger($filters);
    }
    public function getCurrentBalance(): float
    {
        return $this->ledger->getCurrentBalance();
    }
    public function getMonthlySummary(int $months = 6): array
    {
        return $this->ledger->getMonthlySummary($months);
    }
    public function getSummaryByCategory(string $from, string $to)
    {
        return $this->ledger->getSummaryByCategory($from, $to);
    }
    public function uploadReceipt(CashBookEntry $e, $file, int $tid)
    {
        return $this->ledger->uploadReceipt($e, $file, $tid);
    }

    // Online payment
    public function recordOnlinePayment(array $data): OnlinePayment
    {
        return $this->onlinePayment->record($data);
    }
    public function updatePaymentStatus(OnlinePayment $p, string $s, array $extra = [])
    {
        return $this->onlinePayment->updateStatus($p, $s, $extra);
    }
    public function refundPayment(OnlinePayment $p, float $amount): OnlinePayment
    {
        return $this->onlinePayment->refund($p, $amount);
    }
    public function getOnlinePayments(array $filters, int $perPage = 20)
    {
        return $this->onlinePayment->getTracker($filters, $perPage);
    }
    public function getGatewaySummary(string $from, string $to): array
    {
        return $this->onlinePayment->getGatewaySummary($from, $to);
    }

    // QR
    public function generateQr(array $data, int $tenantId): PaymentQr
    {
        return $this->qr->generate($data, $tenantId);
    }
    public function deactivateQr(PaymentQr $qr): PaymentQr
    {
        return $this->qr->deactivate($qr);
    }
    public function sendQrAlert(PaymentQr $qr): void
    {
        $this->qr->sendAlert($qr);
    }
    public function buildUpiLink(string $upiId, string $name, ?float $amount, ?string $note): string
    {
        return $this->qr->buildUpiLink($upiId, $name, $amount, $note);
    }
}
