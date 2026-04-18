<?php

namespace App\Services\Inventory;

use App\Models\InventoryItem;
use Illuminate\Support\Facades\{Log, DB};

class AlertService
{
    // ── Get all low stock items ────────────────────────
    public function getLowStockItems(): array
    {
        $items = InventoryItem::active()
            ->lowStock()
            ->with('category')
            ->get();

        return [
            'out_of_stock'  => $items->filter(fn($i) => $i->isOutOfStock())->values(),
            'low_stock'     => $items->filter(fn($i) => !$i->isOutOfStock() && $i->isLowStock())->values(),
            'reorder_needed' => InventoryItem::active()->needsReorder()->get()->filter(fn($i) => !$i->isLowStock())->values(),
        ];
    }

    // ── Check and send alerts after stock change ───────
    public function checkAndAlert(InventoryItem $item): void
    {
        if ($item->isLowStock() && !$item->low_stock_alert_sent) {
            $this->sendLowStockAlert($item);
            $item->update(['low_stock_alert_sent' => true]);
        }
    }

    // ── Send low stock alert ───────────────────────────
    private function sendLowStockAlert(InventoryItem $item): void
    {
        // TODO: Connect notification system
        // Option 1: Email
        // Mail::to(auth()->user()->email)->send(new LowStockAlert($item));

        // Option 2: Database notification
        // auth()->user()->notify(new LowStockNotification($item));

        Log::info("Low stock alert: {$item->name} — Current: {$item->quantity_in_stock} {$item->unit} | Min: {$item->minimum_stock_level}");
    }

    // ── Inventory valuation ────────────────────────────
    public function getValuation(): array
    {
        $items = InventoryItem::active()->get();

        $byCategory = $items->groupBy(fn($i) => $i->category_id ?? 'uncategorized')
            ->map(fn($group) => [
                'count'       => $group->count(),
                'total_value' => round($group->sum('total_stock_value'), 2),
            ]);

        return [
            'total_items'       => $items->count(),
            'total_value'       => round($items->sum('total_stock_value'), 2),
            'low_stock_count'   => $items->filter(fn($i) => $i->isLowStock())->count(),
            'out_of_stock_count' => $items->filter(fn($i) => $i->isOutOfStock())->count(),
            'by_category'       => $byCategory,
            'by_type'           => $items->groupBy('item_type')
                ->map(fn($g) => ['count' => $g->count(), 'value' => round($g->sum('total_stock_value'), 2)]),
        ];
    }
}
