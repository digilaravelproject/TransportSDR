<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'tenant_id', 'name', 'type', 'format', 'file_path', 'status', 'created_by'
    ];
}
