<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripExpense extends Model
{
    protected $fillable = [
        'tenant_id', 'trip_id', 'category', 'amount', 'description', 'entry_date', 'receipt_path', 'created_by'
    ];

    protected $casts = [
        'entry_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
