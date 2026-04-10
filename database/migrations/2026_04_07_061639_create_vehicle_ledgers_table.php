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
        Schema::create('vehicle_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            $table->enum('entry_type', ['income', 'expense']);
            $table->enum('category', [
                'trip_income',
                'fuel',
                'maintenance',
                'repair',
                'spare_part',
                'document',
                'driver_da',
                'toll',
                'other_income',
                'other_expense',
            ]);

            $table->string('reference_type')->nullable(); // Trip, FuelLog, MaintenanceLog
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('description');
            $table->decimal('amount', 12, 2);
            $table->date('entry_date');
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
        Schema::dropIfExists('vehicle_ledgers');
    }
};
