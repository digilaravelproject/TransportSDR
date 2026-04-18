<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class TemplateLog extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'tenant_id',
        'template_type',
        'reference_type',
        'reference_id',
        'reference_number',
        'file_path',
        'file_name',
        'irn',
        'ack_number',
        'ack_date',
        'qr_code_path',
        'einvoice_status',
        'einvoice_response',
        'created_by',
    ];

    protected $casts = [
        'ack_date' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->created_by = auth()->id());
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function getFileUrlAttribute(): ?string
    {
        return $this->file_path ? asset("storage/{$this->file_path}") : null;
    }

    public function getQrUrlAttribute(): ?string
    {
        return $this->qr_code_path ? asset("storage/{$this->qr_code_path}") : null;
    }
}
