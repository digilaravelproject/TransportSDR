<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TripDutySheet extends Model
{
    protected $fillable = [
        'tenant_id', 'trip_id', 'uploaded_by', 'file_path', 'file_name', 'notes'
    ];

    public function trip()
    {
        return $this->belongsTo(Trip::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
