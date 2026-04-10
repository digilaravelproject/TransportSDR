<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corporate_duties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('corporate_id')->constrained('corporates')->cascadeOnDelete();

            $table->string('duty_number')->unique();
            $table->date('duty_date');
            $table->enum('duty_type', ['general', 'shift', 'shuttle'])->default('general');
            $table->enum('duty_status', ['scheduled', 'ongoing', 'completed', 'cancelled'])->default('scheduled');

            // Shift details
            $table->string('shift_name')->nullable(); // Morning, Evening, Night
            $table->time('shift_start')->nullable();
            $table->time('shift_end')->nullable();

            // Vehicle & Driver
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->string('vehicle_type')->nullable();
            $table->unsignedInteger('number_of_vehicles')->default(1);
            $table->foreignId('driver_id')->nullable()->constrained('staff')->nullOnDelete();
            $table->foreignId('helper_id')->nullable()->constrained('staff')->nullOnDelete();

            // Route
            $table->string('pickup_location')->nullable();
            $table->string('drop_location')->nullable();
            $table->text('route_details')->nullable();

            // KM tracking
            $table->decimal('start_km', 10, 2)->nullable();
            $table->decimal('end_km', 10, 2)->nullable();
            $table->decimal('total_km', 10, 2)->nullable();
            $table->decimal('extra_km', 10, 2)->default(0);

            // Hour tracking
            $table->decimal('total_hours', 6, 2)->nullable();
            $table->decimal('extra_hours', 6, 2)->default(0);

            // Duty type flags
            $table->boolean('is_holiday')->default(false);
            $table->boolean('is_extra_duty')->default(false);

            // Billing
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('extra_km_amount', 10, 2)->default(0);
            $table->decimal('extra_hour_amount', 10, 2)->default(0);
            $table->decimal('holiday_amount', 10, 2)->default(0);
            $table->decimal('extra_duty_amount', 10, 2)->default(0);
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->decimal('total_amount', 12, 2)->default(0);

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corporate_duties');
    }
};
