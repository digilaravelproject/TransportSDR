<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'name', 'category', 'item_code', 'unit', 'quantity_in_stock', 'reorder_level', 'unit_price', 'storage_location', 'description', 'created_by'
    ];

    protected $casts = [
        'quantity_in_stock' => 'decimal:2',
        'reorder_level'     => 'decimal:2',
        'unit_price'        => 'decimal:2',
    ];

    public function stocks()
    {
        return $this->hasMany(InventoryStock::class, 'inventory_id');
    }
}
