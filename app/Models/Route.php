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

    public function drivers()
    {
        return $this->belongsToMany(Staff::class, 'route_driver', 'route_id', 'driver_id')
            ->withPivot(['assigned_from', 'assigned_to'])
            ->withTimestamps();
    }
}
