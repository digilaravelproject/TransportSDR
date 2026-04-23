<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');                     // Invoice, Letterhead, Quotation
            $table->string('slug')->unique();           // invoice, letterhead, quotation
            $table->text('description')->nullable();
            $table->string('icon')->nullable();         // fas fa-file-invoice
            $table->string('color')->default('#6c757d'); // badge color
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_categories');
    }
};
