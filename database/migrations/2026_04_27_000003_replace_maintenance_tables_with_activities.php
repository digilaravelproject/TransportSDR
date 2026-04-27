<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop old tables if they exist
        Schema::dropIfExists('vehicle_ledgers');
        Schema::dropIfExists('vehicle_spare_parts');
        Schema::dropIfExists('vehicle_maintenance_logs');
        Schema::dropIfExists('vehicle_fuel_logs');

        // Create unified activities table
        Schema::create('vehicle_activities', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('vehicle_id')->index();
            $table->enum('activity_type', ['fuel', 'service', 'repair']);
            $table->string('title')->nullable();
            $table->date('activity_date')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->decimal('quantity', 12, 2)->nullable();
            $table->decimal('price_per_unit', 12, 2)->nullable();
            $table->string('station_name')->nullable();
            $table->string('workshop_name')->nullable();
            $table->string('garage_name')->nullable();
            $table->string('receipt_path')->nullable();
            $table->json('meta')->nullable();
            $table->unsignedBigInteger('created_by')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicle_activities');

        // Note: dropping these tables cannot recreate original structure reliably.
        // If you need rollback for old tables, restore from backups or create fresh migrations.
    }
};
