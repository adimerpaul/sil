<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Interpretación del rango: texto que se imprime bajo el resultado en el PDF.
     * Se escribe al crear/editar el rango. No se confunde con `interpretacion`,
     * que es el texto libre del rango de referencia.
     */
    public function up(): void
    {
        Schema::table('area_rangos', function (Blueprint $table) {
            $table->text('interpretacion_resultado')->nullable()->after('interpretacion');
        });
    }

    public function down(): void
    {
        Schema::table('area_rangos', function (Blueprint $table) {
            $table->dropColumn('interpretacion_resultado');
        });
    }
};
