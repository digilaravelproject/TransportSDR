<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
        'type',
        'duration_hours',
        'is_active',
        'notes',
        'date',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'date' => 'date',
    ];

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    public function scopeByType($query, $type)
    {
        if ($type === 'all') {
            return $query;
        }
        return $query->where('type', $type);
    }

    public function scopeSearchByName($query, $search)
    {
        return $query->where('name', 'like', "%{$search}%");
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('start_time', 'asc');
    }

    /**
     * Get formatted start time
     */
    public function getFormattedStartTimeAttribute()
    {
        return \Carbon\Carbon::createFromTimeString($this->start_time)->format('h:i A');
    }

    /**
     * Get formatted end time
     */
    public function getFormattedEndTimeAttribute()
    {
        return \Carbon\Carbon::createFromTimeString($this->end_time)->format('h:i A');
    }

    /**
     * Get time range display
     */
    public function getTimeRangeAttribute()
    {
        return $this->formatted_start_time . ' - ' . $this->formatted_end_time;
    }

    /**
     * Drivers assigned to this shift
     */
    public function drivers()
    {
        return $this->belongsToMany(\App\Models\Staff::class, 'shift_driver', 'shift_id', 'driver_id')
            ->where('staff_type', 'driver');
    }
}
