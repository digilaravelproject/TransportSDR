<?php
// database/migrations/xxxx_update_type_in_otps_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::statement(
            "ALTER TABLE otps MODIFY COLUMN type ENUM('login','forgot_password','registration') NOT NULL"
        );
    }

    public function down(): void
    {
        \Illuminate\Support\Facades\DB::statement(
            "ALTER TABLE otps MODIFY COLUMN type ENUM('login','forgot_password') NOT NULL"
        );
    }
};
