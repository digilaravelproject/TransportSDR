<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            // String se UnsignedBigInteger mein change kar rahe hain taaki ID save ho sake
            $table->unsignedBigInteger('work_shift')->nullable()->change();

            // Foreign key relation add kar rahe hain shifts table ke sath
            $table->foreign('work_shift')->references('id')->on('shifts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign(['work_shift']);
            $table->string('work_shift', 100)->nullable()->change();
        });
    }
};
