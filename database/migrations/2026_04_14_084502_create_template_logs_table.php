<?php
// database/migrations/xxxx_create_template_logs_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('template_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();

            // What document was generated
            $table->enum('template_type', [
                'invoice_gst',
                'invoice_non_gst',
                'letterhead',
                'quotation',
                'einvoice',
            ]);

            // Reference — which model it belongs to
            $table->string('reference_type')->nullable(); // Trip, Lead, Corporate
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->string('reference_number')->nullable(); // TRP-2026-0001

            // File
            $table->string('file_path')->nullable();
            $table->string('file_name')->nullable();

            // E-invoice GST portal fields
            $table->string('irn')->nullable();           // Invoice Reference Number
            $table->string('ack_number')->nullable();    // Acknowledgement Number
            $table->timestamp('ack_date')->nullable();
            $table->string('qr_code_path')->nullable();
            $table->enum('einvoice_status', [
                'not_uploaded',
                'uploaded',
                'failed',
                'cancelled',
            ])->default('not_uploaded');
            $table->text('einvoice_response')->nullable(); // raw API response

            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->timestamps();

            $table->index(['tenant_id', 'template_type']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('template_logs');
    }
};
