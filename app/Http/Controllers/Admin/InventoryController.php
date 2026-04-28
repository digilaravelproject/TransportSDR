<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Inventory;

class InventoryController extends Controller
{
    public function index()
    {
        $inventories = Inventory::latest()->paginate(20);
        return view('admin.inventory.index', compact('inventories'));
    }

    public function show(Inventory $inventory)
    {
        $inventory->load('stocks');
        return view('admin.inventory.show', compact('inventory'));
    }

    public function destroy(Inventory $inventory)
    {
        $inventory->delete();
        return redirect()->route('admin.inventory.index')->with('success','Inventory deleted');
    }
}
