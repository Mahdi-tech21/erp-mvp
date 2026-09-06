<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->string('doctor_name');
            $table->dateTime('starts_at');
            $table->integer('duration_minutes')->default(30);
            $table->foreignId('service_item_id')->nullable()->constrained('items')->nullOnDelete();
            $table->enum('status', ['scheduled', 'done', 'cancelled', 'invoiced'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
