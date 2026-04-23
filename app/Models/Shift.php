<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'start_time',
        'end_time',
        'type',
        'days',
        'duration_hours',
        'is_active',
        'max_drivers',
        'hourly_rate',
        'notes',
    ];

    protected $casts = [
        'days' => 'array',
        'is_active' => 'boolean',
        'start_time' => 'datetime:H:i',
        'end_time' => 'datetime:H:i',
        'hourly_rate' => 'decimal:2',
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
        return $query->where('name', 'like', "%{$search}%")
                     ->orWhere('description', 'like', "%{$search}%");
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('start_time', 'asc');
    }

    /**
     * Calculate duration in hours
     */
    public function calculateDuration()
    {
        $start = strtotime($this->start_time);
        $end = strtotime($this->end_time);
        
        // Handle night shift (end time is next day)
        if ($end < $start) {
            $end = $end + (24 * 3600);
        }
        
        $hours = ($end - $start) / 3600;
        return $hours;
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
     * Get day names
     */
    public function getDayNames()
    {
        if (!$this->days) {
            return [];
        }

        $dayMap = [
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday',
        ];

        return array_map(fn($day) => $dayMap[$day] ?? null, $this->days);
    }

    /**
     * Check if shift is available on a specific day
     */
    public function isAvailableOnDay($day)
    {
        return in_array($day, $this->days ?? []);
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
