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
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->time('start_time');
            $table->time('end_time');
            $table->enum('type', ['regular', 'overtime', 'night', 'custom'])->default('regular');
            $table->json('days')->nullable(); // Days of week [1,2,3,4,5] = Mon-Fri
            $table->integer('duration_hours')->nullable(); // Calculated duration
            $table->boolean('is_active')->default(true);
            $table->integer('max_drivers')->nullable(); // Max drivers allowed in this shift
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('type');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};
