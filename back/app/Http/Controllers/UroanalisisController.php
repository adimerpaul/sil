<?php

namespace App\Http\Controllers;

use App\Models\Uroanalisis;
use App\Models\Solicitude;
use Illuminate\Http\Request;

class UroanalisisController extends Controller
{
    /**
     * Devuelve cabecera de solicitud + uroanálisis (si existe).
     */
    public function showBySolicitude($solicitudeId)
    {
        $solicitud = Solicitude::with([
            'paciente',
            'doctor',
            'servicios.area',
        ])->findOrFail($solicitudeId);

        $uro = Uroanalisis::firstOrNew([
            'solicitude_id' => $solicitudeId,
        ]);

        return response()->json([
            'solicitud'   => $solicitud,
            'uroanalisis' => $uro,
        ]);
    }

    /**
     * Crea o actualiza el uroanálisis de una solicitud.
     */
    public function upsert(Request $request, $solicitudeId)
    {
        $data = $request->all();
        $data['solicitude_id'] = $solicitudeId;

        $uro = Uroanalisis::updateOrCreate(
            ['solicitude_id' => $solicitudeId],
            $data
        );

        return response()->json($uro);
    }

    /**
     * Elimina el uroanálisis de una solicitud.
     */
    public function destroyBySolicitude($solicitudeId)
    {
        $uro = Uroanalisis::where('solicitude_id', $solicitudeId)->firstOrFail();
        $uro->delete();

        return response()->json([
            'message' => 'Uroanálisis eliminado',
        ]);
    }
}
