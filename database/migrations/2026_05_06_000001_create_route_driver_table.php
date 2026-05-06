<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('route_driver', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('route_id');
            $table->unsignedBigInteger('driver_id');
            $table->date('assigned_from')->nullable();
            $table->date('assigned_to')->nullable();
            $table->timestamps();

            $table->foreign('route_id')->references('id')->on('routes')->onDelete('cascade');
            $table->foreign('driver_id')->references('id')->on('staff')->onDelete('cascade');
            $table->unique(['route_id', 'driver_id', 'assigned_from', 'assigned_to'], 'route_driver_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('route_driver');
    }
};
