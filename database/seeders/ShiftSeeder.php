<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Shift;

class ShiftSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $shifts = [
            [
                'name' => 'Morning Shift A',
                'description' => 'Early morning shift - 6 AM to 2 PM',
                'start_time' => '06:00',
                'end_time' => '14:00',
                'type' => 'regular',
                'days' => [1, 2, 3, 4, 5], // Monday to Friday
                'duration_hours' => 8,
                'is_active' => true,
                'max_drivers' => 15,
                'hourly_rate' => 150.00,
                'notes' => 'Standard morning shift with 15 driver capacity'
            ],
            [
                'name' => 'Evening Shift B',
                'description' => 'Evening shift - 2 PM to 10 PM',
                'start_time' => '14:00',
                'end_time' => '22:00',
                'type' => 'regular',
                'days' => [1, 2, 3, 4, 5, 6], // Monday to Saturday
                'duration_hours' => 8,
                'is_active' => true,
                'max_drivers' => 12,
                'hourly_rate' => 175.00,
                'notes' => 'Evening shift with 12 driver capacity'
            ],
            [
                'name' => 'Night Extra',
                'description' => 'Night shift - 10 PM to 6 AM',
                'start_time' => '22:00',
                'end_time' => '06:00',
                'type' => 'night',
                'days' => [2, 3, 4, 5, 6, 7], // Tuesday to Sunday
                'duration_hours' => 8,
                'is_active' => true,
                'max_drivers' => 8,
                'hourly_rate' => 250.00,
                'notes' => 'Night shift with higher hourly rate and 8 driver capacity'
            ],
            [
                'name' => 'Overtime - Weekend',
                'description' => 'Weekend overtime - Saturday and Sunday 8 AM to 4 PM',
                'start_time' => '08:00',
                'end_time' => '16:00',
                'type' => 'overtime',
                'days' => [6, 7], // Saturday and Sunday
                'duration_hours' => 8,
                'is_active' => true,
                'max_drivers' => 10,
                'hourly_rate' => 200.00,
                'notes' => 'Weekend overtime shift with premium hourly rate'
            ],
            [
                'name' => 'Half Day Morning',
                'description' => 'Half day morning - 6 AM to 12 PM',
                'start_time' => '06:00',
                'end_time' => '12:00',
                'type' => 'custom',
                'days' => [1, 2, 3, 4, 5], // Monday to Friday
                'duration_hours' => 6,
                'is_active' => true,
                'max_drivers' => 5,
                'hourly_rate' => 140.00,
                'notes' => 'Half day morning shift for flexible scheduling'
            ],
            [
                'name' => 'Half Day Evening',
                'description' => 'Half day evening - 4 PM to 10 PM',
                'start_time' => '16:00',
                'end_time' => '22:00',
                'type' => 'custom',
                'days' => [1, 2, 3, 4, 5], // Monday to Friday
                'duration_hours' => 6,
                'is_active' => true,
                'max_drivers' => 5,
                'hourly_rate' => 165.00,
                'notes' => 'Half day evening shift for flexible scheduling'
            ],
        ];

        foreach ($shifts as $shift) {
            Shift::create($shift);
        }
    }
}
