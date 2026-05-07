<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\UnidadSolicitante;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('unidad_solicitante_id')
                ->nullable()
                ->after('establecimiento_id')
                ->constrained('unidad_solicitantes')
                ->nullOnDelete();
        });

        UnidadSolicitante::firstOrCreate(
            ['nombre' => 'DEPTO. DE INGENIERÍA Y MANTENIMIENTO HGSJDD BLOQUE ORURO COREA']
        );
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['unidad_solicitante_id']);
            $table->dropColumn('unidad_solicitante_id');
        });
    }
};
