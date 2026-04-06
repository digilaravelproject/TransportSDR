<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'tenant_id',
        'name',
        'email',
        'password',
        'role',
        'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'is_active'         => 'boolean',
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }
    public function staff()
    {
        return $this->hasOne(Staff::class);
    }

    // Role helpers
    public function isSuperAdmin(): bool
    {
        return $this->role === 'superadmin';
    }
    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
    public function isDriver(): bool
    {
        return $this->role === 'driver';
    }
    public function isAccountant(): bool
    {
        return $this->role === 'accountant';
    }
    public function hasRole(array $roles): bool
    {
        return in_array($this->role, $roles);
    }
}
