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
        Schema::table('staff', function (Blueprint $table) {
            $table->date('date_of_birth')->nullable()->after('email');
            $table->date('date_of_joining')->nullable()->after('date_of_birth');
            $table->text('address')->nullable()->after('date_of_joining');
            $table->string('emergency_contact', 15)->nullable()->after('address');
            $table->string('emergency_contact_name')->nullable()->after('emergency_contact');
            $table->string('license_type')->nullable()->after('license_expiry');
            $table->decimal('basic_salary', 10, 2)->default(0)->after('license_type');
            $table->decimal('da_per_day', 10, 2)->default(0)->after('basic_salary');
            $table->decimal('hra', 10, 2)->default(0)->after('da_per_day');
            $table->decimal('other_allowance', 10, 2)->default(0)->after('hra');
            $table->string('bank_name')->nullable()->after('other_allowance');
            $table->string('bank_account')->nullable()->after('bank_name');
            $table->string('bank_ifsc')->nullable()->after('bank_account');
            $table->text('notes')->nullable()->after('bank_ifsc');
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
