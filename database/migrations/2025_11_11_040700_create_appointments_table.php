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
        Schema::create('appointments', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->foreignId('doctor_id')->constrained()->cascadeOnDelete();
            $table->string('patient_name');
            $table->string('patient_email');
            $table->string('patient_phone');
            $table->string('patient_id_number');
            $table->date('patient_birth_date');
            $table->text('reason')->nullable();
            $table->dateTime('appointment_date');
            $table->integer('duration_minutes');
            $table->enum('status', ['pendiente', 'confirmada', 'rechazada', 'completada'])->default('pendiente');
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('appointments');
    }
};
