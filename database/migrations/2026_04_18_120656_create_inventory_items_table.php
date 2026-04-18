<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('inventory_categories')->nullOnDelete();

            // Item info
            $table->string('item_code')->nullable();     // auto or manual
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('model_compatible')->nullable(); // which vehicle model

            // Unit & stock
            $table->string('unit')->default('piece');   // piece, liter, kg, meter, set
            $table->decimal('quantity_in_stock', 12, 2)->default(0);
            $table->decimal('minimum_stock_level', 12, 2)->default(1);
            $table->decimal('maximum_stock_level', 12, 2)->nullable();
            $table->decimal('reorder_level', 12, 2)->default(2);

            // Pricing
            $table->decimal('purchase_price', 12, 2)->default(0);
            $table->decimal('selling_price', 12, 2)->default(0);
            $table->decimal('total_stock_value', 12, 2)->default(0);

            // Location
            $table->string('storage_location')->nullable(); // Rack A, Shelf 2
            $table->string('barcode')->nullable();

            // Vehicle linkage
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
            $table->enum('item_type', [
                'spare_part',
                'consumable',     // oil, grease, coolant
                'tyre',
                'tool',
                'safety',         // fire extinguisher, first aid
                'electrical',
                'body_part',
                'office',
                'other',
            ])->default('spare_part');

            // Condition
            $table->enum('condition', ['new', 'good', 'fair', 'needs_replacement'])->default('new');

            // Vendor
            $table->string('vendor_name')->nullable();
            $table->string('vendor_contact', 15)->nullable();

            // Status
            $table->boolean('is_active')->default(true);
            $table->boolean('low_stock_alert_sent')->default(false);
            $table->timestamp('last_restocked_at')->nullable();
            $table->timestamp('last_used_at')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'item_type']);
            $table->index(['tenant_id', 'category_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_items');
    }
};
