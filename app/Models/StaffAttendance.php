<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\BelongsToTenant;

class StaffAttendance extends Model
{
    use BelongsToTenant;

    protected $table = 'staff_attendance';

    protected $fillable = [
        'tenant_id',
        'staff_id',
        'date',
        'status',
        'check_in',
        'check_out',
        'working_hours',
        'notes',
        'marked_by',
    ];

    protected $casts = [
        'date'          => 'date',
        'working_hours' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::creating(fn($m) => $m->marked_by = auth()->id());

        static::saving(function ($att) {
            if ($att->check_in && $att->check_out) {
                $in  = \Carbon\Carbon::parse($att->check_in);
                $out = \Carbon\Carbon::parse($att->check_out);
                $att->working_hours = round($out->diffInMinutes($in) / 60, 2);
            }
        });
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
    public function markedBy()
    {
        return $this->belongsTo(User::class, 'marked_by');
    }
}
