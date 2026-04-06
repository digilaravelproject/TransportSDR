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
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('company_name');
            $table->string('email')->unique();
            $table->string('phone', 15);
            $table->string('gstin', 15)->nullable();
            $table->text('address')->nullable();
            $table->string('logo_path')->nullable();
            $table->enum('plan', ['basic', 'pro', 'enterprise'])->default('basic');
            $table->unsignedInteger('max_vehicles')->default(5);
            $table->unsignedInteger('max_trips_per_month')->default(50);
            $table->boolean('is_active')->default(true);
            $table->timestamp('plan_expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenants');
    }
};
