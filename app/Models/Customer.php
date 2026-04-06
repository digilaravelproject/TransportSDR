<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\BelongsToTenant;

class Customer extends Model
{
    use SoftDeletes, BelongsToTenant;
    protected $fillable = ['tenant_id', 'name', 'phone', 'email', 'address', 'gstin', 'is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function trips()
    {
        return $this->hasMany(Trip::class);
    }
}
