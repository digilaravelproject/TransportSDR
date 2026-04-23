<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'origin', 'destination', 'distance', 'estimated_time', 'stops', 'status'
    ];

    protected $casts = [
        'stops' => 'array',
    ];

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'route_vehicle');
    }
}
