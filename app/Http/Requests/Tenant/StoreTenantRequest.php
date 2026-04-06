<?php

namespace App\Http\Requests\Tenant;

use Illuminate\Foundation\Http\FormRequest;

class StoreTenantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'company_name'        => 'required|string|max:255',
            'email'               => 'required|email|unique:users,email',
            'phone'               => 'required|string|max:15',
            'gstin'               => 'nullable|string|max:15',
            'address'             => 'nullable|string',
            'plan'                => 'required|in:basic,pro,enterprise',
            'max_vehicles'        => 'required|integer|min:1',
            'max_trips_per_month' => 'required|integer|min:1',
            'admin_name'          => 'required|string|max:255',
            'admin_password'      => 'required|string|min:8',
            'plan_expires_at'     => 'nullable|date|after:today',
        ];
    }
}
