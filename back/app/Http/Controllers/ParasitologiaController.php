<?php

namespace App\Http\Controllers;

use App\Models\Parasitologia;
use App\Models\Solicitude;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ParasitologiaController extends Controller
{
    public function showBySolicitude($solicitudeId)
    {
        $solicitud = Solicitude::with([
            'paciente',
            'doctor',
            'servicios.area',
        ])->findOrFail($solicitudeId);

        $parasitologia = Parasitologia::firstOrNew([
            'solicitude_id' => $solicitudeId,
        ]);

        return response()->json([
            'solicitud'     => $solicitud,
            'parasitologia' => $parasitologia,
        ]);
    }

    public function upsert(Request $request, $solicitudeId)
    {
        $data = $request->all();
        $data['solicitude_id'] = $solicitudeId;

        // si es SIMPLE, limpiamos seriado
        if (($data['tipo'] ?? 'SIMPLE') === 'SIMPLE') {
            $data['descripcion_muestra_1'] = null;
            $data['descripcion_muestra_2'] = null;
            $data['descripcion_muestra_3'] = null;
        } else { // SERIADO
            $data['descripcion_muestra'] = null;
        }

        $parasitologia = Parasitologia::updateOrCreate(
            ['solicitude_id' => $solicitudeId],
            $data
        );

        return response()->json($parasitologia);
    }

    public function destroyBySolicitude($solicitudeId)
    {
        $parasitologia = Parasitologia::where('solicitude_id', $solicitudeId)->firstOrFail();
        $parasitologia->delete();

        return response()->json(['message' => 'Parasitología eliminada']);
    }

    public function pdfBySolicitude($solicitudeId)
    {
        $solicitud = Solicitude::with([
            'paciente',
            'doctor',
            'servicios.area',
        ])->findOrFail($solicitudeId);

        $parasitologia = Parasitologia::where('solicitude_id', $solicitudeId)->first();

        $pdf = Pdf::loadView('pdf.parasitologia', [
            'solicitud'     => $solicitud,
            'parasitologia' => $parasitologia,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream('PARASITOLOGIA_'.$solicitud->nro_registro.'.pdf');
    }
}
