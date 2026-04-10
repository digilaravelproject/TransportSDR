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
        Schema::create('vehicle_spare_parts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->nullable()->constrained()->nullOnDelete();

            $table->string('part_name');
            $table->string('part_number')->nullable();
            $table->string('category')->nullable(); // engine, tyre, body, electrical

            // Stock
            $table->integer('quantity_in_stock')->default(0);
            $table->integer('minimum_stock_alert')->default(2);
            $table->string('unit')->default('piece'); // piece, liter, kg

            // Cost
            $table->decimal('unit_price', 10, 2)->default(0);
            $table->decimal('total_value', 10, 2)->default(0);

            // Health
            $table->enum('condition', ['good', 'fair', 'needs_replacement'])->default('good');
            $table->date('last_replaced_on')->nullable();
            $table->decimal('km_at_replacement', 10, 2)->nullable();
            $table->decimal('replacement_interval_km', 10, 2)->nullable();

            // Vendor
            $table->string('vendor_name')->nullable();
            $table->boolean('is_available')->default(true);
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
        Schema::dropIfExists('vehicle_spare_parts');
    }
};
