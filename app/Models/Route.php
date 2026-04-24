<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Route extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'distance', 'estimated_time', 'points', 'schedules', 'status'
    ];

    protected $casts = [
        'points' => 'array',
        'schedules' => 'array',
    ];

    public function vehicles()
    {
        return $this->belongsToMany(Vehicle::class, 'route_vehicle');
    }
}
