<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_book_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // Entry type
            $table->enum('entry_type', ['income', 'expense']);
            $table->enum('payment_mode', ['cash', 'online', 'cheque', 'upi', 'bank_transfer', 'neft', 'rtgs', 'imps']);

            // Category
            $table->enum('category', [
                // Income categories
                'trip_payment',
                'advance_received',
                'corporate_payment',
                'lead_advance',
                'other_income',
                // Expense categories
                'fuel_expense',
                'maintenance_expense',
                'salary_payment',
                'da_payment',
                'advance_given',
                'toll_charge',
                'office_expense',
                'vehicle_insurance',
                'vehicle_tax',
                'other_expense',
            ]);

            // Reference linking
            $table->string('reference_type')->nullable(); // Trip, Lead, Staff, Vehicle etc.
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number')->nullable(); // TRP-2026-0001

            // Amount
            $table->decimal('amount', 12, 2);
            $table->decimal('opening_balance', 12, 2)->default(0);
            $table->decimal('closing_balance', 12, 2)->default(0);

            // Details
            $table->string('description');
            $table->date('entry_date');
            $table->string('party_name')->nullable();       // Customer/Vendor name
            $table->string('party_contact', 15)->nullable();

            // Online payment details
            $table->string('transaction_id')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('cheque_number')->nullable();
            $table->date('cheque_date')->nullable();

            // Status
            $table->enum('status', ['confirmed', 'pending', 'bounced', 'cancelled'])->default('confirmed');

            $table->text('notes')->nullable();
            $table->string('receipt_path')->nullable();    // uploaded receipt image
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['tenant_id', 'entry_date']);
            $table->index(['tenant_id', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_book_entries');
    }
};
