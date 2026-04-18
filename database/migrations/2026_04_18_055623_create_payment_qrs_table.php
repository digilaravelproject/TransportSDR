<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_qrs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // What is this QR for
            $table->enum('qr_type', [
                'trip_payment',
                'advance_collection',
                'corporate_payment',
                'general',
            ])->default('general');

            $table->string('reference_type')->nullable();
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number')->nullable();

            // UPI details
            $table->string('upi_id');
            $table->string('payee_name');
            $table->decimal('amount', 12, 2)->nullable(); // null = open amount
            $table->string('transaction_note')->nullable();
            $table->string('currency')->default('INR');

            // QR file
            $table->string('qr_image_path')->nullable();
            $table->string('upi_deep_link')->nullable(); // upi://pay?pa=...

            // Validity
            $table->timestamp('expires_at')->nullable();
            $table->boolean('is_active')->default(true);

            // Alert settings
            $table->boolean('send_alert')->default(true);
            $table->string('alert_contact', 15)->nullable(); // send to this number
            $table->boolean('alert_sent')->default(false);

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_qrs');
    }
};
