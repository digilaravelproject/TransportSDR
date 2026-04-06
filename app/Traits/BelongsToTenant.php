<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait BelongsToTenant
{
    protected static function bootBelongsToTenant(): void
    {
        // Auto set tenant_id on create
        static::creating(function ($model) {
            if (Auth::check() && Auth::user()->tenant_id) {
                $model->tenant_id = Auth::user()->tenant_id;
            }
        });

        // Auto scope all queries — driver/operator sirf apne tenant ka data dekhega
        static::addGlobalScope('tenant', function ($query) {
            if (Auth::check() && Auth::user()->tenant_id) {
                $query->where(
                    $query->getModel()->getTable() . '.tenant_id',
                    Auth::user()->tenant_id
                );
            }
        });
    }
}
