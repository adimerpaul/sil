<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('servicio_rangos', function (Blueprint $table) {
            // Lista de valores seleccionables para la variable (JSON de strings).
            // Si es null o vacío, el valor se escribe libremente.
            $table->text('opciones')->nullable()->after('nombre_variable');
        });
    }

    public function down(): void
    {
        Schema::table('servicio_rangos', function (Blueprint $table) {
            $table->dropColumn('opciones');
        });
    }
};
