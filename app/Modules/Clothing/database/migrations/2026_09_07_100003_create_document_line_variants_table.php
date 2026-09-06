<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Module-owned link: attaches an item variant to a core document_line
 * WITHOUT adding a column to document_lines.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_line_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_line_id')->constrained('document_lines')->cascadeOnDelete();
            $table->foreignId('item_variant_id')->constrained('item_variants')->restrictOnDelete();
            $table->timestamps();

            $table->unique('document_line_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_line_variants');
    }
};
