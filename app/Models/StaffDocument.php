<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class StaffDocument extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'staff_id',
        'document_type',
        'document_number',
        'document_path',
        'expiry_date',
        'is_verified',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'is_verified' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
