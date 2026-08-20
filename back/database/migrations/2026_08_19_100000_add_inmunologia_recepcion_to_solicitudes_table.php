<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->date('inmunologia_fecha_recepcion')->nullable()->after('inmunologia_analitica_codigo');
            $table->text('inmunologia_comentario')->nullable()->after('inmunologia_fecha_recepcion');
        });
    }

    public function down(): void
    {
        Schema::table('solicitudes', function (Blueprint $table) {
            $table->dropColumn(['inmunologia_fecha_recepcion', 'inmunologia_comentario']);
        });
    }
};
