<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->string('vendor_name');
            $table->string('contract_name')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->string('duty_type')->nullable();
            $table->string('vehicle_type')->nullable();
            $table->integer('quantity')->default(0);
            $table->decimal('monthly_amount', 14, 2)->default(0);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('vendor_vehicle', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vendor_id')->index();
            $table->unsignedBigInteger('vehicle_id')->index();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('assigned_by')->nullable();
            $table->timestamps();
        });

        Schema::create('vendor_bills', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('vendor_id')->index();
            $table->string('invoice_number')->nullable();
            $table->decimal('amount', 14, 2)->default(0);
            $table->date('billing_date')->nullable();
            $table->string('status')->default('pending');
            $table->string('file_path')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_bills');
        Schema::dropIfExists('vendor_vehicle');
        Schema::dropIfExists('vendors');
    }
};
