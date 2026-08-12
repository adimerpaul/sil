<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    protected string $variable = 'citoquimico_albumina';

    protected string $prestacion = 'CITOQUÍMICO LÍQUIDO CEFALORRAQUÍDEO Y OTROS LÍQUIDOS';

    public function up(): void
    {
        $norm = fn ($v) => mb_strtolower(trim(preg_replace('/\s+/u', ' ', (string) $v)));

        $datoId = DB::table('datos_quimica_sanguinea')->where('variable', $this->variable)->value('id');

        if (! $datoId) {
            $datoId = DB::table('datos_quimica_sanguinea')->insertGetId([
                'variable' => $this->variable,
                'nombre' => 'Albúmina (citoquímico)',
                'seccion' => 'Citoquímico',
                // entre proteínas totales (640) y LDH (650), como en el formato impreso
                'orden' => 645,
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $servicioId = DB::table('servicios')->whereNull('deleted_at')->get(['id', 'nombre'])
            ->first(fn ($s) => $norm($s->nombre) === $norm($this->prestacion))?->id;

        if ($servicioId) {
            DB::table('datos_quimica_sanguinea_prestacion')->insertOrIgnore([
                'dato_quimica_sanguinea_id' => $datoId,
                'servicio_id' => $servicioId,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // el cascadeOnDelete de la FK limpia datos_quimica_sanguinea_prestacion
        DB::table('datos_quimica_sanguinea')->where('variable', $this->variable)->delete();
    }
};
