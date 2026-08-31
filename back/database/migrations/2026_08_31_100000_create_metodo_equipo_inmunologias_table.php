<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Catálogo de inmunología: métodos y equipos son listas independientes,
        // se distinguen por la columna tipo (METODO / EQUIPO).
        Schema::create('metodo_equipo_inmunologias', function (Blueprint $table) {
            $table->id();
            $table->string('tipo', 10);
            $table->string('nombre', 150);
            $table->timestamps();
            $table->softDeletes();

            $table->index('tipo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('metodo_equipo_inmunologias');
    }
};
