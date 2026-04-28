<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory;
use App\Models\InventoryStock;

class InventoryNewController extends Controller
{
    // GET /api/v1/inventories
    public function index(Request $request)
    {
        $request->validate([
            'type' => 'nullable|string',
            'search' => 'nullable|string|max:150',
        ]);

        $q = Inventory::query();

        if ($request->type) {
            $q->where('category', $request->type);
        }

        if ($request->search) {
            $s = $request->search;
            $q->where(function($qq) use ($s) {
                $qq->where('name', 'like', "%{$s}%")
                   ->orWhere('item_code', 'like', "%{$s}%");
            });
        }

        $items = $q->latest()->paginate($request->per_page ?? 20);

        // add low stock indicator
        $items->getCollection()->transform(function($item){
            $item->is_low_stock = ($item->quantity_in_stock <= $item->reorder_level);
            return $item;
        });

        return response()->json(['success' => true, 'data' => $items]);
    }

    // POST /api/v1/inventories
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:100',
            'item_code' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'initial_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'storage_location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $inventory = Inventory::create([
            'tenant_id' => auth()->user()->tenant_id ?? null,
            'name' => $data['name'],
            'category' => $data['category'] ?? null,
            'item_code' => $data['item_code'] ?? null,
            'unit' => $data['unit'] ?? 'unit',
            'quantity_in_stock' => 0,
            'reorder_level' => $data['reorder_level'] ?? 0,
            'unit_price' => $data['unit_price'] ?? 0,
            'storage_location' => $data['storage_location'] ?? null,
            'description' => $data['description'] ?? null,
            'created_by' => auth()->id() ?? null,
        ]);

        if (!empty($data['initial_stock']) && $data['initial_stock'] > 0) {
            $this->createStockRecord($inventory, 'stock_in', $data['initial_stock'], $data['unit_price'] ?? 0, $data['storage_location'] ?? null, 'Initial stock');
            $inventory->update(['quantity_in_stock' => $data['initial_stock']]);
        }

        return response()->json(['success' => true, 'data' => $inventory], 201);
    }

    public function show(Inventory $inventory)
    {
        $inventory->load(['stocks' => function($q){ $q->latest()->take(10); }]);
        $inventory->is_low_stock = ($inventory->quantity_in_stock <= $inventory->reorder_level);
        return response()->json(['success'=>true,'data'=>['inventory'=>$inventory,'recent_stocks'=>$inventory->stocks()->latest()->take(10)->get()]]);
    }

    public function update(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'name' => 'sometimes|string|max:255',
            'category' => 'nullable|string|max:100',
            'item_code' => 'nullable|string|max:100',
            'unit' => 'nullable|string|max:50',
            'reorder_level' => 'nullable|numeric|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'storage_location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
        ]);

        $inventory->update($data);
        return response()->json(['success'=>true,'data'=>$inventory]);
    }

    public function stocks(Inventory $inventory)
    {
        $stocks = $inventory->stocks()->latest()->paginate(request()->per_page ?? 20);
        return response()->json(['success'=>true,'data'=>$stocks]);
    }

    public function stockIn(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
            'vendor_name' => 'nullable|string|max:255',
            'invoice_number' => 'nullable|string|max:100',
            'transaction_date' => 'nullable|date',
            'reason' => 'nullable|string',
        ]);

        $this->createStockRecord($inventory, 'stock_in', $data['quantity'], $data['unit_price'] ?? 0, $data['vendor_name'] ?? null, $data['reason'] ?? null, $data);

        return response()->json(['success'=>true,'message'=>'Stock added.','data'=>$inventory->fresh()]);
    }

    public function stockOut(Request $request, Inventory $inventory)
    {
        $data = $request->validate([
            'quantity' => 'required|numeric|min:0.01',
            'unit_price' => 'nullable|numeric|min:0',
            'reason' => 'nullable|string',
            'transaction_date' => 'nullable|date',
        ]);

        if ($inventory->quantity_in_stock < $data['quantity']) {
            return response()->json(['success'=>false,'message'=>'Insufficient stock.'],422);
        }

        $this->createStockRecord($inventory, 'stock_out', $data['quantity'], $data['unit_price'] ?? 0, null, $data['reason'] ?? null, $data);

        return response()->json(['success'=>true,'message'=>'Stock removed.','data'=>$inventory->fresh()]);
    }

    protected function createStockRecord(Inventory $inventory, string $type, $quantity, $unitPrice = 0, $vendor = null, $reason = null, $extra = [])
    {
        $before = $inventory->quantity_in_stock;
        $after = $type === 'stock_in' ? $before + $quantity : $before - $quantity;

        $stock = InventoryStock::create([
            'inventory_id' => $inventory->id,
            'transaction_type' => $type,
            'quantity' => $quantity,
            'stock_before' => $before,
            'stock_after' => $after,
            'unit_price' => $unitPrice,
            'total_price' => round($unitPrice * $quantity,2),
            'vendor_name' => $vendor,
            'invoice_number' => $extra['invoice_number'] ?? null,
            'transaction_date' => $extra['transaction_date'] ?? now()->toDateString(),
            'reason' => $reason,
            'created_by' => auth()->id() ?? null,
        ]);

        $inventory->update(['quantity_in_stock' => $after]);

        return $stock;
    }
}
