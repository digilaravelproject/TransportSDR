<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'assigned_vehicles')) {
                $table->json('assigned_vehicles')->nullable()->after('vehicle_id');
            }
            if (!Schema::hasColumn('trips', 'assigned_drivers')) {
                $table->json('assigned_drivers')->nullable()->after('assigned_vehicles');
            }
        });
    }

    public function down(): void
    {
        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'assigned_drivers')) {
                $table->dropColumn('assigned_drivers');
            }
            if (Schema::hasColumn('trips', 'assigned_vehicles')) {
                $table->dropColumn('assigned_vehicles');
            }
        });
    }
};
