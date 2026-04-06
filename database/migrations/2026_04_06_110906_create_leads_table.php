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
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Lead number
            $table->string('lead_number')->unique();

            // Enquiry info
            $table->date('enquiry_date');
            $table->string('trip_route');
            $table->date('trip_date');
            $table->date('return_date')->nullable();
            $table->unsignedInteger('duration_days')->default(1);

            // Vehicle
            $table->string('vehicle_type');
            $table->unsignedInteger('seating_capacity');
            $table->unsignedInteger('number_of_vehicles')->default(1);

            // Pickup & destination
            $table->text('pickup_address');
            $table->json('destination_points');

            // Customer
            $table->string('customer_name');
            $table->string('customer_contact', 15);
            $table->string('customer_email')->nullable();
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();

            // Quoted amount
            $table->decimal('quoted_amount', 12, 2)->default(0);
            $table->decimal('advance_amount', 12, 2)->default(0);
            $table->boolean('is_gst')->default(false);
            $table->decimal('gst_percent', 5, 2)->default(0);

            // Lead status
            $table->enum('status', [
                'new',
                'contacted',
                'followup',
                'quoted',
                'confirmed',
                'converted',
                'lost',
                'cancelled',
            ])->default('new');

            // Source
            $table->enum('source', [
                'phone',
                'website',
                'whatsapp',
                'email',
                'walkin',
                'reference',
                'other',
            ])->default('phone');

            // Notes & followup
            $table->text('notes')->nullable();
            $table->date('followup_date')->nullable();
            $table->text('followup_notes')->nullable();

            // Converted trip reference
            $table->foreignId('converted_trip_id')->nullable()->constrained('trips')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();

            // Who created
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
