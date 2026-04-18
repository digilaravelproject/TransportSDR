<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained('inventory_items')->cascadeOnDelete();

            $table->enum('transaction_type', [
                'stock_in',      // purchase / received
                'stock_out',     // used / issued
                'adjustment',    // manual correction
                'return',        // returned from use
                'transfer',      // between locations
                'damage',        // damaged / scrap
            ]);

            // Quantity
            $table->decimal('quantity', 12, 2);
            $table->decimal('stock_before', 12, 2);   // stock before this txn
            $table->decimal('stock_after', 12, 2);    // stock after this txn

            // Cost
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('total_price', 12, 2)->default(0);

            // Reference
            $table->string('reference_type')->nullable();  // Trip, Vehicle, Maintenance
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number')->nullable();

            // Details
            $table->string('vendor_name')->nullable();
            $table->string('vendor_contact', 15)->nullable();
            $table->string('invoice_number')->nullable();
            $table->date('transaction_date');
            $table->string('reason')->nullable();          // why stock out / adjustment
            $table->string('received_by')->nullable();
            $table->string('issued_to')->nullable();
            $table->string('storage_location')->nullable();

            $table->text('notes')->nullable();
            $table->string('document_path')->nullable();   // bill/invoice upload
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'item_id']);
            $table->index(['tenant_id', 'transaction_type']);
            $table->index(['tenant_id', 'transaction_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_transactions');
    }
};
