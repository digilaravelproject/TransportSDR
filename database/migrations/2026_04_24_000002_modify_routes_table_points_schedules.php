<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            // if existing `stops` column exists, rename to `points`
            if (Schema::hasColumn('routes', 'stops')) {
                $table->renameColumn('stops', 'points');
            } else {
                $table->json('points')->nullable()->after('estimated_time');
            }

            // add schedules json
            if (!Schema::hasColumn('routes', 'schedules')) {
                $table->json('schedules')->nullable()->after('points');
            }

            // drop origin and destination columns if they exist
            if (Schema::hasColumn('routes', 'origin')) {
                $table->dropColumn('origin');
            }
            if (Schema::hasColumn('routes', 'destination')) {
                $table->dropColumn('destination');
            }
        });
    }

    public function down(): void
    {
        Schema::table('routes', function (Blueprint $table) {
            if (!Schema::hasColumn('routes', 'origin')) {
                $table->string('origin')->nullable()->after('name');
            }
            if (!Schema::hasColumn('routes', 'destination')) {
                $table->string('destination')->nullable()->after('origin');
            }

            if (Schema::hasColumn('routes', 'schedules')) {
                $table->dropColumn('schedules');
            }

            // if points exists, rename back to stops
            if (Schema::hasColumn('routes', 'points')) {
                // If the original 'stops' was present we renamed it; rename back.
                $table->renameColumn('points', 'stops');
            }
        });
    }
};
