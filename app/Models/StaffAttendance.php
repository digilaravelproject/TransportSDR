<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StaffAttendance extends Model
{
    use HasFactory;

    protected $table = 'staff_attendances';

    protected $fillable = [
<<<<<<< Updated upstream
        'staff_id', 'date', 'status', 'in_time', 'out_time', 'total_hours', 'notes'
=======
        'tenant_id',
        'staff_id',
        'date',
        'trip_purpose',
        'status',
        'check_in',
        'check_out',
        'working_hours',
        'notes',
        'marked_by',
>>>>>>> Stashed changes
    ];

    protected $casts = [
        'date' => 'date',
        'total_hours' => 'decimal:2'
    ];

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }
}
