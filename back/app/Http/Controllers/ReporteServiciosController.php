<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;

class ReporteServiciosController extends Controller
{
    public function index(Request $request)
    {
        $rows = $this->baseQuery($request)->get();

        $totalMonto  = $rows->sum(fn ($r) => (float) $r->total_monto);
        $embarazadas = $rows->where('paciente_embarazo', 1)->count();

        return response()->json([
            'rows' => $rows,
            'summary' => [
                'total_solicitudes' => $rows->count(),
                'total_monto'       => round($totalMonto, 2),
                'embarazadas'       => $embarazadas,
            ],
        ]);
    }

    public function exportExcel(Request $request)
    {
        $rows     = $this->baseQuery($request)->get();
        $dateFrom = $request->get('date_from', 'inicio');
        $dateTo   = $request->get('date_to', 'fin');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Solicitudes');

        $lastCol = 'O';

        // ── Fila 1: Título ────────────────────────────────────────────────
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', 'REPORTE DE SOLICITUDES POR SERVICIOS / PRESTACIONES');
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A237E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // ── Fila 2: Rango y totales ───────────────────────────────────────
        $sheet->mergeCells("A2:{$lastCol}2");
        $totalMonto = $rows->sum(fn ($r) => (float) $r->total_monto);
        $sheet->setCellValue('A2', "Rango: {$dateFrom} — {$dateTo}    |    Total solicitudes: {$rows->count()}    |    Total Bs: " . number_format($totalMonto, 2));
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF333333']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8EAF6']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(16);

        // ── Fila 3: Encabezados ───────────────────────────────────────────
        $headers = [
            'A' => 'ID',
            'B' => 'Código',
            'C' => 'Fecha',
            'D' => 'Paciente',
            'E' => 'CI',
            'F' => 'Edad',
            'G' => 'Género',
            'H' => 'Embarazada',
            'I' => 'Cama',
            'J' => 'Sala',
            'K' => 'Servicios',
            'L' => 'Prestaciones (Áreas)',
            'M' => 'Total Bs',
            'N' => 'Estado',
            'O' => 'Doctor',
        ];

        foreach ($headers as $col => $label) {
            $sheet->setCellValue("{$col}3", $label);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $sheet->setAutoFilter("A3:{$lastCol}3");

        $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF283593']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(16);

        // ── Filas de datos ────────────────────────────────────────────────
        $rowNum = 4;
        foreach ($rows as $i => $item) {
            $isEven  = $i % 2 === 0;
            $bgColor = $isEven ? 'FFFFFFFF' : 'FFF5F5F5';

            $sheet->setCellValue("A{$rowNum}", $item->id);
            $sheet->setCellValue("B{$rowNum}", $item->codigo_solicitud ?? '');
            $sheet->setCellValue("C{$rowNum}", $item->fecha_solicitud);
            $sheet->setCellValue("D{$rowNum}", $item->paciente_nombre);
            $sheet->setCellValue("E{$rowNum}", $item->paciente_ci);
            $sheet->setCellValue("F{$rowNum}", (int) $item->paciente_edad);
            $sheet->setCellValue("G{$rowNum}", $item->paciente_genero);
            $sheet->setCellValue("H{$rowNum}", $item->paciente_embarazo ? 'Sí' : 'No');
            $sheet->setCellValue("I{$rowNum}", $item->cama);
            $sheet->setCellValue("J{$rowNum}", $item->sala);
            $sheet->setCellValue("K{$rowNum}", $item->servicios_nombres);
            $sheet->setCellValue("L{$rowNum}", $item->areas_nombres);
            $sheet->setCellValue("M{$rowNum}", (float) $item->total_monto);
            $sheet->setCellValue("N{$rowNum}", $item->estado);
            $sheet->setCellValue("O{$rowNum}", $item->doctor_nombre);

            $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bgColor]],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
            ]);

            // Color de género
            $genero = $item->paciente_genero;
            if ($genero === 'M') {
                $sheet->getStyle("G{$rowNum}")->getFont()->getColor()->setARGB('FF1565C0');
            } elseif ($genero === 'F') {
                $sheet->getStyle("G{$rowNum}")->getFont()->getColor()->setARGB('FFAD1457');
            }

            // Embarazada en morado
            if ($item->paciente_embarazo) {
                $sheet->getStyle("H{$rowNum}")->getFont()->getColor()->setARGB('FF6A1B9A');
                $sheet->getStyle("H{$rowNum}")->getFont()->setBold(true);
            }

            // Alineación centrada para columnas cortas
            $sheet->getStyle("A{$rowNum}:C{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$rowNum}:J{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("M{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

            $rowNum++;
        }

        // ── Fila de totales ───────────────────────────────────────────────
        $sheet->mergeCells("A{$rowNum}:L{$rowNum}");
        $sheet->setCellValue("A{$rowNum}", 'TOTAL');
        $sheet->setCellValue("M{$rowNum}", $totalMonto);
        $sheet->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1A237E']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
        ]);
        $sheet->getStyle("M{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        // ── Anchos fijos para columnas de texto largo ─────────────────────
        $sheet->getColumnDimension('K')->setWidth(40);
        $sheet->getColumnDimension('L')->setWidth(30);
        $sheet->getColumnDimension('D')->setWidth(28);
        $sheet->getColumnDimension('O')->setWidth(22);

        $filename = "solicitudes_{$dateFrom}_{$dateTo}.xlsx";
        $path     = storage_path("app/{$filename}");

        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    public function exportPdf(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(0);

        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');

        $rows        = $this->baseQuery($request)->get();
        $totalMonto  = $rows->sum(fn ($r) => (float) $r->total_monto);
        $embarazadas = $rows->where('paciente_embarazo', 1)->count();

        $pdf = Pdf::loadView('reportes.servicios_resumen', [
            'rows'        => $rows,
            'dateFrom'    => $dateFrom,
            'dateTo'      => $dateTo,
            'totalMonto'  => $totalMonto,
            'embarazadas' => $embarazadas,
            'totalCount'  => $rows->count(),
        ])->setPaper('letter', 'landscape');

        return $pdf->stream("solicitudes_{$dateFrom}_{$dateTo}.pdf");
    }

    private function baseQuery(Request $request)
    {
        $dateFrom   = $request->get('date_from');
        $dateTo     = $request->get('date_to');
        $servicioId = $request->get('servicio_id');
        $areaId     = $request->get('area_id');
        $genero     = $request->get('genero');
        $embarazada = $request->get('embarazada'); // '1' | '0' | null
        $cama       = $request->get('cama');
        $paciente   = $request->get('paciente');

        return DB::table('solicitudes as s')
            ->leftJoin('users as u', 'u.id', '=', 's.user_id')
            ->whereNull('s.deleted_at')
            ->when($dateFrom, fn ($q) => $q->whereDate('s.fecha_solicitud', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('s.fecha_solicitud', '<=', $dateTo))
            ->when($genero,   fn ($q) => $q->where('s.paciente_genero', $genero))
            ->when(
                $cama !== null && $cama !== '',
                fn ($q) => $q->where('s.cama', 'like', "%{$cama}%")
            )
            ->when(
                $paciente !== null && $paciente !== '',
                fn ($q) => $q->where(function ($qq) use ($paciente) {
                    $qq->where('s.paciente_nombre', 'like', "%{$paciente}%")
                       ->orWhere('s.paciente_ci', 'like', "%{$paciente}%");
                })
            )
            ->when(
                $embarazada !== null && $embarazada !== '',
                fn ($q) => $q->where('s.paciente_embarazo', (bool) (int) $embarazada)
            )
            ->when($servicioId, fn ($q) => $q->whereExists(function ($sub) use ($servicioId) {
                $sub->from('servicio_solicitudes as ss')
                    ->whereColumn('ss.solicitude_id', 's.id')
                    ->whereNull('ss.deleted_at')
                    ->where('ss.servicio_id', $servicioId);
            }))
            ->when($areaId, fn ($q) => $q->whereExists(function ($sub) use ($areaId) {
                $sub->from('servicio_solicitudes as ss')
                    ->whereColumn('ss.solicitude_id', 's.id')
                    ->whereNull('ss.deleted_at')
                    ->where('ss.area_id', $areaId);
            }))
            ->select(
                's.id',
                's.codigo_solicitud',
                's.fecha_solicitud',
                's.hora_solicitud',
                's.paciente_nombre',
                's.paciente_ci',
                's.paciente_edad',
                's.paciente_genero',
                's.paciente_embarazo',
                's.cama',
                's.sala',
                's.estado',
                's.doctor_nombre',
                DB::raw('u.name as usuario_nombre')
            )
            ->selectRaw("(
                SELECT GROUP_CONCAT(
                    DISTINCT COALESCE(NULLIF(se2.nombre,''), NULLIF(ss2.nombre,''), 'SIN NOMBRE')
                    ORDER BY se2.nombre SEPARATOR ' | '
                )
                FROM servicio_solicitudes ss2
                LEFT JOIN servicios se2 ON se2.id = ss2.servicio_id
                WHERE ss2.solicitude_id = s.id AND ss2.deleted_at IS NULL
            ) as servicios_nombres")
            ->selectRaw("(
                SELECT GROUP_CONCAT(
                    DISTINCT COALESCE(a2.name, '')
                    ORDER BY a2.name SEPARATOR ' | '
                )
                FROM servicio_solicitudes ss2
                LEFT JOIN areas a2 ON a2.id = ss2.area_id
                WHERE ss2.solicitude_id = s.id AND ss2.deleted_at IS NULL
            ) as areas_nombres")
            ->selectRaw("(
                SELECT ROUND(SUM(COALESCE(ss2.precio, 0)), 2)
                FROM servicio_solicitudes ss2
                WHERE ss2.solicitude_id = s.id AND ss2.deleted_at IS NULL
            ) as total_monto")
            ->selectRaw("(
                SELECT COUNT(ss2.id)
                FROM servicio_solicitudes ss2
                WHERE ss2.solicitude_id = s.id AND ss2.deleted_at IS NULL
            ) as total_items")
            ->orderByDesc('s.fecha_solicitud')
            ->orderByDesc('s.id');
    }
}
