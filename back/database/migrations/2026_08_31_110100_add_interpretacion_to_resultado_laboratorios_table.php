<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Interpretación elegida para ESTE resultado (Reactivo, Positivo, ...).
     * No se confunde con area_rangos.interpretacion_resultado, que es el texto
     * fijo definido en el rango.
     */
    public function up(): void
    {
        Schema::table('resultado_laboratorios', function (Blueprint $table) {
            $table->string('interpretacion', 50)->nullable()->after('observacion');
        });
    }

    public function down(): void
    {
        Schema::table('resultado_laboratorios', function (Blueprint $table) {
            $table->dropColumn('interpretacion');
        });
    }
};
