<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try { DB::statement('ALTER TABLE `trips` DROP FOREIGN KEY `trips_vehicle_type_foreign`'); } catch (\Throwable $e) {}
        // Modify column type from string to bigint unsigned nullable
        DB::statement('ALTER TABLE `trips` MODIFY `vehicle_type` BIGINT UNSIGNED NULL');
        // Add FK to vehicle_types
        try { DB::statement('ALTER TABLE `trips` ADD CONSTRAINT `trips_vehicle_type_foreign` FOREIGN KEY (`vehicle_type`) REFERENCES `vehicle_types`(`id`) ON DELETE SET NULL'); } catch (\Throwable $e) {}
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try { DB::statement('ALTER TABLE `trips` DROP FOREIGN KEY `trips_vehicle_type_foreign`'); } catch (\Throwable $e) {}
        DB::statement('ALTER TABLE `trips` MODIFY `vehicle_type` VARCHAR(255) NOT NULL');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
