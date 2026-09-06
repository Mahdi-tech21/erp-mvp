<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->enum('doc_type', ['sales_invoice', 'purchase_invoice']);
            $table->string('number')->nullable();
            $table->foreignId('party_id')->constrained('parties')->restrictOnDelete();
            $table->date('doc_date');
            $table->date('due_date')->nullable();
            $table->enum('status', ['draft', 'posted', 'partial', 'settled', 'void'])
                ->default('draft');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('tax_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2)->default(0);
            $table->decimal('settled_total', 12, 2)->default(0);
            $table->string('external_ref')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('posted_at')->nullable();
            $table->timestamps();

            $table->unique(['doc_type', 'number']);
            $table->index(['party_id', 'doc_type', 'status']);
            $table->index(['doc_type', 'doc_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
