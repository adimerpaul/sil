<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * valor_final era double, así que solo aceptaba números. Los rangos con lista
     * de valores (REACTIVO, POSITIVO, etc.) guardan texto, por eso pasa a varchar.
     * Las comparaciones numéricas (fuera de rango) filtran por valores numéricos.
     */
    public function up(): void
    {
        Schema::table('resultado_laboratorios', function (Blueprint $table) {
            $table->string('valor_final', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('resultado_laboratorios', function (Blueprint $table) {
            $table->double('valor_final')->nullable()->change();
        });
    }
};
