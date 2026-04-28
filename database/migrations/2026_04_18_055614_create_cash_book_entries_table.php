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
        Schema::create('cash_book_entries', function (Blueprint $table) {
            $table->id();

            // Tenant relation
            $table->unsignedBigInteger('tenant_id')->nullable()->index();

            // Entry details
            $table->enum('entry_type', ['income', 'expense'])->index();
            $table->string('payment_mode')->index();
            $table->string('category')->index();

            // Financial data
            $table->decimal('amount', 12, 2);

            // Optional details
            $table->text('description')->nullable();
            $table->date('entry_date')->index();
            $table->string('reference_number')->nullable();

            // Receipt file path
            $table->string('receipt_path')->nullable();

            $table->timestamps();

            // Optional foreign key (enable if tenants table exists)
            // $table->foreign('tenant_id')
            //       ->references('id')
            //       ->on('tenants')
            //       ->cascadeOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_book_entries');
    }
};