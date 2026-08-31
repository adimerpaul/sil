<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Vincula usuarios del sistema con los doctores del hospital: un usuario puede
 * representar a uno o varios doctores y así ver las solicitudes que ellos pidieron.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doctor_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('doctor_id')->constrained('doctors')->onDelete('cascade');
            $table->timestamps();
            $table->unique(['user_id', 'doctor_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doctor_user');
    }
};
