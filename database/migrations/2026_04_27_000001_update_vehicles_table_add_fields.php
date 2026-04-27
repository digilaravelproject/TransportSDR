<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Add new fields used by UI
            $table->integer('model_year')->nullable()->after('seating_capacity');
            $table->decimal('per_km_price', 10, 2)->nullable()->after('model_year');
            $table->decimal('ac_price_per_km', 10, 2)->nullable()->after('per_km_price');

            $table->string('rc_number')->nullable()->after('ac_price_per_km');
            $table->date('rc_expiry')->nullable()->after('rc_number');

            $table->string('insurance_number')->nullable()->after('rc_expiry');
            $table->date('insurance_expiry')->nullable()->after('insurance_number');

            $table->string('permit_number')->nullable()->after('insurance_expiry');
            $table->date('permit_expiry')->nullable()->after('permit_number');

            // Remove fields not shown on UI (if they exist)
            if (Schema::hasColumn('vehicles', 'make')) {
                $table->dropColumn('make');
            }
            if (Schema::hasColumn('vehicles', 'model')) {
                $table->dropColumn('model');
            }
            if (Schema::hasColumn('vehicles', 'fuel_type')) {
                $table->dropColumn('fuel_type');
            }
            if (Schema::hasColumn('vehicles', 'current_km')) {
                $table->dropColumn('current_km');
            }
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            // Recreate dropped columns (best-effort)
            if (! Schema::hasColumn('vehicles', 'make')) {
                $table->string('make')->nullable()->after('seating_capacity');
            }
            if (! Schema::hasColumn('vehicles', 'model')) {
                $table->string('model')->nullable()->after('make');
            }
            if (! Schema::hasColumn('vehicles', 'fuel_type')) {
                $table->string('fuel_type')->nullable()->after('model');
            }
            if (! Schema::hasColumn('vehicles', 'current_km')) {
                $table->decimal('current_km', 12, 2)->nullable()->after('fuel_type');
            }

            $table->dropColumn([
                'model_year', 'per_km_price', 'ac_price_per_km',
                'rc_number', 'rc_expiry', 'insurance_number', 'insurance_expiry',
                'permit_number', 'permit_expiry'
            ]);
        });
    }
};
