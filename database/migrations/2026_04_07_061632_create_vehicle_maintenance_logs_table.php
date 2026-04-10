<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::create('vehicle_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            // Type of maintenance
            $table->enum('maintenance_type', [
                'repair',
                'service',
                'lubricant',
                'spare_part',
                'tyre',
                'battery',
                'other',
            ]);

            $table->string('title');
            $table->text('description')->nullable();

            // Cost
            $table->decimal('labour_cost', 10, 2)->default(0);
            $table->decimal('parts_cost', 10, 2)->default(0);
            $table->decimal('total_cost', 10, 2)->default(0);

            // KM
            $table->decimal('km_at_service', 10, 2)->nullable();
            $table->decimal('next_service_km', 10, 2)->nullable();
            $table->date('next_service_date')->nullable();

            // Vendor / Workshop
            $table->string('vendor_name')->nullable();
            $table->string('vendor_contact')->nullable();
            $table->string('bill_number')->nullable();
            $table->string('bill_image')->nullable();

            // Status
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('completed');

            $table->date('service_date');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_maintenance_logs');
    }
};
