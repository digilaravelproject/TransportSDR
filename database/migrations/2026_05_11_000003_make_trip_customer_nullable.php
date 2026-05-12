<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            DB::statement('ALTER TABLE `trips` DROP FOREIGN KEY `trips_customer_id_foreign`');
        } catch (\Throwable $e) {}

        DB::statement('ALTER TABLE `trips` MODIFY `customer_id` BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE `trips` ADD CONSTRAINT `trips_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE SET NULL');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        try {
            DB::statement('ALTER TABLE `trips` DROP FOREIGN KEY `trips_customer_id_foreign`');
        } catch (\Throwable $e) {}
        DB::statement('ALTER TABLE `trips` MODIFY `customer_id` BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE `trips` ADD CONSTRAINT `trips_customer_id_foreign` FOREIGN KEY (`customer_id`) REFERENCES `customers`(`id`) ON DELETE RESTRICT');
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }
};
