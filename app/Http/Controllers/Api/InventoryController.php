<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\{InventoryItem, InventoryTransaction, InventoryCategory};
use App\Services\Inventory\InventoryService;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function __construct(private InventoryService $inventoryService) {}

    // ─────────────────────────────────────────────────
    // GET /api/v1/inventory
    // List all items
    // ─────────────────────────────────────────────────
    public function index(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        $request->validate([
            'item_type'   => 'nullable|string',
            'category_id' => 'nullable|integer',
            'vehicle_id'  => 'nullable|integer',
            'stock_status' => 'nullable|in:in_stock,low_stock,out_of_stock,reorder_needed',
            'search'      => 'nullable|string|max:100',
        ]);

        $items = InventoryItem::with(['category', 'vehicle'])
            ->when($request->item_type,   fn($q, $v) => $q->where('item_type', $v))
            ->when($request->category_id, fn($q, $v) => $q->where('category_id', $v))
            ->when($request->vehicle_id,  fn($q, $v) => $q->where('vehicle_id', $v))
            ->when($request->stock_status, function ($q, $v) {
                return match ($v) {
                    'low_stock'      => $q->lowStock(),
                    'out_of_stock'   => $q->outOfStock(),
                    'reorder_needed' => $q->needsReorder(),
                    default          => $q,
                };
            })
            ->when($request->search, fn($q, $v) => $q->where(function ($q) use ($v) {
                $q->where('name', 'like', "%{$v}%")
                    ->orWhere('item_code', 'like', "%{$v}%")
                    ->orWhere('brand', 'like', "%{$v}%")
                    ->orWhere('barcode', 'like', "%{$v}%");
            }))
            ->active()
            ->latest()
            ->paginate($request->per_page ?? 20)
            ->withQueryString();

        return response()->json([
            'success' => true,
            'data'    => $items,
            'meta'    => [
                'total'        => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page'    => $items->lastPage(),
            ],
        ]);
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/inventory
    // Add new item
    // ─────────────────────────────────────────────────
    public function store(Request $request)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $data = $request->validate([
            'name'                => 'required|string|max:255',
            'category_id'         => 'nullable|exists:inventory_categories,id',
            'item_code'           => 'nullable|string|max:50',
            'description'         => 'nullable|string',
            'brand'               => 'nullable|string|max:100',
            'model_compatible'    => 'nullable|string|max:100',
            'unit'                => 'required|string|max:50',
            'quantity_in_stock'   => 'nullable|numeric|min:0',
            'minimum_stock_level' => 'nullable|numeric|min:0',
            'maximum_stock_level' => 'nullable|numeric|min:0',
            'reorder_level'       => 'nullable|numeric|min:0',
            'purchase_price'      => 'nullable|numeric|min:0',
            'selling_price'       => 'nullable|numeric|min:0',
            'storage_location'    => 'nullable|string|max:100',
            'barcode'             => 'nullable|string|max:100',
            'vehicle_id'          => 'nullable|exists:vehicles,id',
            'item_type'           => 'required|in:spare_part,consumable,tyre,tool,safety,electrical,body_part,office,other',
            'condition'           => 'nullable|in:new,good,fair,needs_replacement',
            'vendor_name'         => 'nullable|string|max:255',
            'vendor_contact'      => 'nullable|string|max:15',
            'notes'               => 'nullable|string',
        ], [
            'name.required'     => 'Item name is required.',
            'unit.required'     => 'Unit is required.',
            'item_type.required' => 'Item type is required.',
        ]);

        $item = $this->inventoryService->createItem($data);

        return response()->json([
            'success' => true,
            'message' => "Item {$item->item_code} — {$item->name} added successfully.",
            'data'    => $item->load('category'),
        ], 201);
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/inventory/{id}
    // Single item detail
    // ─────────────────────────────────────────────────
    public function show(InventoryItem $item)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        $item->load(['category', 'vehicle', 'creator']);

        $recentTransactions = InventoryTransaction::where('item_id', $item->id)
            ->latest('transaction_date')
            ->take(10)
            ->get();

        return response()->json([
            'success' => true,
            'data'    => [
                'item'                => $item,
                'stock_status'        => $item->stock_status,
                'is_low_stock'        => $item->isLowStock(),
                'is_out_of_stock'     => $item->isOutOfStock(),
                'needs_reorder'       => $item->needsReorder(),
                'recent_transactions' => $recentTransactions,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────
    // PUT /api/v1/inventory/{id}
    // Update item
    // ─────────────────────────────────────────────────
    public function update(Request $request, InventoryItem $item)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $data = $request->validate([
            'name'                => 'sometimes|string|max:255',
            'category_id'         => 'nullable|exists:inventory_categories,id',
            'description'         => 'nullable|string',
            'brand'               => 'nullable|string|max:100',
            'model_compatible'    => 'nullable|string|max:100',
            'unit'                => 'sometimes|string|max:50',
            'minimum_stock_level' => 'nullable|numeric|min:0',
            'maximum_stock_level' => 'nullable|numeric|min:0',
            'reorder_level'       => 'nullable|numeric|min:0',
            'purchase_price'      => 'nullable|numeric|min:0',
            'selling_price'       => 'nullable|numeric|min:0',
            'storage_location'    => 'nullable|string|max:100',
            'vehicle_id'          => 'nullable|exists:vehicles,id',
            'item_type'           => 'sometimes|in:spare_part,consumable,tyre,tool,safety,electrical,body_part,office,other',
            'condition'           => 'nullable|in:new,good,fair,needs_replacement',
            'vendor_name'         => 'nullable|string|max:255',
            'vendor_contact'      => 'nullable|string|max:15',
            'is_active'           => 'boolean',
            'notes'               => 'nullable|string',
        ]);

        $item = $this->inventoryService->updateItem($item, $data);

        return response()->json([
            'success' => true,
            'message' => 'Item updated successfully.',
            'data'    => $item,
        ]);
    }

    // ─────────────────────────────────────────────────
    // DELETE /api/v1/inventory/{id}
    // Soft delete
    // ─────────────────────────────────────────────────
    public function destroy(InventoryItem $item)
    {
        $this->checkRole(['superadmin', 'admin']);

        if ($item->quantity_in_stock > 0) {
            return response()->json([
                'success' => false,
                'message' => "Cannot delete. Item has {$item->quantity_in_stock} {$item->unit} in stock. Please clear stock first.",
            ], 422);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item deleted successfully.',
        ]);
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/inventory/{id}/stock-in
    // Add stock (purchase/received)
    // ─────────────────────────────────────────────────
    public function stockIn(Request $request, InventoryItem $item)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $data = $request->validate([
            'quantity'         => 'required|numeric|min:0.01',
            'unit_price'       => 'nullable|numeric|min:0',
            'vendor_name'      => 'nullable|string|max:255',
            'vendor_contact'   => 'nullable|string|max:15',
            'invoice_number'   => 'nullable|string|max:100',
            'transaction_date' => 'required|date',
            'received_by'      => 'nullable|string|max:255',
            'storage_location' => 'nullable|string|max:100',
            'reference_type'   => 'nullable|string|max:100',
            'reference_id'     => 'nullable|integer',
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
        ], [
            'quantity.required'         => 'Quantity is required.',
            'transaction_date.required' => 'Transaction date is required.',
        ]);

        $result = $this->inventoryService->stockIn($item, $data);

        return response()->json([
            'success' => true,
            'message' => "Stock in: +{$data['quantity']} {$item->unit}. New balance: {$result['item']->quantity_in_stock}",
            'data'    => $result,
        ], 201);
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/inventory/{id}/stock-out
    // Issue/use stock
    // ─────────────────────────────────────────────────
    public function stockOut(Request $request, InventoryItem $item)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $data = $request->validate([
            'quantity'         => 'required|numeric|min:0.01|max:' . $item->quantity_in_stock,
            'unit_price'       => 'nullable|numeric|min:0',
            'issued_to'        => 'nullable|string|max:255',
            'reason'           => 'nullable|string|max:255',
            'transaction_date' => 'required|date',
            'reference_type'   => 'nullable|string|max:100',
            'reference_id'     => 'nullable|integer',
            'reference_number' => 'nullable|string|max:100',
            'notes'            => 'nullable|string',
        ], [
            'quantity.required'         => 'Quantity is required.',
            'quantity.max'              => "Cannot issue more than {$item->quantity_in_stock} {$item->unit} in stock.",
            'transaction_date.required' => 'Transaction date is required.',
        ]);

        $result = $this->inventoryService->stockOut($item, $data);

        return response()->json([
            'success' => true,
            'message' => "Stock out: -{$data['quantity']} {$item->unit}. Remaining: {$result['item']->quantity_in_stock}",
            'data'    => $result,
        ], 201);
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/inventory/{id}/adjust
    // Manual stock adjustment
    // ─────────────────────────────────────────────────
    public function adjust(Request $request, InventoryItem $item)
    {
        $this->checkRole(['superadmin', 'admin']);

        $data = $request->validate([
            'new_quantity'     => 'required|numeric|min:0',
            'reason'           => 'required|string|max:255',
            'transaction_date' => 'nullable|date',
            'notes'            => 'nullable|string',
        ], [
            'new_quantity.required' => 'New quantity is required.',
            'reason.required'       => 'Reason for adjustment is required.',
        ]);

        $result = $this->inventoryService->adjust($item, $data);

        return response()->json([
            'success' => true,
            'message' => "Stock adjusted to {$data['new_quantity']} {$item->unit}.",
            'data'    => $result,
        ]);
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/inventory/{id}/return
    // Return stock
    // ─────────────────────────────────────────────────
    public function returnStock(Request $request, InventoryItem $item)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $data = $request->validate([
            'quantity'         => 'required|numeric|min:0.01',
            'reason'           => 'nullable|string|max:255',
            'returned_by'      => 'nullable|string|max:255',
            'transaction_date' => 'nullable|date',
            'notes'            => 'nullable|string',
        ]);

        $result = $this->inventoryService->returnStock($item, $data);

        return response()->json([
            'success' => true,
            'message' => "Stock returned: +{$data['quantity']} {$item->unit}. New balance: {$result['item']->quantity_in_stock}",
            'data'    => $result,
        ]);
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/inventory/{id}/damage
    // Mark as damaged
    // ─────────────────────────────────────────────────
    public function markDamaged(Request $request, InventoryItem $item)
    {
        $this->checkRole(['superadmin', 'admin']);

        $data = $request->validate([
            'quantity'         => 'required|numeric|min:0.01|max:' . $item->quantity_in_stock,
            'reason'           => 'required|string|max:255',
            'transaction_date' => 'nullable|date',
            'notes'            => 'nullable|string',
        ], [
            'quantity.required' => 'Quantity is required.',
            'quantity.max'      => "Cannot exceed available stock of {$item->quantity_in_stock}.",
            'reason.required'   => 'Reason is required.',
        ]);

        $result = $this->inventoryService->markDamaged($item, $data);

        return response()->json([
            'success' => true,
            'message' => "{$data['quantity']} {$item->unit} marked as damaged.",
            'data'    => $result,
        ]);
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/inventory/{id}/history
    // Transaction history
    // ─────────────────────────────────────────────────
    public function history(Request $request, InventoryItem $item)
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        $request->validate([
            'type' => 'nullable|in:stock_in,stock_out,adjustment,return,transfer,damage',
            'from' => 'nullable|date',
            'to'   => 'nullable|date',
        ]);

        $history = $this->inventoryService->getHistory(
            $item,
            $request->only(['type', 'from', 'to']),
            $request->per_page ?? 20
        );

        return response()->json([
            'success' => true,
            'data'    => [
                'item'    => $item->only(['id', 'name', 'item_code', 'unit', 'quantity_in_stock']),
                'history' => $history,
            ],
        ]);
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/inventory/transactions/{txn}/document
    // Upload bill/invoice document
    // ─────────────────────────────────────────────────
    public function uploadDocument(Request $request, InventoryTransaction $transaction)
    {
        $this->checkRole(['superadmin', 'admin', 'operator']);

        $request->validate([
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
        ]);

        $path = $this->inventoryService->uploadDocument(
            $transaction,
            $request->file('document'),
            auth()->user()->tenant_id
        );

        return response()->json([
            'success'      => true,
            'message'      => 'Document uploaded.',
            'document_url' => asset("storage/{$path}"),
        ]);
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/inventory/alerts/low-stock
    // Low stock alerts
    // ─────────────────────────────────────────────────
    public function lowStockAlerts()
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        $alerts = $this->inventoryService->getLowStockAlerts();

        return response()->json([
            'success' => true,
            'data'    => [
                'out_of_stock_count'  => count($alerts['out_of_stock']),
                'low_stock_count'     => count($alerts['low_stock']),
                'reorder_count'       => count($alerts['reorder_needed']),
                'out_of_stock'        => $alerts['out_of_stock'],
                'low_stock'           => $alerts['low_stock'],
                'reorder_needed'      => $alerts['reorder_needed'],
            ],
        ]);
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/inventory/valuation
    // Total stock value and summary
    // ─────────────────────────────────────────────────
    public function valuation()
    {
        $this->checkRole(['superadmin', 'admin', 'accountant']);

        return response()->json([
            'success' => true,
            'data'    => $this->inventoryService->getValuation(),
        ]);
    }

    // ─────────────────────────────────────────────────
    // GET /api/v1/inventory/categories
    // List categories
    // ─────────────────────────────────────────────────
    public function categories()
    {
        $this->checkRole(['superadmin', 'admin', 'operator', 'accountant']);

        return response()->json([
            'success' => true,
            'data'    => $this->inventoryService->getCategories(),
        ]);
    }

    // ─────────────────────────────────────────────────
    // POST /api/v1/inventory/categories
    // Create category
    // ─────────────────────────────────────────────────
    public function createCategory(Request $request)
    {
        $this->checkRole(['superadmin', 'admin']);

        $data = $request->validate([
            'name'        => 'required|string|max:100',
            'description' => 'nullable|string',
            'icon'        => 'nullable|string|max:50',
        ], [
            'name.required' => 'Category name is required.',
        ]);

        $category = $this->inventoryService->createCategory($data);

        return response()->json([
            'success' => true,
            'message' => "Category '{$category->name}' created.",
            'data'    => $category,
        ], 201);
    }

    private function checkRole(array $roles): void
    {
        if (!auth()->user()->hasRole($roles)) {
            abort(403, 'You do not have permission for this action.');
        }
    }
}
