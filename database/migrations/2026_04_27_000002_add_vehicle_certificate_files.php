<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('rc_file')->nullable()->after('rc_expiry');
            $table->string('insurance_file')->nullable()->after('insurance_expiry');
            $table->string('permit_file')->nullable()->after('permit_expiry');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['rc_file', 'insurance_file', 'permit_file']);
        });
    }
};
