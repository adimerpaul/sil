<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Marca si el rango vinculado a la prestación se captura con interpretación
     * (Reactivo, No reactivo, Positivo, ...) en la analítica de inmunología.
     */
    public function up(): void
    {
        Schema::table('servicio_rangos', function (Blueprint $table) {
            $table->boolean('con_interpretacion')->default(false)->after('visible');
        });
    }

    public function down(): void
    {
        Schema::table('servicio_rangos', function (Blueprint $table) {
            $table->dropColumn('con_interpretacion');
        });
    }
};
