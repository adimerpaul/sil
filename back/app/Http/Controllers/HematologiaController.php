<?php

namespace App\Http\Controllers;

use App\Models\Hematologia;
use App\Models\Solicitude;
use App\Models\Area;
use Illuminate\Http\Request;

class HematologiaController extends Controller
{
    /**
     * Devuelve datos de cabecera de la solicitud + hematología (si existe)
     * + rangos del área Hematología.
     */
    public function showBySolicitude($solicitudeId)
    {
        // cabecera con relaciones que necesites en el front
        $solicitud = Solicitude::with([
            'paciente',
            'doctor',
            'servicios.area',
        ])->findOrFail($solicitudeId);

        $hematologia = Hematologia::firstOrNew([
            'solicitude_id' => $solicitudeId,
        ]);

        // Buscar área de hematología (ajusta según el nombre que tengas)
        $areaHemato = Area::where('title', 'HEMATOLOGÍA')
            ->orWhere('title', 'Hematología')
            ->first();
//        error_log('Área Hematología: ' . ($areaHemato ? $areaHemato->id : 'No encontrada'));

        $rangos = [];
        if ($areaHemato) {
            // usamos la relación ->rangos() que ya tienes en el modelo Area
            $rangos = $areaHemato->rangos()
                ->orderBy('id')
                ->get();
        }

        return response()->json([
            'solicitud'   => $solicitud,
            'hematologia' => $hematologia,
            'rangos'      => $rangos,
        ]);
    }

    /**
     * UPSERT: crea o actualiza la hematología de una solicitud.
     */
    public function upsert(Request $request, $solicitudeId)
    {
        $data = $request->all();
        $data['solicitude_id'] = $solicitudeId;

        $hematologia = Hematologia::updateOrCreate(
            ['solicitude_id' => $solicitudeId],
            $data
        );

        return response()->json($hematologia);
    }

    /**
     * (Opcional) eliminar registro de hematología de una solicitud.
     */
    public function destroyBySolicitude($solicitudeId)
    {
        $hematologia = Hematologia::where('solicitude_id', $solicitudeId)->firstOrFail();
        $hematologia->delete();

        return response()->json(['message' => 'Hematología eliminada']);
    }
}
