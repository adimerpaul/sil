<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Acuse del doctor sobre el resultado: cuándo lo vio y cuándo lo aceptó.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dateTime('doctor_visto_at')->nullable()->after('fecha_finalizacion');
            $table->dateTime('doctor_aceptado_at')->nullable()->after('doctor_visto_at');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropColumn(['doctor_visto_at', 'doctor_aceptado_at']);
        });
    }
};
