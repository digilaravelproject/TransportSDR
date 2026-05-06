<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Convert existing enum/string `staff_type` values to `role_modules.id` where possible,
     * and change the column to unsignedBigInteger with nullable FK.
     */
    public function up(): void
    {
        // Add a temporary column to hold the new integer role id
        Schema::table('staff', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_type_new')->nullable()->after('emergency_contact_name');
        });

        // Map existing staff_type string values to role_modules.id (by matching name)
        $staffs = DB::table('staff')->select('id', 'staff_type')->get();

        foreach ($staffs as $s) {
            if (empty($s->staff_type)) {
                continue;
            }

            $role = DB::table('role_modules')->where('name', $s->staff_type)->first();

            if (!$role) {
                $role = DB::table('role_modules')->whereRaw('LOWER(name) = ?', [strtolower($s->staff_type)])->first();
            }

            if ($role) {
                DB::table('staff')->where('id', $s->id)->update(['staff_type_new' => $role->id]);
            }
        }

        // Drop the old column and rename the new
        Schema::table('staff', function (Blueprint $table) {
            // Use dropColumn regardless of type (works for enum/string)
            $table->dropColumn('staff_type');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->unsignedBigInteger('staff_type')->nullable()->after('emergency_contact_name');
        });

        // copy values
        DB::statement('UPDATE staff SET staff_type = staff_type_new');

        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('staff_type_new');
            $table->foreign('staff_type')->references('id')->on('role_modules')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     * Convert `staff_type` back to a string column populated with role names where possible.
     */
    public function down(): void
    {
        // add temporary string column
        Schema::table('staff', function (Blueprint $table) {
            $table->string('staff_type_old')->nullable()->after('emergency_contact_name');
        });

        // Map role ids back to names
        $staffs = DB::table('staff')->select('id', 'staff_type')->get();

        foreach ($staffs as $s) {
            if (empty($s->staff_type)) {
                continue;
            }

            $role = DB::table('role_modules')->where('id', $s->staff_type)->first();
            if ($role) {
                DB::table('staff')->where('id', $s->id)->update(['staff_type_old' => $role->name]);
            }
        }

        Schema::table('staff', function (Blueprint $table) {
            $table->dropForeign(['staff_type']);
            $table->dropColumn('staff_type');
        });

        Schema::table('staff', function (Blueprint $table) {
            $table->string('staff_type')->nullable()->after('emergency_contact_name');
        });

        DB::statement('UPDATE staff SET staff_type = staff_type_old');

        Schema::table('staff', function (Blueprint $table) {
            $table->dropColumn('staff_type_old');
        });
    }
};
