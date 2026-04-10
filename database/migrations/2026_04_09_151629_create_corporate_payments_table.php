<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corporate_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('corporate_id')->constrained('corporates')->cascadeOnDelete();

            $table->string('invoice_number')->unique();
            $table->string('billing_period');     // e.g. 2026-04
            $table->date('billing_from');
            $table->date('billing_to');

            // Duty summary
            $table->integer('total_duties')->default(0);
            $table->integer('holiday_duties')->default(0);
            $table->integer('extra_duties')->default(0);
            $table->decimal('total_km', 10, 2)->default(0);
            $table->decimal('extra_km', 10, 2)->default(0);

            // Amount breakdown
            $table->decimal('base_amount', 12, 2)->default(0);
            $table->decimal('extra_km_amount', 12, 2)->default(0);
            $table->decimal('extra_hour_amount', 12, 2)->default(0);
            $table->decimal('holiday_amount', 12, 2)->default(0);
            $table->decimal('extra_duty_amount', 12, 2)->default(0);
            $table->decimal('fine_deduction', 12, 2)->default(0);
            $table->decimal('subtotal', 12, 2)->default(0);

            // GST
            $table->boolean('is_gst')->default(false);
            $table->decimal('gst_percent', 5, 2)->default(0);
            $table->decimal('cgst', 10, 2)->default(0);
            $table->decimal('sgst', 10, 2)->default(0);
            $table->decimal('igst', 10, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);

            $table->decimal('total_amount', 12, 2)->default(0);
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->decimal('balance_amount', 12, 2)->default(0);

            $table->enum('payment_status', ['pending', 'partial', 'paid'])->default('pending');
            $table->enum('payment_mode', ['cash', 'bank', 'cheque', 'upi'])->nullable();
            $table->date('paid_on')->nullable();
            $table->string('transaction_ref')->nullable();

            $table->string('invoice_path')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corporate_payments');
    }
};
