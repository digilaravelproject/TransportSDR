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
        Schema::create('plans', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->enum('duration', ['monthly', 'yearly', 'lifetime'])->default('monthly');
            $table->integer('billing_cycle_days')->default(30);
            $table->integer('max_vehicles')->nullable(); // null means unlimited
            $table->integer('max_trips_per_month')->nullable(); // null means unlimited
            $table->integer('max_staff')->nullable();
            $table->json('features')->nullable(); // Store features as JSON array
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('plans');
    }
};
