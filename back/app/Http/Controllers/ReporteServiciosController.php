<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;

// Excel
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ReporteServiciosController extends Controller
{
    public function index(Request $request)
    {
        $dateFrom = $request->get('date_from'); // YYYY-MM-DD
        $dateTo   = $request->get('date_to');   // YYYY-MM-DD
        $userId   = $request->get('user_id');   // opcional

        $rows = $this->baseQuery($dateFrom, $dateTo, $userId)->get();

        // Para el gráfico (top 20)
        $chart = $rows->take(20)->values()->map(function ($r) {
            return [
                'servicio' => $r->servicio_nombre,
                'total'    => (float) $r->total_monto,
                'cantidad' => (int) $r->cantidad,
            ];
        });

        return response()->json([
            'filters' => [
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'user_id' => $userId,
            ],
            'rows' => $rows,
            'chart' => $chart,
        ]);
    }

    public function exportExcel(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');
        $userId   = $request->get('user_id');

        $rows = $this->baseQuery($dateFrom, $dateTo, $userId)->get();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Servicios');

        $headers = [
            'Servicio', 'Área', 'Cantidad (veces)',
            'Solicitudes', 'Precio promedio', 'Total Bs'
        ];

        // Header
        $col = 1;
        foreach ($headers as $h) {
            $sheet->setCellValueByColumnAndRow($col, 1, $h);
            $col++;
        }

        // Rows
        $r = 2;
        foreach ($rows as $item) {
            $sheet->setCellValue("A{$r}", $item->servicio_nombre);
            $sheet->setCellValue("B{$r}", $item->area_nombre);
            $sheet->setCellValue("C{$r}", (int)$item->cantidad);
            $sheet->setCellValue("D{$r}", (int)$item->solicitudes);
            $sheet->setCellValue("E{$r}", (float)$item->precio_promedio);
            $sheet->setCellValue("F{$r}", (float)$item->total_monto);
            $r++;
        }

        // Autosize simple
        foreach (range('A', 'F') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $filename = "reporte_servicios_{$dateFrom}_{$dateTo}.xlsx";
        $path = storage_path("app/{$filename}");

        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');
        $userId   = $request->get('user_id');

        $rows = $this->baseQuery($dateFrom, $dateTo, $userId)->get();

        $pdf = Pdf::loadView('reportes.servicios_resumen', [
            'rows' => $rows,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'userId' => $userId,
        ])->setPaper('letter', 'portrait');

        return $pdf->stream("reporte_servicios_{$dateFrom}_{$dateTo}.pdf");
    }

    private function baseQuery($dateFrom, $dateTo, $userId)
    {
        // Agrupa por servicio y área usando servicio_solicitudes
        // - cantidad: cuántas veces se pidió el servicio
        // - solicitudes: cuántas solicitudes distintas
        // - total_monto: suma de precios (pivot ss.precio)
        // - precio_promedio: promedio de precio pivot
        $q = DB::table('servicio_solicitudes as ss')
            ->join('solicitudes as s', 's.id', '=', 'ss.solicitude_id')
            ->leftJoin('servicios as se', 'se.id', '=', 'ss.servicio_id')
            ->leftJoin('areas as a', 'a.id', '=', 'ss.area_id')
            ->whereNull('s.deleted_at')
            ->when($dateFrom, function ($qq) use ($dateFrom) {
                $qq->whereDate('s.fecha_solicitud', '>=', $dateFrom);
            })
            ->when($dateTo, function ($qq) use ($dateTo) {
                $qq->whereDate('s.fecha_solicitud', '<=', $dateTo);
            })
            ->when($userId, function ($qq) use ($userId) {
                $qq->where('s.user_id', $userId);
            })
            ->groupBy('ss.servicio_id', 'ss.area_id', 'se.nombre', 'ss.nombre', 'a.name')
            ->select(
                'ss.servicio_id',
                'ss.area_id',
                DB::raw("COALESCE(NULLIF(se.nombre,''), NULLIF(ss.nombre,''), 'SIN NOMBRE') as servicio_nombre"),
                DB::raw("COALESCE(a.name, 'SIN ÁREA') as area_nombre"),
                DB::raw('COUNT(ss.id) as cantidad'),
                DB::raw('COUNT(DISTINCT s.id) as solicitudes'),
                DB::raw('ROUND(AVG(COALESCE(ss.precio, 0)), 2) as precio_promedio'),
                DB::raw('ROUND(SUM(COALESCE(ss.precio, 0)), 2) as total_monto')
            )
            ->orderByDesc('total_monto')
            ->orderByDesc('cantidad');

        return $q;
    }
}
