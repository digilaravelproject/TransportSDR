<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('template_categories')->cascadeOnDelete();
            $table->string('name');                        // Invoice with GST
            $table->string('slug')->unique();              // invoice-with-gst
            $table->text('description')->nullable();
            $table->string('thumbnail')->nullable();       // preview image
            $table->string('blade_view');                  // pdf.templates.invoice-gst
            $table->json('variables')->nullable();         // [{key,label,type}]
            $table->json('sample_data')->nullable();       // preview ke liye dummy data
            $table->boolean('is_active')->default(true);
            $table->boolean('is_default')->default(false); // category ka default template
            $table->integer('sort_order')->default(0);
            $table->integer('usage_count')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
