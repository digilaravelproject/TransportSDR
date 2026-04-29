<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadDutySheet extends Model
{
    protected $fillable = [
        'tenant_id', 'lead_id', 'uploaded_by', 'file_path', 'file_name', 'notes'
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
