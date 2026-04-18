<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

class InventoryItem extends Model
{
    use SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'category_id',
        'item_code',
        'name',
        'description',
        'brand',
        'model_compatible',
        'unit',
        'quantity_in_stock',
        'minimum_stock_level',
        'maximum_stock_level',
        'reorder_level',
        'purchase_price',
        'selling_price',
        'total_stock_value',
        'storage_location',
        'barcode',
        'vehicle_id',
        'item_type',
        'condition',
        'vendor_name',
        'vendor_contact',
        'is_active',
        'low_stock_alert_sent',
        'last_restocked_at',
        'last_used_at',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'is_active'            => 'boolean',
        'low_stock_alert_sent' => 'boolean',
        'last_restocked_at'    => 'datetime',
        'last_used_at'         => 'datetime',
        'quantity_in_stock'    => 'decimal:2',
        'minimum_stock_level'  => 'decimal:2',
        'maximum_stock_level'  => 'decimal:2',
        'reorder_level'        => 'decimal:2',
        'purchase_price'       => 'decimal:2',
        'selling_price'        => 'decimal:2',
        'total_stock_value'    => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(function ($item) {
            $item->created_by = auth()->id();

            // Auto item code
            if (empty($item->item_code)) {
                $count = self::withoutGlobalScopes()
                    ->where('tenant_id', $item->tenant_id)
                    ->count() + 1;
                $item->item_code = 'ITM-' . str_pad($count, 4, '0', STR_PAD_LEFT);
            }
        });

        static::saving(function ($item) {
            $item->total_stock_value = round(
                $item->quantity_in_stock * $item->purchase_price,
                2
            );
        });
    }

    // Relationships
    public function category()
    {
        return $this->belongsTo(InventoryCategory::class, 'category_id');
    }
    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }
    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'item_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Helpers
    public function isLowStock(): bool
    {
        return $this->quantity_in_stock <= $this->minimum_stock_level;
    }

    public function isOutOfStock(): bool
    {
        return $this->quantity_in_stock <= 0;
    }

    public function needsReorder(): bool
    {
        return $this->quantity_in_stock <= $this->reorder_level;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->isOutOfStock())  return 'out_of_stock';
        if ($this->isLowStock())    return 'low_stock';
        if ($this->needsReorder())  return 'reorder_needed';
        return 'in_stock';
    }

    // Scopes
    public function scopeLowStock($q)
    {
        return $q->whereColumn('quantity_in_stock', '<=', 'minimum_stock_level');
    }
    public function scopeOutOfStock($q)
    {
        return $q->where('quantity_in_stock', '<=', 0);
    }
    public function scopeNeedsReorder($q)
    {
        return $q->whereColumn('quantity_in_stock', '<=', 'reorder_level');
    }
    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }
}
