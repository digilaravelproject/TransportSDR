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
        Schema::table('staff', function (Blueprint $col) {
            $col->string('salary_type')->default('monthly')->after('staff_type'); // monthly, daily
            $col->string('work_shift')->nullable()->after('salary_type'); // e.g., Day Shift
            $col->string('assigned_vehicle')->nullable()->after('work_shift');
            $col->string('badge_number')->nullable()->after('license_type');
            $col->date('badge_expiry')->nullable()->after('badge_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff', function (Blueprint $table) {
            //
        });
    }
};
