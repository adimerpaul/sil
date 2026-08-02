<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Visibilidad del resultado en el PDF, decidida por solicitud.
     * NULL = se respeta el valor por defecto del servicio (servicio_rangos.visible).
     */
    public function up(): void
    {
        Schema::table('resultado_laboratorios', function (Blueprint $table) {
            $table->boolean('visible')->nullable()->after('valor_final');
        });
    }

    public function down(): void
    {
        Schema::table('resultado_laboratorios', function (Blueprint $table) {
            $table->dropColumn('visible');
        });
    }
};
