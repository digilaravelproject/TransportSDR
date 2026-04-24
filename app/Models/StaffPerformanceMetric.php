<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StaffPerformanceMetric extends Model
{
    protected $fillable = ['staff_id', 'overall_score', 'on_time_percentage', 'fuel_efficiency', 'safety_violations', 'customer_satisfaction', 'month'];
}
