<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class InventoryCategory extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'name',
        'description',
        'icon',
        'is_active',
        'created_by',
    ];

    protected $casts = ['is_active' => 'boolean'];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());
    }

    public function items()
    {
        return $this->hasMany(InventoryItem::class, 'category_id');
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
