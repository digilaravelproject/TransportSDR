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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('trip_number')->unique();

            // Schedule
            $table->date('trip_date');
            $table->date('return_date')->nullable();
            $table->unsignedInteger('duration_days')->default(1);

            // Route
            $table->string('trip_route');
            $table->text('pickup_address');
            $table->json('destination_points');

            // Vehicle
            $table->foreignId('vehicle_id')->constrained('vehicles')->restrictOnDelete();
            $table->string('vehicle_type');
            $table->unsignedInteger('seating_capacity');
            $table->unsignedInteger('number_of_vehicles')->default(1);

            // Customer
            $table->foreignId('customer_id')->constrained('customers')->restrictOnDelete();
            $table->string('customer_name');
            $table->string('customer_contact', 15);

            // Assigned staff
            $table->foreignId('driver_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('helper_id')->nullable()->constrained('staff')->nullOnDelete();

            // KM
            $table->decimal('start_km', 10, 2)->nullable();
            $table->decimal('end_km', 10, 2)->nullable();
            $table->decimal('total_km', 10, 2)->nullable();
            $table->char('km_grade', 1)->nullable();

            // Payment
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('advance_amount', 12, 2)->default(0);
            $table->decimal('part_payment', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->boolean('is_gst')->default(false);
            $table->decimal('gst_percent', 5, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');

            // Status
            $table->enum('status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');

            // Documents
            $table->string('duty_slip_path')->nullable();
            $table->string('invoice_path')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
