<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('corporate_fines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('corporate_id')->constrained('corporates')->cascadeOnDelete();
            $table->foreignId('duty_id')->nullable()->constrained('corporate_duties')->nullOnDelete();

            $table->string('reason');
            $table->decimal('amount', 10, 2);
            $table->date('fine_date');
            $table->enum('status', ['pending', 'deducted', 'waived'])->default('pending');
            $table->foreignId('payment_id')->nullable()->constrained('corporate_payments')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('corporate_fines');
    }
};
