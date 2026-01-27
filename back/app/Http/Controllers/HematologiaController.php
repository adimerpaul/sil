<?php

namespace App\Http\Controllers;

use App\Models\Hematologia;
use App\Models\ServicioSolicitude;
use App\Models\Solicitude;
use App\Models\Area;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

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
//            'servicios'
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
        $user = $request->user();

        $hematologia = Hematologia::updateOrCreate(
            [
                'solicitude_id' => $solicitudeId,
            ],
            array_merge($data, ['user_id' => $user->id])
        );

        $areaIdHemato = 1;
        ServicioSolicitude::where('solicitude_id', $solicitudeId)
            ->where('area_id', $areaIdHemato)
            ->update(['realizado' => 'REALIZADO']);


        $soliditude = Solicitude::find($solicitudeId);
        $soliditude->estado = 'ANALIZADO';
        $soliditude->fecha_finalizacion = now();
//        $soliditude->user_analitica_id = $request->user()->id;

//        error_log('Muestra rechazada: ' . $request->muestra_rechazada);
        if ($request->muestra_rechazada === 'Si') {
            $soliditude->muestra_rechazada = 'Si';
            $soliditude->estado = 'MUESTRA RECHAZADA';
            $soliditude->muestra_observacion = $request->muestra_observacion;

            $solicitudRechazada = new \App\Models\SolicitudRechazada();
            $solicitudRechazada->solicitude_id = $solicitudeId;
            $solicitudRechazada->motivo = $request->muestra_observacion;
            $solicitudRechazada->fecha_hora = now();
            $solicitudRechazada->area_id = $request->user()->area_id;
            $solicitudRechazada->user_id = $request->user()->id;
            $solicitudRechazada->save();
        }
        $soliditude->save();

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
    public function pdfBySolicitude($code)
    {
        // 1) buscar solicitud por code en hematología
        $solicitudeId = Hematologia::where('code', $code)->value('solicitude_id');
        if (!$solicitudeId) {
            abort(404, 'No se encontró la solicitud para ese código.');
        }

        // 2) solicitud con relaciones
        $solicitud = Solicitude::with([
            'paciente',
            'doctor',
            'servicios.area',
        ])->findOrFail($solicitudeId);

        // 3) hematología
        $hematologia = Hematologia::where('solicitude_id', $solicitudeId)->first();

        // Si quieres que nunca sea null:
        // $hematologia = Hematologia::firstOrNew(['solicitude_id' => $solicitudeId]);

        // 4) rangos del área Hematología
        $areaHemato = Area::where('title', 'HEMATOLOGÍA')
            ->orWhere('title', 'Hematología')
            ->first();

        $rangos = [];
        if ($areaHemato) {
            $rangos = $areaHemato->rangos()->orderBy('id')->get();
        }

        // 5) QR apuntando al mismo PDF
        $url = url("/api/hematologia/solicitud/{$code}/pdf");
        $qrSvgBase64 = base64_encode(
            QrCode::format('svg')->size(110)->margin(1)->generate($url)
        );
//        return $solicitud->preAnaliticaMuestras;

        // 6) generar PDF
        $pdf = Pdf::loadView('pdf.hematologia', [
            'solicitud'   => $solicitud,
            'hematologia' => $hematologia,
            'rangos'      => $rangos,
            'qrSvgBase64' => $qrSvgBase64,
            'qrUrl'       => $url,
        ])->setPaper('legal');

        $nro = $solicitud->nro_registro ?? $solicitud->id;
        return $pdf->stream('HEMATOLOGIA_'.$nro.'.pdf');
    }
}
