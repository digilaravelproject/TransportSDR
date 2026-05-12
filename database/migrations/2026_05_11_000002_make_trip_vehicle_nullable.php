<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop existing foreign key, modify column to nullable, recreate FK with ON DELETE SET NULL
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        // Try drop foreign key if exists
        try {
            DB::statement('ALTER TABLE `trips` DROP FOREIGN KEY `trips_vehicle_id_foreign`');
        } catch (\Throwable $e) {
            // ignore
        }
        DB::statement('ALTER TABLE `trips` MODIFY `vehicle_id` BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE `trips` ADD CONSTRAINT `trips_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE SET NULL');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            DB::statement('ALTER TABLE `trips` DROP FOREIGN KEY `trips_vehicle_id_foreign`');
        } catch (\Throwable $e) {}
        // Revert column to NOT NULL (set default 0) and recreate FK with RESTRICT
        DB::statement('ALTER TABLE `trips` MODIFY `vehicle_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `trips` ADD CONSTRAINT `trips_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles`(`id`) ON DELETE RESTRICT');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
