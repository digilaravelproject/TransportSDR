<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'plan',
                'max_vehicles',
                'max_trips_per_month',
                'plan_expires_at',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('plan', ['basic', 'pro', 'enterprise'])->default('basic')->after('logo_path');
            $table->unsignedInteger('max_vehicles')->default(5)->after('plan');
            $table->unsignedInteger('max_trips_per_month')->default(50)->after('max_vehicles');
            $table->timestamp('plan_expires_at')->nullable()->after('max_trips_per_month');
        });
    }
};
