<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Solicitude;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InmunologiaAnaliticaController extends Controller
{
    private const AREA_ID = 6;

    /**
     * GET /inmunologia-analitica/solicitud/{id}
     * Devuelve la solicitud con sus prestaciones de inmunología y los rangos vinculados.
     */
    public function show($solicitudId)
    {
        $solicitud = Solicitude::findOrFail($solicitudId);

        // Prestaciones de inmunología seleccionadas en esta solicitud
        $servicioIds = DB::table('servicio_solicitudes')
            ->where('solicitude_id', $solicitudId)
            ->where('area_id', self::AREA_ID)
            ->pluck('servicio_id');

        $prestaciones = Servicio::with(['rangos' => function ($q) {
            $q->orderBy('area_rangos.id');
        }])
            ->whereIn('id', $servicioIds)
            ->orderBy('codigo')
            ->get();

        // Resultados ya guardados para esta solicitud en área 6
        $resultados = DB::table('resultado_laboratorios')
            ->where('solicitude_id', $solicitudId)
            ->where('area_id', self::AREA_ID)
            ->whereNull('deleted_at')
            ->get()
            ->keyBy('area_rango_id');

        // Agregar resultados existentes a cada rango
        $prestacionesConResultados = $prestaciones->map(function ($servicio) use ($resultados) {
            $rangos = $servicio->rangos->map(function ($rango) use ($resultados) {
                $resultado = $resultados->get($rango->id);
                return [
                    'id'              => $rango->id,
                    'rango_nombre'    => $rango->rango_nombre,
                    'unidad'          => $rango->unidad,
                    'interpretacion'  => $rango->interpretacion,
                    'rango_minimo'    => $rango->rango_minimo,
                    'rango_maximo'    => $rango->rango_maximo,
                    'metodo'          => $rango->metodo,
                    'muestra'         => $rango->muestra,
                    'marca'           => $rango->marca,
                    'perfil'          => $rango->perfil,
                    'nombre_variable' => $rango->pivot->nombre_variable,
                    'resultado'       => $resultado ? [
                        'id'          => $resultado->id,
                        'valor_final' => $resultado->valor_final,
                        'observacion' => $resultado->observacion,
                    ] : null,
                ];
            });

            return [
                'servicio_id' => $servicio->id,
                'nombre'      => $servicio->nombre,
                'metodo'      => $servicio->metodo,
                'subarea'     => $servicio->subarea,
                'rangos'      => $rangos,
            ];
        });

        return response()->json([
            'solicitud'    => [
                'id'               => $solicitud->id,
                'codigo'           => $solicitud->codigo,
                'paciente_nombre'  => $solicitud->paciente_nombre,
                'paciente_edad'    => $solicitud->paciente_edad,
                'paciente_genero'  => $solicitud->paciente_genero,
                'doctor_nombre'    => $solicitud->doctor_nombre,
                'fecha_solicitud'  => $solicitud->fecha_solicitud,
                'estado'           => $solicitud->estado,
            ],
            'prestaciones' => $prestacionesConResultados,
        ]);
    }

    /**
     * POST /inmunologia-analitica/solicitud/{id}/resultados
     * Guarda o actualiza los valores ingresados para cada rango.
     */
    public function saveResultados(Request $request, $solicitudId)
    {
        $solicitud = Solicitude::findOrFail($solicitudId);

        $data = $request->validate([
            'resultados'                => 'required|array',
            'resultados.*.area_rango_id'=> 'required|integer|exists:area_rangos,id',
            'resultados.*.valor_final'  => 'nullable|string|max:255',
            'resultados.*.observacion'  => 'nullable|string',
        ]);

        $now = now();

        foreach ($data['resultados'] as $item) {
            DB::table('resultado_laboratorios')->updateOrInsert(
                [
                    'solicitude_id' => $solicitudId,
                    'area_rango_id' => $item['area_rango_id'],
                    'area_id'       => self::AREA_ID,
                ],
                [
                    'valor_final' => $item['valor_final'] ?? null,
                    'observacion' => $item['observacion'] ?? null,
                    'updated_at'  => $now,
                    'created_at'  => $now,
                ]
            );
        }

        return response()->json(['message' => 'Resultados guardados']);
    }
}
