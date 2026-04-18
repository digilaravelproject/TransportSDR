<?php

namespace App\Services\Inventory;

use App\Models\{InventoryItem, InventoryTransaction};
use Illuminate\Support\Facades\{DB, File};

class StockService
{
    // ── Stock In ───────────────────────────────────────
    public function stockIn(InventoryItem $item, array $data): InventoryTransaction
    {
        return DB::transaction(function () use ($item, $data) {
            $stockBefore = $item->quantity_in_stock;
            $stockAfter  = $stockBefore + $data['quantity'];

            // Update item stock
            $item->update([
                'quantity_in_stock'    => $stockAfter,
                'purchase_price'       => $data['unit_price'] ?? $item->purchase_price,
                'vendor_name'          => $data['vendor_name']    ?? $item->vendor_name,
                'vendor_contact'       => $data['vendor_contact'] ?? $item->vendor_contact,
                'last_restocked_at'    => now(),
                'low_stock_alert_sent' => false, // reset alert
            ]);

            return InventoryTransaction::create([
                'item_id'          => $item->id,
                'transaction_type' => 'stock_in',
                'quantity'         => $data['quantity'],
                'stock_before'     => $stockBefore,
                'stock_after'      => $stockAfter,
                'unit_price'       => $data['unit_price']      ?? 0,
                'vendor_name'      => $data['vendor_name']     ?? null,
                'vendor_contact'   => $data['vendor_contact']  ?? null,
                'invoice_number'   => $data['invoice_number']  ?? null,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'received_by'      => $data['received_by']     ?? auth()->user()->name,
                'storage_location' => $data['storage_location'] ?? $item->storage_location,
                'reference_type'   => $data['reference_type']  ?? null,
                'reference_id'     => $data['reference_id']    ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'notes'            => $data['notes']            ?? null,
            ]);
        });
    }

    // ── Stock Out ──────────────────────────────────────
    public function stockOut(InventoryItem $item, array $data): InventoryTransaction
    {
        if ($item->quantity_in_stock < $data['quantity']) {
            abort(422, "Insufficient stock. Available: {$item->quantity_in_stock} {$item->unit}");
        }

        return DB::transaction(function () use ($item, $data) {
            $stockBefore = $item->quantity_in_stock;
            $stockAfter  = $stockBefore - $data['quantity'];

            $item->update([
                'quantity_in_stock' => $stockAfter,
                'last_used_at'      => now(),
            ]);

            $txn = InventoryTransaction::create([
                'item_id'          => $item->id,
                'transaction_type' => 'stock_out',
                'quantity'         => $data['quantity'],
                'stock_before'     => $stockBefore,
                'stock_after'      => $stockAfter,
                'unit_price'       => $data['unit_price']      ?? $item->purchase_price,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'issued_to'        => $data['issued_to']        ?? null,
                'reason'           => $data['reason']           ?? null,
                'reference_type'   => $data['reference_type']   ?? null,
                'reference_id'     => $data['reference_id']     ?? null,
                'reference_number' => $data['reference_number'] ?? null,
                'notes'            => $data['notes']            ?? null,
            ]);

            return $txn;
        });
    }

    // ── Manual Adjustment ─────────────────────────────
    public function adjust(InventoryItem $item, array $data): InventoryTransaction
    {
        return DB::transaction(function () use ($item, $data) {
            $stockBefore = $item->quantity_in_stock;
            $newQty      = $data['new_quantity'];
            $diff        = $newQty - $stockBefore;

            $item->update(['quantity_in_stock' => $newQty]);

            return InventoryTransaction::create([
                'item_id'          => $item->id,
                'transaction_type' => 'adjustment',
                'quantity'         => abs($diff),
                'stock_before'     => $stockBefore,
                'stock_after'      => $newQty,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'reason'           => $data['reason'] ?? 'Manual stock adjustment',
                'notes'            => $data['notes']  ?? null,
            ]);
        });
    }

    // ── Return Stock ───────────────────────────────────
    public function returnStock(InventoryItem $item, array $data): InventoryTransaction
    {
        return DB::transaction(function () use ($item, $data) {
            $stockBefore = $item->quantity_in_stock;
            $stockAfter  = $stockBefore + $data['quantity'];

            $item->update(['quantity_in_stock' => $stockAfter]);

            return InventoryTransaction::create([
                'item_id'          => $item->id,
                'transaction_type' => 'return',
                'quantity'         => $data['quantity'],
                'stock_before'     => $stockBefore,
                'stock_after'      => $stockAfter,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'reason'           => $data['reason']           ?? 'Stock returned',
                'issued_to'        => $data['returned_by']      ?? null,
                'notes'            => $data['notes']            ?? null,
            ]);
        });
    }

    // ── Mark Damaged ──────────────────────────────────
    public function markDamaged(InventoryItem $item, array $data): InventoryTransaction
    {
        if ($item->quantity_in_stock < $data['quantity']) {
            abort(422, "Cannot mark more than available stock as damaged.");
        }

        return DB::transaction(function () use ($item, $data) {
            $stockBefore = $item->quantity_in_stock;
            $stockAfter  = $stockBefore - $data['quantity'];

            $item->update(['quantity_in_stock' => $stockAfter]);

            return InventoryTransaction::create([
                'item_id'          => $item->id,
                'transaction_type' => 'damage',
                'quantity'         => $data['quantity'],
                'stock_before'     => $stockBefore,
                'stock_after'      => $stockAfter,
                'transaction_date' => $data['transaction_date'] ?? now()->toDateString(),
                'reason'           => $data['reason']           ?? 'Item damaged',
                'notes'            => $data['notes']            ?? null,
            ]);
        });
    }

    // ── Upload document ────────────────────────────────
    public function uploadDocument(InventoryTransaction $txn, $file, int $tenantId): string
    {
        $dir = storage_path(
            'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR .
                'tenants' . DIRECTORY_SEPARATOR . $tenantId . DIRECTORY_SEPARATOR . 'inventory-docs'
        );

        if (!File::exists($dir)) {
            File::makeDirectory($dir, 0775, true);
        }

        $fileName = "inv-doc-{$txn->id}-" . time() . '.' . $file->extension();
        $file->storeAs("public/tenants/{$tenantId}/inventory-docs", $fileName);
        $path = "tenants/{$tenantId}/inventory-docs/{$fileName}";
        $txn->update(['document_path' => $path]);

        return $path;
    }
}
