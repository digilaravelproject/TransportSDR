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
        Schema::create('vehicle_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vehicle_id')->constrained()->cascadeOnDelete();

            $table->enum('document_type', [
                'rc',             // Registration Certificate
                'insurance',
                'pollution',      // PUC
                'permit',
                'fitness',
                'tax',
                'other',
            ]);

            $table->string('document_number')->nullable();
            $table->string('document_path')->nullable(); // file upload
            $table->date('issue_date')->nullable();
            $table->date('expiry_date');

            // Alert before expiry (days)
            $table->unsignedInteger('alert_before_days')->default(30);
            $table->boolean('is_expired')->default(false);

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_documents');
    }
};
