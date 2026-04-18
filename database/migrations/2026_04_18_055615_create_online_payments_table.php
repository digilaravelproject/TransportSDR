<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('online_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Payment gateway / mode
            $table->enum('gateway', [
                'razorpay',
                'paytm',
                'phonepe',
                'googlepay',
                'upi_direct',
                'neft',
                'rtgs',
                'imps',
                'bank_transfer',
                'other',
            ]);

            // Reference
            $table->string('reference_type')->nullable(); // Trip, Lead, Corporate
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number')->nullable();

            // Payment details
            $table->string('transaction_id')->nullable()->unique();
            $table->string('gateway_order_id')->nullable();
            $table->string('gateway_payment_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->string('currency')->default('INR');

            // Payer details
            $table->string('payer_name')->nullable();
            $table->string('payer_contact', 15)->nullable();
            $table->string('payer_upi_id')->nullable();
            $table->string('payer_bank')->nullable();

            // Status
            $table->enum('status', [
                'pending',
                'success',
                'failed',
                'refunded',
                'partially_refunded',
            ])->default('pending');

            $table->decimal('refund_amount', 12, 2)->default(0);
            $table->timestamp('paid_at')->nullable();
            $table->text('gateway_response')->nullable(); // raw JSON
            $table->text('failure_reason')->nullable();

            // Alert sent
            $table->boolean('alert_sent')->default(false);
            $table->timestamp('alert_sent_at')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'gateway']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('online_payments');
    }
};
