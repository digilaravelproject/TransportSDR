<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'                     => $this->id,
            'name'                   => $this->name,
            'phone'                  => $this->phone,
            'email'                  => $this->email,

            // Un-commented and updated for frontend forms
            'staff_type_id'          => $this->staff_type,
            'role_name'              => $this->role ? $this->role->name : null,

            'salary_type'            => $this->salary_type,

            // Updated to return Shift Object details instead of just an ID string
            'work_shift'             => $this->shift ? [
                'id'         => $this->shift->id,
                'name'       => $this->shift->name,
                'start_time' => $this->shift->start_time,
                'end_time'   => $this->shift->end_time,
                'type'       => $this->shift->type
            ] : null,

            // Added missing field from Add/Update functionality
            'assigned_vehicle'       => $this->assigned_vehicle,

            'basic_salary'           => (float) $this->basic_salary,
            'date_of_birth'          => $this->date_of_birth?->format('d-m-Y'),
            'date_of_joining'        => $this->date_of_joining?->format('d-m-Y'),
            'address'                => $this->address,
            'emergency_contact'      => $this->emergency_contact,
            'emergency_contact_name' => $this->emergency_contact_name,

            'license' => [
                'number'  => $this->license_number,
                'expiry'  => $this->license_expiry?->format('d-m-Y'),
                'type'    => $this->license_type,
                'expired' => $this->license_expiry?->isPast() ?? false,
            ],

            // Added Badge object which was missing
            'badge' => [
                'number'  => $this->badge_number,
                'expiry'  => $this->badge_expiry?->format('d-m-Y'),
                'expired' => $this->badge_expiry?->isPast() ?? false,
            ],

            'salary' => [
                'basic_salary'    => (float) $this->basic_salary,
                'da_per_day'      => (float) $this->da_per_day,
                'hra'             => (float) $this->hra,
                'other_allowance' => (float) $this->other_allowance,
            ],

            'bank' => [
                'bank_name'    => $this->bank_name,
                'bank_account' => $this->bank_account,
                'bank_ifsc'    => $this->bank_ifsc,
            ],

            'is_available'           => $this->is_available,
            'is_active'              => $this->is_active,
            'notes'                  => $this->notes,
            'documents'              => $this->whenLoaded('documents'),
            'user_id'                => $this->user_id,
            'created_at'             => $this->created_at?->format('d-m-Y'),
        ];
    }
}
