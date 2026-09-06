<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('item_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->string('size');
            $table->string('color');
            $table->string('sku')->unique();
            $table->integer('stock_qty')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->timestamps();

            $table->index('item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_variants');
    }
};
