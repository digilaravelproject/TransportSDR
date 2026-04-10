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
        Schema::create('staff_advances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('staff_id')->constrained('staff')->cascadeOnDelete();

            $table->decimal('amount', 10, 2);
            $table->date('advance_date');
            $table->string('reason')->nullable();
            $table->enum('payment_mode', ['cash', 'bank', 'upi'])->default('cash');
            $table->string('transaction_ref')->nullable();

            // Deduction info
            $table->boolean('is_deducted')->default(false);
            $table->foreignId('salary_id')->nullable()->constrained('staff_salaries')->nullOnDelete();
            $table->date('deducted_on')->nullable();

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
        Schema::dropIfExists('staff_advances');
    }
};
