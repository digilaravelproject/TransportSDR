<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('shift_driver', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained('shifts')->cascadeOnDelete();
            $table->foreignId('driver_id')->constrained('staff')->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['shift_id', 'driver_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('shift_driver');
    }
};
