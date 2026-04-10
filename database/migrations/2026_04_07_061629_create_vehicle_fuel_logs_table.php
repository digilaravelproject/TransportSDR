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
        Schema::create('vehicle_fuel_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            // Fuel type
            $table->enum('fuel_type', ['diesel', 'petrol', 'cng', 'adblue', 'electric'])->default('diesel');

            // Fuel details
            $table->decimal('quantity_liters', 8, 2);
            $table->decimal('price_per_liter', 8, 2);
            $table->decimal('total_cost', 10, 2);

            // KM tracking
            $table->decimal('km_at_fill', 10, 2);
            $table->decimal('km_since_last_fill', 10, 2)->nullable();
            $table->decimal('fuel_efficiency', 8, 2)->nullable(); // km per liter

            // Station & payment
            $table->string('fuel_station')->nullable();
            $table->enum('payment_mode', ['cash', 'card', 'upi', 'account'])->default('cash');
            $table->string('bill_number')->nullable();
            $table->string('bill_image')->nullable();

            $table->date('filled_on');
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
        Schema::dropIfExists('vehicle_fuel_logs');
    }
};
