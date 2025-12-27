<?php

namespace App\Http\Controllers;

use App\Models\PanelSexual;
use App\Models\Solicitude;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class PanelSexualController extends Controller
{
    public function showBySolicitude($id)
    {
        $solicitud = Solicitude::with(['paciente', 'doctor','servicios'])->findOrFail($id);

        $panel = PanelSexual::firstOrNew([
            'solicitude_id' => $id
        ]);

        $defaults = [
            'chlamydia_trachomatis'   => 'NO DETECTADO',
            'mycoplasma_genitalium'   => 'NO DETECTADO',
            'neisseria_gonorrhoeae'   => 'NO DETECTADO',
            'trichomonas_vaginalis'   => 'NO DETECTADO',
            'ureaplasma_urealyticum'  => 'NO DETECTADO',
            'ureaplasma_parvum'       => 'NO DETECTADO',
            'mycoplasma_hominis'      => 'NO DETECTADO',
            'hsv_1'                   => 'NO DETECTADO',
            'hsv_2'                   => 'NO DETECTADO',
            'treponema_pallidum'      => 'NO DETECTADO',
            'candida_albicans'        => 'NO DETECTADO',
            'gardnerella_vaginalis'   => 'NO DETECTADO',
        ];

        foreach ($defaults as $k => $v) {
            if (empty($panel->{$k})) $panel->{$k} = $v;
        }
        $soliditude = Solicitude::find($id);
        $soliditude->estado = 'ANALIZADO';
        $soliditude->fecha_finalizacion = now();
        $soliditude->save();

        return response()->json([
            'solicitud' => $solicitud,
            'panel'     => $panel,
        ]);
    }

    public function upsert(Request $request, $id)
    {
        $data = $request->all();
        $data['solicitude_id'] = $id;

        $registro = PanelSexual::updateOrCreate(
            ['solicitude_id' => $id],
            $data
        );

        return response()->json($registro);
    }

    public function destroyBySolicitude($id)
    {
        $registro = PanelSexual::where('solicitude_id', $id)->firstOrFail();
        $registro->delete();

        return response()->json(['message' => 'Registro eliminado']);
    }

    public function pdfBySolicitude($code)
    {
        $id = PanelSexual::where('code', $code)
            ->value('solicitude_id');
        $solicitud = Solicitude::with(['paciente', 'doctor'])->findOrFail($id);
        $panel = PanelSexual::where('solicitude_id', $id)->first();

        $url = url("/api/panel-sexual/solicitud/{$code}/pdf");
        $qrSvgBase64 = base64_encode(
            QrCode::format('svg')->size(110)->margin(1)->generate($url)
        );


        $pdf = Pdf::loadView('pdf.panel_sexual', [
            'solicitud' => $solicitud,
            'panel' => $panel,
            'qrSvgBase64' => $qrSvgBase64,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream('PANEL_ITS_'.$solicitud->nro_registro.'.pdf');
    }
}
