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
        Schema::create('staff_performance_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('staff_id')->constrained()->onDelete('cascade');
            $table->decimal('overall_score', 3, 1)->default(0.0);
            $table->integer('on_time_percentage')->default(0);
            $table->decimal('fuel_efficiency', 5, 2)->default(0.0);
            $table->integer('safety_violations')->default(0);
            $table->integer('customer_satisfaction')->default(0);
            $table->string('month'); // e.g., 2024-04
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_performance_metrics');
    }
};
