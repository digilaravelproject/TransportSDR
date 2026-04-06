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
        Schema::table('leads', function (Blueprint $table) {
            $table->decimal('tax_amount', 12, 2)->default(0)->after('gst_percent');
            $table->decimal('discount', 12, 2)->default(0)->after('tax_amount');
            $table->decimal('total_with_tax', 12, 2)->default(0)->after('discount');
            $table->string('quotation_path')->nullable()->after('total_with_tax');
            $table->string('bill_path')->nullable()->after('quotation_path');
            $table->timestamp('quotation_sent_at')->nullable()->after('bill_path');
        });
    }

    public function down(): void
    {
        Schema::table('leads', function (Blueprint $table) {
            $table->dropColumn([
                'tax_amount',
                'discount',
                'total_with_tax',
                'quotation_path',
                'bill_path',
                'quotation_sent_at',
            ]);
        });
    }
};
