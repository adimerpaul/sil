<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hematologia_opciones', function (Blueprint $table) {
            $table->id();
            $table->string('seccion');   // HEMOGRAMA | COAGULOGRAMA
            $table->string('tipo');      // METODO | EQUIPO
            $table->string('nombre');
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->unique(['seccion', 'tipo', 'nombre'], 'hematologia_opcion_unica');
            $table->index(['seccion', 'tipo']);
        });

        $this->seedOpciones();
    }

    public function down(): void
    {
        Schema::dropIfExists('hematologia_opciones');
    }

    /**
     * Valores que estaban fijos en el select de Hematologia.vue.
     */
    protected function seedOpciones(): void
    {
        $opciones = [
            ['HEMOGRAMA', 'METODO', ['Automática', 'SemiAutomática', 'Manual']],
            ['HEMOGRAMA', 'EQUIPO', ['Mindray BC 5130', 'Mindray BC 3000 Plus', 'Otro']],
            ['COAGULOGRAMA', 'METODO', ['Automática', 'SemiAutomática', 'Manual']],
            ['COAGULOGRAMA', 'EQUIPO', ['Mindray BC 3510', 'Coatro', 'Otro']],
        ];

        foreach ($opciones as [$seccion, $tipo, $nombres]) {
            $orden = 0;
            foreach ($nombres as $nombre) {
                $orden += 10;
                DB::table('hematologia_opciones')->insertOrIgnore([
                    'seccion' => $seccion,
                    'tipo' => $tipo,
                    'nombre' => $nombre,
                    'orden' => $orden,
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
