<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeadExpense extends Model
{
    protected $fillable = [
        'tenant_id', 'lead_id', 'category', 'amount', 'description', 'entry_date', 'receipt_path', 'created_by'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function lead()
    {
        return $this->belongsTo(Lead::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
