<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('gps_device_id')->nullable()->after('permit_file');
            $table->string('gps_status', 50)->nullable()->after('gps_device_id');

            $table->string('puc_certificate_number')->nullable()->after('gps_status');
            $table->date('puc_expiry_date')->nullable()->after('puc_certificate_number');
            $table->string('puc_file')->nullable()->after('puc_expiry_date');

            $table->string('fitness_certificate_number')->nullable()->after('puc_file');
            $table->date('fitness_valid_till')->nullable()->after('fitness_certificate_number');
            $table->string('fitness_file')->nullable()->after('fitness_valid_till');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn([
                'gps_device_id',
                'gps_status',
                'puc_certificate_number',
                'puc_expiry_date',
                'puc_file',
                'fitness_certificate_number',
                'fitness_valid_till',
                'fitness_file',
            ]);
        });
    }
};
