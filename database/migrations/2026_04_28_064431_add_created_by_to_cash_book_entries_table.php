<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cash_book_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')
                ->nullable()
                ->after('receipt_path');

            // $table->index('created_by');
            if (!Schema::hasColumn('cash_book_entries', 'created_by')) {
                $table->unsignedBigInteger('created_by')->nullable()->after('receipt_path');
            }

            // Optional foreign key (recommended)
            // $table->foreign('created_by')
            //       ->references('id')
            //       ->on('users')
            //       ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cash_book_entries', function (Blueprint $table) {

            // Drop foreign key first if used
            // $table->dropForeign(['created_by']);

            $table->dropIndex(['created_by']);
            $table->dropColumn('created_by');
        });
    }
};
