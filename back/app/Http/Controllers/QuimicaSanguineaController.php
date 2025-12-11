<?php

namespace App\Http\Controllers;

use App\Models\QuimicaSanguinea;
use App\Models\Solicitude;
use App\Models\Area;
use Illuminate\Http\Request;

class QuimicaSanguineaController extends Controller
{
    /**
     * Devuelve datos de cabecera de la solicitud + química sanguínea (si existe)
     * + rangos del área Química Sanguínea y Serología.
     */
    public function showBySolicitude($solicitudeId)
    {
        $solicitud = Solicitude::with([
            'paciente',
            'doctor',
            'servicios.area',
        ])->findOrFail($solicitudeId);

        $quimica = QuimicaSanguinea::firstOrNew([
            'solicitude_id' => $solicitudeId,
        ]);

        // Buscar área de Química Sanguínea
        $areaQuimica = Area::where('title', 'QUÍMICA SANGUÍNEA Y SEROLOGÍA')
            ->orWhere('title', 'Química Sanguínea y Serología')
            ->first();

        $rangos = [];
        if ($areaQuimica) {
            $rangos = $areaQuimica->rangos()
                ->orderBy('id')
                ->get();
        }

        return response()->json([
            'solicitud' => $solicitud,
            'quimica'   => $quimica,
            'rangos'    => $rangos,
        ]);
    }

    /**
     * UPSERT: crea o actualiza la química sanguínea de una solicitud.
     */
    public function upsert(Request $request, $solicitudeId)
    {
        $data = $request->all();
        $data['solicitude_id'] = $solicitudeId;

        $quimica = QuimicaSanguinea::updateOrCreate(
            ['solicitude_id' => $solicitudeId],
            $data
        );

        return response()->json($quimica);
    }

    /**
     * Eliminar registro de química sanguínea de una solicitud.
     */
    public function destroyBySolicitude($solicitudeId)
    {
        $quimica = QuimicaSanguinea::where('solicitude_id', $solicitudeId)->firstOrFail();
        $quimica->delete();

        return response()->json(['message' => 'Química sanguínea eliminada']);
    }
}
