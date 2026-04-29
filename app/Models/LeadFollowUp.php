<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadFollowUp extends Model
{
    protected $table = 'lead_followups';

    protected $fillable = [
        'tenant_id', 'lead_id', 'created_by', 'reminder_at', 'note', 'notified'
    ];

    protected $casts = [
        'reminder_at' => 'datetime',
        'notified'    => 'boolean',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function author()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
