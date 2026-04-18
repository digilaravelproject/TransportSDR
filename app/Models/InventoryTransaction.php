<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class InventoryTransaction extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'item_id',
        'transaction_type',
        'quantity',
        'stock_before',
        'stock_after',
        'unit_price',
        'total_price',
        'reference_type',
        'reference_id',
        'reference_number',
        'vendor_name',
        'vendor_contact',
        'invoice_number',
        'transaction_date',
        'reason',
        'received_by',
        'issued_to',
        'storage_location',
        'notes',
        'document_path',
        'created_by',
    ];

    protected $casts = [
        'transaction_date' => 'date',
        'quantity'         => 'decimal:2',
        'stock_before'     => 'decimal:2',
        'stock_after'      => 'decimal:2',
        'unit_price'       => 'decimal:2',
        'total_price'      => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());

        static::saving(function ($t) {
            $t->total_price = round($t->quantity * $t->unit_price, 2);
        });
    }

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'item_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
