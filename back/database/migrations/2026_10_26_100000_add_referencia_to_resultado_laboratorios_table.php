<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rango de referencia editado para ESTE resultado. Si es null se imprime
     * la referencia definida en area_rangos (sub-rangos + texto libre).
     */
    public function up(): void
    {
        Schema::table('resultado_laboratorios', function (Blueprint $table) {
            $table->text('referencia')->nullable()->after('interpretacion');
        });
    }

    public function down(): void
    {
        Schema::table('resultado_laboratorios', function (Blueprint $table) {
            $table->dropColumn('referencia');
        });
    }
};
