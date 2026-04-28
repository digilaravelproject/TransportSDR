<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('lead_number')->nullable()->index();
            $table->string('trip_route');
            $table->date('trip_date');
            $table->integer('duration_days')->default(1);
            $table->string('vehicle_type')->nullable();
            $table->integer('seating_capacity')->nullable();
            $table->text('pickup_address')->nullable();
            $table->json('points')->nullable();
            $table->string('customer_name');
            $table->string('customer_contact');
            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('advance_amount', 12, 2)->default(0);
            $table->decimal('pending_amount', 12, 2)->default(0);
            $table->string('status')->default('pending')->index();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
