<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_variant_id')->constrained('item_variants')->cascadeOnDelete();
            $table->enum('direction', ['in', 'out']);
            $table->integer('qty');
            $table->decimal('unit_cost', 12, 2)->nullable();
            $table->string('reference_type');
            $table->unsignedBigInteger('reference_id')->nullable();
            $table->dateTime('moved_at');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['item_variant_id', 'moved_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
