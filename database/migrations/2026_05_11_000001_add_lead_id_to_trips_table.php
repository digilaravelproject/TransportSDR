<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('trips', function (Blueprint $table) {
            if (!Schema::hasColumn('trips', 'lead_id')) {
                $table->unsignedBigInteger('lead_id')->nullable()->after('id')->index();
            }
        });
    }

    public function down()
    {
        Schema::table('trips', function (Blueprint $table) {
            if (Schema::hasColumn('trips', 'lead_id')) {
                $table->dropColumn('lead_id');
            }
        });
    }
};
