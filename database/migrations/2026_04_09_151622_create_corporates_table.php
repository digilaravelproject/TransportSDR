<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corporates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Company / Vendor info
            $table->string('company_name');
            $table->string('contact_person')->nullable();
            $table->string('phone', 15);
            $table->string('email')->nullable();
            $table->text('address')->nullable();
            $table->string('gstin', 15)->nullable();
            $table->string('pan', 10)->nullable();

            // Contract details
            $table->enum('contract_type', ['monthly', 'daily', 'trip_based'])->default('monthly');
            $table->decimal('monthly_package', 12, 2)->default(0);
            $table->decimal('per_day_rate', 10, 2)->default(0);
            $table->decimal('per_km_rate', 8, 2)->default(0);
            $table->decimal('extra_hour_rate', 8, 2)->default(0);
            $table->decimal('holiday_rate', 10, 2)->default(0);
            $table->decimal('extra_duty_rate', 10, 2)->default(0);

            // Included KM / Hours in package
            $table->decimal('included_km', 10, 2)->default(0);
            $table->integer('included_hours')->default(0);

            // Vehicle
            $table->string('vehicle_type')->nullable();
            $table->unsignedInteger('number_of_vehicles')->default(1);

            // Duty type
            $table->enum('duty_type', ['general', 'shift', 'shuttle'])->default('general');

            // GST settings
            $table->boolean('is_gst')->default(false);
            $table->decimal('gst_percent', 5, 2)->default(18);

            // Status
            $table->boolean('is_active')->default(true);
            $table->date('contract_start')->nullable();
            $table->date('contract_end')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corporates');
    }
};
