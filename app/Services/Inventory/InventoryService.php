<?php

namespace App\Services\Inventory;

use App\Models\{InventoryItem, InventoryTransaction, InventoryCategory};
use Illuminate\Support\Facades\DB;

class InventoryService
{
    public function __construct(
        private StockService $stock,
        private AlertService $alert,
    ) {}

    // ── Item CRUD ──────────────────────────────────────
    public function createItem(array $data): InventoryItem
    {
        return InventoryItem::create($data);
    }

    public function updateItem(InventoryItem $item, array $data): InventoryItem
    {
        $item->update($data);
        return $item->fresh(['category', 'vehicle']);
    }

    // ── Stock operations ───────────────────────────────
    public function stockIn(InventoryItem $item, array $data): array
    {
        $txn = $this->stock->stockIn($item, $data);
        $this->alert->checkAndAlert($item->fresh());
        return ['item' => $item->fresh(['category']), 'transaction' => $txn];
    }

    public function stockOut(InventoryItem $item, array $data): array
    {
        $txn = $this->stock->stockOut($item, $data);
        $this->alert->checkAndAlert($item->fresh());
        return ['item' => $item->fresh(['category']), 'transaction' => $txn];
    }

    public function adjust(InventoryItem $item, array $data): array
    {
        $txn = $this->stock->adjust($item, $data);
        $this->alert->checkAndAlert($item->fresh());
        return ['item' => $item->fresh(), 'transaction' => $txn];
    }

    public function returnStock(InventoryItem $item, array $data): array
    {
        $txn = $this->stock->returnStock($item, $data);
        return ['item' => $item->fresh(), 'transaction' => $txn];
    }

    public function markDamaged(InventoryItem $item, array $data): array
    {
        $txn = $this->stock->markDamaged($item, $data);
        $this->alert->checkAndAlert($item->fresh());
        return ['item' => $item->fresh(), 'transaction' => $txn];
    }

    public function uploadDocument(InventoryTransaction $txn, $file, int $tenantId): string
    {
        return $this->stock->uploadDocument($txn, $file, $tenantId);
    }

    // ── Alerts & reports ──────────────────────────────
    public function getLowStockAlerts(): array
    {
        return $this->alert->getLowStockItems();
    }
    public function getValuation(): array
    {
        return $this->alert->getValuation();
    }

    // ── Transaction history ───────────────────────────
    public function getHistory(InventoryItem $item, array $filters, int $perPage = 20)
    {
        return InventoryTransaction::where('item_id', $item->id)
            ->when($filters['type'] ?? null, fn($q, $v) => $q->where('transaction_type', $v))
            ->when($filters['from'] ?? null, fn($q, $v) => $q->whereDate('transaction_date', '>=', $v))
            ->when($filters['to']   ?? null, fn($q, $v) => $q->whereDate('transaction_date', '<=', $v))
            ->with('creator')
            ->latest('transaction_date')
            ->paginate($perPage);
    }

    // ── Category CRUD ──────────────────────────────────
    public function createCategory(array $data): InventoryCategory
    {
        return InventoryCategory::create($data);
    }

    public function getCategories()
    {
        return InventoryCategory::withCount('items')->where('is_active', true)->get();
    }
}
