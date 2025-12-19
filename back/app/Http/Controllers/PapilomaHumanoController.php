<?php

namespace App\Http\Controllers;

use App\Models\PapilomaHumano;
use App\Models\Solicitude;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class PapilomaHumanoController extends Controller
{
    public function showBySolicitude($id)
    {
        $solicitud = Solicitude::with(['paciente', 'doctor'])
            ->findOrFail($id);

        $papiloma = PapilomaHumano::firstOrNew([
            'solicitude_id' => $id
        ]);

        return response()->json([
            'solicitud' => $solicitud,
            'papiloma'  => $papiloma,
        ]);
    }

    public function upsert(Request $request, $id)
    {
        $data = $request->all();
        $data['solicitude_id'] = $id;

        $registro = PapilomaHumano::updateOrCreate(
            ['solicitude_id' => $id],
            $data
        );

        return response()->json($registro);
    }

    public function destroyBySolicitude($id)
    {
        $registro = PapilomaHumano::where('solicitude_id', $id)->firstOrFail();
        $registro->delete();

        return response()->json(['message' => 'Registro eliminado']);
    }

    public function pdfBySolicitude($code)
    {
        $id = PapilomaHumano::where('codigo', $code)
            ->value('solicitude_id');
        $solicitud = Solicitude::with(['paciente', 'doctor'])->findOrFail($id);
        $papiloma = PapilomaHumano::where('solicitude_id', $id)->first();

        $pdf = Pdf::loadView('pdf.papiloma_humano', [
            'solicitud' => $solicitud,
            'papiloma'  => $papiloma,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream('VPH_'.$solicitud->nro_registro.'.pdf');
    }
}
