<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Worksheet\SheetView;

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
                'hospital_general'  => $rows->where('procedencia', 'HG')->count(),
                'externos'          => $rows->where('procedencia', 'EXTERNO')->count(),
            ],
        ]);
    }

    public function exportPdfExternos(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $data = $this->buildExternosData($request);
        $pdf  = Pdf::loadView('reportes.pacientes_externos', $data)->setPaper('letter', 'landscape');

        return $pdf->stream("pacientes_externos_{$data['dateFrom']}_{$data['dateTo']}.pdf");
    }

    public function exportExcelExternos(Request $request)
    {
        $data     = $this->buildExternosData($request);
        $rows     = $data['rows'];
        $grupos   = $data['grupos'];
        $dateFrom = $data['dateFrom'];
        $dateTo   = $data['dateTo'];

        $spreadsheet = new Spreadsheet();

        $estiloTitulo = [
            'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE65100']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $estiloSubtitulo = [
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['argb' => 'FF333333']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF3E0']],
        ];
        $estiloEncabezado = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFEF6C00']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]],
        ];
        $estiloTotal = [
            'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE65100']],
        ];
        $subtitulo = "Rango: {$dateFrom} — {$dateTo}    |    Pacientes externos: {$rows->count()}    |    Establecimientos: {$grupos->count()}    |    Total Bs: " . number_format($data['totalMonto'], 2);

        // ── Hoja 1: Resumen por establecimiento ───────────────────────────
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Resumen');

        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A1', 'PACIENTES EXTERNOS — RESUMEN POR ESTABLECIMIENTO');
        $sheet->getStyle('A1')->applyFromArray($estiloTitulo);
        $sheet->getRowDimension(1)->setRowHeight(24);

        $sheet->mergeCells('A2:E2');
        $sheet->setCellValue('A2', $subtitulo);
        $sheet->getStyle('A2')->applyFromArray($estiloSubtitulo);

        foreach (['A' => '#', 'B' => 'Establecimiento de salud', 'C' => 'Solicitudes', 'D' => '%', 'E' => 'Total Bs'] as $col => $label) {
            $sheet->setCellValue("{$col}3", $label);
        }
        $sheet->getStyle('A3:E3')->applyFromArray($estiloEncabezado);

        $rowNum = 4;
        foreach ($grupos->values() as $i => $g) {
            $sheet->setCellValue("A{$rowNum}", $i + 1);
            $sheet->setCellValue("B{$rowNum}", $g['establecimiento']);
            $sheet->setCellValue("C{$rowNum}", $g['cantidad']);
            $sheet->setCellValue("D{$rowNum}", $g['porcentaje']);
            $sheet->setCellValue("E{$rowNum}", $g['monto']);
            $sheet->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $i % 2 === 0 ? 'FFFFFFFF' : 'FFF5F5F5']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
            ]);
            $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$rowNum}:D{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowNum++;
        }

        $sheet->mergeCells("A{$rowNum}:B{$rowNum}");
        $sheet->setCellValue("A{$rowNum}", 'TOTAL');
        $sheet->setCellValue("C{$rowNum}", $rows->count());
        $sheet->setCellValue("D{$rowNum}", $rows->count() ? 100 : 0);
        $sheet->setCellValue("E{$rowNum}", $data['totalMonto']);
        $sheet->getStyle("A{$rowNum}:E{$rowNum}")->applyFromArray($estiloTotal);
        $sheet->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("C{$rowNum}:D{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle("E4:E{$rowNum}")->getNumberFormat()->setFormatCode('#,##0.00');

        $sheet->getColumnDimension('A')->setWidth(6);
        $sheet->getColumnDimension('B')->setWidth(55);
        $sheet->getColumnDimension('C')->setWidth(13);
        $sheet->getColumnDimension('D')->setWidth(9);
        $sheet->getColumnDimension('E')->setWidth(14);

        // ── Hoja 2: Detalle de solicitudes ────────────────────────────────
        $detalle = $spreadsheet->createSheet();
        $detalle->setTitle('Detalle');
        $lastCol = 'N';

        $detalle->mergeCells("A1:{$lastCol}1");
        $detalle->setCellValue('A1', 'PACIENTES EXTERNOS — DETALLE DE SOLICITUDES');
        $detalle->getStyle('A1')->applyFromArray($estiloTitulo);
        $detalle->getRowDimension(1)->setRowHeight(24);

        $detalle->mergeCells("A2:{$lastCol}2");
        $detalle->setCellValue('A2', $subtitulo);
        $detalle->getStyle('A2')->applyFromArray($estiloSubtitulo);

        $headers = [
            'A' => '#', 'B' => 'Código', 'C' => 'Fecha', 'D' => 'Establecimiento de salud',
            'E' => 'Tipo', 'F' => 'Programa / Tipo externo', 'G' => 'Paciente', 'H' => 'CI',
            'I' => 'Edad', 'J' => 'Género', 'K' => 'Servicios', 'L' => 'Prestaciones (Áreas)',
            'M' => 'Total Bs', 'N' => 'Estado',
        ];
        foreach ($headers as $col => $label) {
            $detalle->setCellValue("{$col}3", $label);
        }
        $detalle->getStyle("A3:{$lastCol}3")->applyFromArray($estiloEncabezado);
        $detalle->setAutoFilter("A3:{$lastCol}3");

        $rowNum = 4;
        foreach ($rows as $i => $item) {
            $detalle->setCellValue("A{$rowNum}", $i + 1);
            $detalle->setCellValue("B{$rowNum}", $item->codigo_solicitud ?? '');
            $detalle->setCellValue("C{$rowNum}", $item->fecha_solicitud);
            $detalle->setCellValue("D{$rowNum}", $item->establecimiento_nombre);
            $detalle->setCellValue("E{$rowNum}", $item->tipo_atencion === 'SI' ? 'SUS' : 'EXT');
            $detalle->setCellValue("F{$rowNum}", $item->tipo_paciente_externo ?? '');
            $detalle->setCellValue("G{$rowNum}", $item->paciente_nombre);
            $detalle->setCellValue("H{$rowNum}", $item->paciente_ci);
            $detalle->setCellValue("I{$rowNum}", (int) $item->paciente_edad);
            $detalle->setCellValue("J{$rowNum}", $item->paciente_genero);
            $detalle->setCellValue("K{$rowNum}", $item->servicios_nombres);
            $detalle->setCellValue("L{$rowNum}", $item->areas_nombres);
            $detalle->setCellValue("M{$rowNum}", (float) $item->total_monto);
            $detalle->setCellValue("N{$rowNum}", $item->estado);

            $detalle->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray([
                'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $i % 2 === 0 ? 'FFFFFFFF' : 'FFF5F5F5']],
                'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
            ]);
            $detalle->getStyle("A{$rowNum}:C{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $detalle->getStyle("E{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $detalle->getStyle("H{$rowNum}:J{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $rowNum++;
        }

        $detalle->mergeCells("A{$rowNum}:L{$rowNum}");
        $detalle->setCellValue("A{$rowNum}", 'TOTAL');
        $detalle->setCellValue("M{$rowNum}", $data['totalMonto']);
        $detalle->getStyle("A{$rowNum}:{$lastCol}{$rowNum}")->applyFromArray($estiloTotal);
        $detalle->getStyle("A{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $detalle->getStyle("M4:M{$rowNum}")->getNumberFormat()->setFormatCode('#,##0.00');

        foreach (['A' => 6, 'B' => 14, 'C' => 12, 'D' => 40, 'E' => 7, 'F' => 26, 'G' => 30, 'H' => 12,
                  'I' => 7, 'J' => 8, 'K' => 40, 'L' => 28, 'M' => 12, 'N' => 13] as $col => $width) {
            $detalle->getColumnDimension($col)->setWidth($width);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = "pacientes_externos_{$dateFrom}_{$dateTo}.xlsx";
        $path     = storage_path("app/{$filename}");

        (new Xlsx($spreadsheet))->save($path);

        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    /**
     * Solicitudes de pacientes externos (establecimiento distinto al Hospital General)
     * con los filtros actuales, agrupadas por establecimiento de salud.
     */
    private function buildExternosData(Request $request): array
    {
        $request->merge(['procedencia' => 'EXTERNO']);

        $rows = $this->baseQuery($request)
            ->reorder()
            ->orderBy('s.fecha_solicitud')
            ->orderBy('s.id')
            ->get()
            ->each(function ($r) {
                $nombre = trim(preg_replace('/\s+/', ' ', (string) $r->establecimiento_salud));
                $r->establecimiento_nombre = $nombre !== '' ? mb_strtoupper($nombre) : 'SIN ESTABLECIMIENTO';
            })
            ->sortBy('establecimiento_nombre')
            ->values();

        $totalMonto = $rows->sum(fn ($r) => (float) $r->total_monto);
        $total      = $rows->count();

        $grupos = $rows->groupBy('establecimiento_nombre')
            ->map(fn ($items, $nombre) => [
                'establecimiento' => $nombre,
                'cantidad'        => $items->count(),
                'porcentaje'      => $total ? round($items->count() * 100 / $total, 1) : 0,
                'monto'           => round($items->sum(fn ($r) => (float) $r->total_monto), 2),
            ])
            ->sortByDesc('cantidad');

        return [
            'rows'       => $rows,
            'grupos'     => $grupos,
            'totalMonto' => $totalMonto,
            'dateFrom'   => $request->get('date_from'),
            'dateTo'     => $request->get('date_to'),
        ];
    }

    /**
     * El Hospital General quedó registrado con varios nombres (HGSJDDO, "Hospital General",
     * "HGSJDD HOSPITAL GENERAL SAN JUAN DE DIOS BLOQUE CENTRAL", ...). Todo lo demás es externo.
     */
    private function sqlEsHospitalGeneral(): string
    {
        return "(UPPER(TRIM(COALESCE(s.establecimiento_salud, ''))) = 'HGSJDDO'
                 OR UPPER(COALESCE(s.establecimiento_salud, '')) LIKE '%HOSPITAL GENERAL%')";
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

    public function exportExcelMensual(Request $request)
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo   = $request->get('date_to',   date('Y-m-t'));

        // Solicitudes del período con datos de quimica_sanguinea (serología VIH/RPR/HVB/HVC)
        $rows = $this->baseQuery($request)->get();

        // Obtener resultados serológicos indexados por solicitude_id
        $solicitudeIds = $rows->pluck('id')->toArray();
        $serologias = \Illuminate\Support\Facades\DB::table('quimica_sanguineas')
            ->whereIn('solicitude_id', $solicitudeIds)
            ->whereNull('deleted_at')
            ->select([
                'solicitude_id',
                'prueba_rapida_vih',
                'rpr',
                'prueba_rapida_hepatitis_b',
                'prueba_rapida_hepatitis_c',
                'prueba_rapida_sifilis',
            ])
            ->get()
            ->keyBy('solicitude_id');

        $templatePath = storage_path('app/templates/registro_vih_mensual.xlsx');
        $spreadsheet  = IOFactory::load($templatePath);

        // Nombre del mes en español
        $meses = [
            1=>'ENERO',2=>'FEBRERO',3=>'MARZO',4=>'ABRIL',5=>'MAYO',6=>'JUNIO',
            7=>'JULIO',8=>'AGOSTO',9=>'SEPTIEMBRE',10=>'OCTUBRE',11=>'NOVIEMBRE',12=>'DICIEMBRE',
        ];
        $mes = $dateFrom ? $meses[(int) date('n', strtotime($dateFrom))] : '';

        // Filas de datos: primera fila = 14, fila TOTAL del template = 34 (20 filas)
        $firstDataRow  = 14;
        $templateTotal = 34;
        $templateRows  = $templateTotal - $firstDataRow;

        // Todo va en una sola hoja: se descartan las hojas extra del template
        // y se insertan tantas filas de datos como registros haya.
        while ($spreadsheet->getSheetCount() > 1) {
            $spreadsheet->removeSheetByIndex(1);
        }

        $sheet = $spreadsheet->getSheet(0);
        $sheet->setCellValue('V9', $mes);

        // El template viene en "Vista previa de salto de página": abrirlo en vista normal
        $sheet->getSheetView()->setView(SheetView::SHEETVIEW_NORMAL);

        // Columna B (correlativo): el ancho del template corta los números de 3 dígitos
        $sheet->getColumnDimension('B')->setWidth(5.5);

        $extra = max(0, $rows->count() - $templateRows);

        if ($extra > 0) {
            // Combinaciones de la fila modelo (D:M, N:Q, R:U, X:Y) para replicarlas
            $mergesModelo = [];
            foreach ($sheet->getMergeCells() as $rango) {
                [$ini, $fin] = explode(':', $rango);
                [$colIni, $filaIni] = Coordinate::coordinateFromString($ini);
                [$colFin, $filaFin] = Coordinate::coordinateFromString($fin);
                if ((int) $filaIni === $firstDataRow && (int) $filaFin === $firstDataRow) {
                    $mergesModelo[] = [$colIni, $colFin];
                }
            }

            $altura    = $sheet->getRowDimension($firstDataRow)->getRowHeight();
            $ultimaCol = Coordinate::columnIndexFromString($sheet->getHighestDataColumn());

            $sheet->insertNewRowBefore($templateTotal, $extra);

            $desde = $templateTotal;
            $hasta = $templateTotal + $extra - 1;

            // Copiar el formato de la fila modelo columna por columna
            for ($i = 1; $i <= $ultimaCol; $i++) {
                $col = Coordinate::stringFromColumnIndex($i);
                $sheet->duplicateStyle($sheet->getStyle("{$col}{$firstDataRow}"), "{$col}{$desde}:{$col}{$hasta}");
            }

            for ($r = $desde; $r <= $hasta; $r++) {
                $sheet->getRowDimension($r)->setRowHeight($altura);
                foreach ($mergesModelo as [$colIni, $colFin]) {
                    $sheet->mergeCells("{$colIni}{$r}:{$colFin}{$r}");
                }
            }
        }

        $lastDataRow = $firstDataRow + max($rows->count(), $templateRows) - 1;
        $totalRow    = $lastDataRow + 1;

        // Reajustar los SUM() de la fila TOTAL al nuevo rango de datos
        foreach ($sheet->getRowIterator($totalRow, $totalRow) as $filaTotal) {
            $celdas = $filaTotal->getCellIterator();
            $celdas->setIterateOnlyExistingCells(true);
            foreach ($celdas as $celda) {
                $formula = $celda->getValue();
                if (is_string($formula) && str_starts_with($formula, '=')) {
                    $celda->setValue(preg_replace(
                        '/([A-Z]{1,3})'.($templateTotal - 1).'(?![0-9])/',
                        '${1}'.$lastDataRow,
                        $formula
                    ));
                }
            }
        }

        // Área de impresión y repetición del encabezado en cada página impresa
        $sheet->getPageSetup()->setPrintArea('A1:AU'.($totalRow + 8));
        $sheet->getPageSetup()->setRowsToRepeatAtTopByStartAndEnd(1, $firstDataRow - 1);

        $rowNum = $firstDataRow;
        $nro    = 1;
        foreach ($rows as $item) {
            $sero = $serologias->get($item->id);

            // ── Datos del paciente ─────────────────────────────────────────
            $sheet->setCellValue("B{$rowNum}", $nro);
            $sheet->setCellValue("C{$rowNum}", $item->fecha_solicitud);
            $sheet->setCellValue("D{$rowNum}", $item->paciente_nombre);
            $sheet->setCellValue("N{$rowNum}", $item->codigo_solicitud ?? '');
            $sheet->setCellValue("R{$rowNum}", $item->paciente_ci);

            // Edad en columna V (masculino) o W (femenino)
            $edad = (int) ($item->paciente_edad ?? 0);
            if ($item->paciente_genero === 'M') {
                $sheet->setCellValue("V{$rowNum}", $edad);
            } elseif ($item->paciente_genero === 'F') {
                $sheet->setCellValue("W{$rowNum}", $edad);
            }

            // Embarazada → marca X en columna X
            if ($item->paciente_embarazo) {
                $sheet->setCellValue("X{$rowNum}", 'X');
            }

            // ── Resultados serológicos ─────────────────────────────────────
            if ($sero) {
                $reactivo = fn($val) => strtolower(trim((string)($val ?? ''))) === 'reactivo';

                // VIH 1ª prueba rápida: AE=prueba hecha, AF=reactivo
                if ($sero->prueba_rapida_vih !== null) {
                    $sheet->setCellValue("AE{$rowNum}", 'X');
                    if ($reactivo($sero->prueba_rapida_vih)) {
                        $sheet->setCellValue("AF{$rowNum}", 'X');
                    }
                }

                // Sifilis (RPR o prueba rápida): AM=prueba hecha, AN=reactivo
                $sifilisVal = $sero->prueba_rapida_sifilis ?? $sero->rpr;
                if ($sifilisVal !== null) {
                    $sheet->setCellValue("AM{$rowNum}", 'X');
                    if ($reactivo($sifilisVal)) {
                        $sheet->setCellValue("AN{$rowNum}", 'X');
                    }
                }

                // HVB: AQ=prueba hecha, AR=reactivo
                if ($sero->prueba_rapida_hepatitis_b !== null) {
                    $sheet->setCellValue("AQ{$rowNum}", 'X');
                    if ($reactivo($sero->prueba_rapida_hepatitis_b)) {
                        $sheet->setCellValue("AR{$rowNum}", 'X');
                    }
                }

                // HVC: AS=prueba hecha, AT=reactivo
                if ($sero->prueba_rapida_hepatitis_c !== null) {
                    $sheet->setCellValue("AS{$rowNum}", 'X');
                    if ($reactivo($sero->prueba_rapida_hepatitis_c)) {
                        $sheet->setCellValue("AT{$rowNum}", 'X');
                    }
                }
            }

            $rowNum++;
            $nro++;
        }

        $filename = "registro_vih_{$dateFrom}_{$dateTo}.xlsx";
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

    // ── Informes SNIS de ENTs ──────────────────────────────────────────
    // Formato "SNIS.ENT26 ACTUALIZADO": por cada prueba se informa el Nº de
    // pacientes, las pruebas procesadas y cuántas quedaron por debajo / por
    // encima de los valores de referencia. Las filas de Ferritina, B12,
    // Troponina, perfil tiroideo y marcadores tumorales se informan desde
    // Inmunología; el resto desde Química Sanguínea.

    private const AREA_INMUNOLOGIA = 6;

    public function exportPdfEnts(Request $request)
    {
        return $this->entsPdf($this->buildEntsData($request));
    }

    public function exportExcelEnts(Request $request)
    {
        return $this->entsExcel($this->buildEntsData($request));
    }

    public function exportPdfEntsInmunologia(Request $request)
    {
        return $this->entsPdf($this->buildEntsInmunologiaData($request));
    }

    public function exportExcelEntsInmunologia(Request $request)
    {
        return $this->entsExcel($this->buildEntsInmunologiaData($request));
    }

    private function entsPdf(array $data)
    {
        $pdf = Pdf::loadView('reportes.ents', $data)->setPaper('letter', 'portrait');
        return $pdf->stream("{$data['archivo']}_{$data['dateFrom']}_{$data['dateTo']}.pdf");
    }

    private function entsExcel(array $data)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($data['hoja']);

        $gris  = 'FF4A4A4A';
        $blanc = 'FFFFFFFF';
        $azul  = 'FF1A237E';

        // ── Cabecera institucional ─────────────────────────────────────
        foreach (['A' => 18, 'B' => 40, 'C' => 14, 'D' => 14, 'E' => 16, 'F' => 16] as $col => $ancho) {
            $sheet->getColumnDimension($col)->setWidth($ancho);
        }

        $cabecera = [
            1 => ['HOSPITAL GENERAL SAN JUAN DE DIOS', ['bold' => true, 'size' => 11]],
            2 => ['SERVICIO DE LABORATORIO DE ANALISIS CLINICO MICROBIOLÓGICO', ['size' => 9]],
            3 => ['INFORME DE PRODUCCIÓN Y VIGILANCIA DE ENTs', ['bold' => true, 'size' => 12, 'color' => ['argb' => $azul]]],
            4 => [$data['area'], ['bold' => true, 'size' => 11, 'color' => ['argb' => $azul]]],
        ];
        foreach ($cabecera as $fila => [$texto, $font]) {
            $sheet->mergeCells("A{$fila}:F{$fila}");
            $sheet->setCellValue("A{$fila}", $texto);
            $sheet->getStyle("A{$fila}")->applyFromArray(['font' => $font, 'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]]);
        }

        // ── Fila mes / gestión y totales ────────────────────────────────
        $sheet->setCellValue('A6', 'MES:');
        $sheet->setCellValue('B6', $data['mes']);
        $sheet->setCellValue('C6', 'GESTIÓN:');
        $sheet->setCellValue('D6', $data['gestion']);

        $sheet->setCellValue('A7', 'TOTAL PACIENTES ATENDIDOS SUS:');
        $sheet->setCellValue('C7', $data['totalSus']);
        $sheet->setCellValue('D7', 'TOTAL');
        $sheet->setCellValue('E7', $data['totalGeneral']);

        $sheet->setCellValue('A8', 'TOTAL PACIENTES ATENDIDOS EXTERNOS:');
        $sheet->setCellValue('C8', $data['totalExternos']);
        $sheet->getStyle('A6:E8')->applyFromArray(['font' => ['bold' => true]]);

        // ── Título tabla ───────────────────────────────────────────────
        $sheet->mergeCells('A10:F10');
        $sheet->setCellValue('A10', 'PRUEBAS PARA ENFERMEDADES NO TRANSMISIBLES');
        $sheet->getStyle('A10')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => $blanc]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $gris]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(10)->setRowHeight(16);

        // ── Cabeceras columnas ─────────────────────────────────────────
        $sheet->mergeCells('A11:B11');
        $sheet->setCellValue('A11', 'PRUEBAS DE DIAGNÓSTICO');
        $sheet->setCellValue('C11', 'Nº DE PACIENTES');
        $sheet->setCellValue('D11', 'PRUEBAS PROCESADAS');
        $sheet->setCellValue('E11', 'POR DEBAJO DE LOS VALORES DE REFERENCIA');
        $sheet->setCellValue('F11', 'POR ENCIMA DE LOS VALORES DE REFERENCIA');
        $sheet->getStyle('A11:F11')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 8, 'color' => ['argb' => $blanc]],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $gris]],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFAAAAAA']]],
        ]);
        $sheet->getRowDimension(11)->setRowHeight(28);

        // ── Filas de datos ─────────────────────────────────────────────
        $rowNum = 12;
        foreach ($data['grupos'] as $grupoLabel => $items) {
            $startRow = $rowNum;
            foreach ($items as $item) {
                $sheet->setCellValue("B{$rowNum}", $item['label']);
                foreach (['C' => 'pacientes', 'D' => 'procesadas', 'E' => 'debajo', 'F' => 'encima'] as $col => $campo) {
                    if ($item[$campo] !== '') {
                        $sheet->setCellValue("{$col}{$rowNum}", $item[$campo]);
                    }
                }

                $bg = ($rowNum % 2 === 0) ? 'FFFAFAFA' : 'FFFFFFFF';
                $sheet->getStyle("A{$rowNum}:F{$rowNum}")->applyFromArray([
                    'fill'    => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $bg]],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFDDDDDD']]],
                    'font'    => ['size' => 8],
                ]);
                $sheet->getStyle("C{$rowNum}:F{$rowNum}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $rowNum++;
            }
            $sheet->mergeCells("A{$startRow}:A" . ($rowNum - 1));
            $sheet->setCellValue("A{$startRow}", $grupoLabel);
            $sheet->getStyle("A{$startRow}")->applyFromArray([
                'font'      => ['bold' => true, 'size' => 8, 'color' => ['argb' => $blanc]],
                'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $gris]],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            ]);
        }

        $filename = "{$data['archivo']}_{$data['dateFrom']}_{$data['dateTo']}.xlsx";
        $path     = storage_path("app/{$filename}");
        (new Xlsx($spreadsheet))->save($path);
        return response()->download($path, $filename)->deleteFileAfterSend(true);
    }

    private function buildEntsData(Request $request): array
    {
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo   = $request->get('date_to',   date('Y-m-t'));

        $rows = DB::table('quimica_sanguineas as q')
            ->join('solicitudes as s', 's.id', '=', 'q.solicitude_id')
            ->whereNull('q.deleted_at')
            ->whereNull('s.deleted_at')
            ->when($dateFrom, fn ($q) => $q->whereDate('s.fecha_solicitud', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('s.fecha_solicitud', '<=', $dateTo))
            ->select(
                'q.solicitude_id',
                'q.glucosa','q.hb_glicosilada','q.hb_a1c',
                'q.tolerancia_glucosa_1h',
                'q.colesterol_total','q.trigliceridos','q.hdl_colesterol','q.ldl_colesterol',
                'q.creatinina','q.urea','q.sodio','q.potasio','q.cloro',
                'q.proteinas_totales','q.albumina','q.calcio',
                'q.microalbuminuria','q.creatinuria_24h','q.dce','q.proteinuria_24h',
                'q.got','q.gpt','q.fosfatasa_alcalina','q.ggt','q.bilirrubina_total',
                'q.hierro_serico',
                'q.ck_mb','q.ck_total',
                'q.amilasa','q.lipasa','q.ldh','q.acido_urico',
                's.tipo_atencion'
            )
            ->get()
            // la hemoglobina glicosilada se registra en cualquiera de las dos columnas
            ->each(fn ($r) => $r->hba1c = trim((string) $r->hb_glicosilada) !== '' ? $r->hb_glicosilada : $r->hb_a1c);

        $totalSus      = $rows->where('tipo_atencion', 'SI')->count();
        $totalExternos = $rows->where('tipo_atencion', '!=', 'SI')->count();
        $totalGeneral  = $rows->count();

        // Registros de una columna con valor; sin límites solo cuenta pacientes y pruebas
        $col = fn (string $columna, ?float $min = null, ?float $max = null) => $rows
            ->filter(fn ($r) => trim((string) $r->$columna) !== '')
            ->map(fn ($r) => [
                'sid'   => $r->solicitude_id,
                'valor' => $this->valorNumerico($r->$columna),
                'min'   => $min,
                'max'   => $max,
            ])
            ->values();

        // El examen general de orina se cuenta desde uroanálisis
        $ego = DB::table('uroanalisis as u')
            ->join('solicitudes as s', 's.id', '=', 'u.solicitude_id')
            ->whereNull('u.deleted_at')
            ->whereNull('s.deleted_at')
            ->when($dateFrom, fn ($q) => $q->whereDate('s.fecha_solicitud', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('s.fecha_solicitud', '<=', $dateTo))
            ->pluck('u.solicitude_id')
            ->map(fn ($sid) => ['sid' => $sid, 'valor' => null, 'min' => null, 'max' => null]);

        $f = fn (string $label, $registros = null) => $this->entsFila($label, $registros);

        $grupos = [
            'PERFIL DIABÉTICO' => [
                $f('1. Glucosa',                          $col('glucosa', 70, 105)),
                $f('2. Glucosa post prandial'),
                $f('3. Hemoglobina glicosilada (A1c)',    $col('hba1c', 3.8, 5.8)),
                $f('4. Curva de Tolerancia a la Glucosa', $col('tolerancia_glucosa_1h')),
            ],
            'PERFIL LIPÍDICO' => [
                $f('5. Colesterol',     $col('colesterol_total', null, 200)),
                $f('6. Triglicéridos',  $col('trigliceridos', null, 150)),
                $f('7. HDL-Colesterol', $col('hdl_colesterol', 35, null)),
                $f('8. LDL-Colesterol', $col('ldl_colesterol', null, 129)),
            ],
            'PERFIL RENAL' => [
                $f('9. Creatinina',                     $col('creatinina', 0.7, 1.2)),
                $f('10. UREA/NUS',                      $col('urea', 15, 39)),
                $f('11. Electrolitos (Sodio)',          $col('sodio', 135, 148)),
                $f('12. Electrolitos (Potasio)',        $col('potasio', 3.5, 5.3)),
                $f('13. Electrolitos (Cloro)',          $col('cloro', 98, 107)),
                $f('14. Electrolitos (Calcio iónico)'),
                $f('15. Proteínas totales',             $col('proteinas_totales', 6.0, 7.8)),
                $f('16. Albúmina',                      $col('albumina', 3.4, 4.8)),
                $f('17. Calcio sérico',                 $col('calcio', 8.6, 10.3)),
                $f('18. Examen general de orina',       $ego),
                $f('19. Microalbuminuria',              $col('microalbuminuria')),
                $f('20. Creatinuria de 24 horas',       $col('creatinuria_24h')),
                $f('21. Depuración de creatinina',      $col('dce')),
                $f('22. Proteinuria de 24 horas',       $col('proteinuria_24h')),
            ],
            'PERFIL HEPÁTICO' => [
                $f('23. Transaminasa GOT',                 $col('got', null, 40)),
                $f('24. Transaminasa GPT',                 $col('gpt', null, 41)),
                $f('25. Fosfatasa Alcalina',               $col('fosfatasa_alcalina', null, 105)),
                $f('26. Gamma Glutamil Transferasa (GGT)', $col('ggt', 5, 39)),
                $f('27. Bilirrubinas (T, D, I)',           $col('bilirrubina_total', null, 0.8)),
            ],
            'INDICADORES DE ANEMIAS' => [
                $f('28. Hierro',        $col('hierro_serico', 50, 170)),
                $f('29. TIBC'),
                $f('32. Ácido Fólico'),
                $f('33. Otros'),
            ],
            'PERFIL CARDIACO' => [
                $f('34. CK-MB',      $col('ck_mb', 0, 24)),
                $f('35. CK',         $col('ck_total', null, 145)),
                $f('37. NT-proBNP'),
                $f('38. Otros'),
            ],
            'VARIOS' => [
                $f('53. Amilasa',                $col('amilasa', 22, 80)),
                $f('54. Lipasa',                 $col('lipasa', null, 150)),
                $f('55. Lactato Deshidrogenasa', $col('ldh', 200, 730)),
                $f('56. Ácido Úrico',            $col('acido_urico', 2.6, 7.2)),
            ],
        ];

        return array_merge(
            compact('grupos', 'totalSus', 'totalExternos', 'totalGeneral', 'dateFrom', 'dateTo'),
            $this->entsPeriodo($dateFrom),
            ['area' => 'AREA QUÍMICA SANGUÍNEA', 'hoja' => 'ENTs Química', 'archivo' => 'ents_quimica']
        );
    }

    private function buildEntsInmunologiaData(Request $request): array
    {
        $dateFrom = $request->get('date_from', date('Y-m-01'));
        $dateTo   = $request->get('date_to',   date('Y-m-t'));

        // Fila del SNIS → analitos (area_rangos.rango_nombre) que la informan.
        // Cada analito existe en versión CLIA y ELISA, con su propio rango.
        $filas = [
            'INDICADORES DE ANEMIAS' => [
                '30. Ferritina'    => ['FERRITINA (FTN)'],
                '31. Vitamina B12' => null,
            ],
            'PERFIL CARDIACO' => [
                '36. Troponina I' => ['TROPONINA-I (cTnl)'],
            ],
            'PERFIL ENDOCRINO' => [
                '39. TSH'                                => ['TIROTROPINA (TSH)'],
                '40. T3 Total'                           => ['TRIYODOTIRONINA (T3)'],
                '41. T3 Libre'                           => ['TRIYODOTIRONINA LIBRE (FT3)'],
                '42. T4 Total'                           => ['TIROXINA TOTAL (T4)'],
                '43. T4 Libre'                           => ['TIROXINA LIBRE (FT4)'],
                '44. Ac. anti-TPO'                       => ['ANTI-PEROXIDASA (Anti TPO)'],
                '45. Ac. anti-Tg'                        => ['ANTI-TIROGLOBULINA (Anti Tg)'],
                '46. Ac. anti-receptor de TSH (TRAb)'    => null,
            ],
            'MARCADORES TUMORALES' => [
                '47. Alfa-fetoproteína (AFP)'                  => ['ALFA FETO PROTEINA (AFP)'],
                '48. Antígeno Carcinoembrionario (CEA)'        => ['CEA'],
                '49. CA 125 (ca. ovario)'                      => ['CA-125'],
                '50. CA 15-3 (ca. mama)'                       => ['CA 15-3'],
                '51. CA 19-9 (ca. páncreas)'                   => ['CA 19-9'],
                '52. Antígeno prostático específico (PSA)'     => ['PSA TOTAL'],
            ],
        ];

        $nombres = collect($filas)->flatten()->filter()->unique()->values();

        $subRangos = [];
        foreach (['', '_2', '_3', '_4', '_5'] as $i) {
            array_push($subRangos, "ar.rango{$i}_descripcion", "ar.rango{$i}_minimo", "ar.rango{$i}_maximo");
        }

        $resultados = DB::table('resultado_laboratorios as r')
            ->join('solicitudes as s', 's.id', '=', 'r.solicitude_id')
            ->join('area_rangos as ar', 'ar.id', '=', 'r.area_rango_id')
            ->where('r.area_id', self::AREA_INMUNOLOGIA)
            ->whereNull('r.deleted_at')
            ->whereNull('s.deleted_at')
            ->whereIn('ar.rango_nombre', $nombres)
            ->whereNotNull('r.valor_final')
            ->where('r.valor_final', '!=', '')
            ->when($dateFrom, fn ($q) => $q->whereDate('s.fecha_solicitud', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('s.fecha_solicitud', '<=', $dateTo))
            ->select('r.solicitude_id', 'r.valor_final', 's.paciente_genero', 'ar.rango_nombre', ...$subRangos)
            ->get()
            ->map(function ($r) {
                [$min, $max] = $this->limitesPorGenero($r);
                return [
                    'nombre' => $r->rango_nombre,
                    'sid'    => $r->solicitude_id,
                    'valor'  => $this->valorNumerico($r->valor_final),
                    'min'    => $min,
                    'max'    => $max,
                ];
            });

        $grupos = [];
        foreach ($filas as $grupo => $items) {
            foreach ($items as $label => $analitos) {
                $grupos[$grupo][] = $this->entsFila(
                    $label,
                    $analitos === null ? null : $resultados->whereIn('nombre', $analitos)->values()
                );
            }
        }

        // Pacientes atendidos en el área: solicitudes con alguna prestación de inmunología
        $solicitudes = DB::table('servicio_solicitudes as ss')
            ->join('solicitudes as s', 's.id', '=', 'ss.solicitude_id')
            ->where('ss.area_id', self::AREA_INMUNOLOGIA)
            ->whereNull('ss.deleted_at')
            ->whereNull('s.deleted_at')
            ->when($dateFrom, fn ($q) => $q->whereDate('s.fecha_solicitud', '>=', $dateFrom))
            ->when($dateTo,   fn ($q) => $q->whereDate('s.fecha_solicitud', '<=', $dateTo))
            ->distinct()
            ->get(['s.id', 's.tipo_atencion']);

        $totalSus      = $solicitudes->where('tipo_atencion', 'SI')->count();
        $totalExternos = $solicitudes->where('tipo_atencion', '!=', 'SI')->count();
        $totalGeneral  = $solicitudes->count();

        return array_merge(
            compact('grupos', 'totalSus', 'totalExternos', 'totalGeneral', 'dateFrom', 'dateTo'),
            $this->entsPeriodo($dateFrom),
            ['area' => 'AREA INMUNOLOGÍA', 'hoja' => 'ENTs Inmunología', 'archivo' => 'ents_inmunologia']
        );
    }

    // Fila del informe; sin registros (prueba que no se realiza) queda en blanco
    private function entsFila(string $label, $registros = null): array
    {
        $fila = ['label' => $label, 'pacientes' => '', 'procesadas' => '', 'debajo' => '', 'encima' => ''];
        if ($registros === null) {
            return $fila;
        }

        $vacioSiCero = fn (int $n) => $n > 0 ? $n : '';
        $fila['pacientes']  = $vacioSiCero($registros->pluck('sid')->unique()->count());
        $fila['procesadas'] = $vacioSiCero($registros->count());
        $fila['debajo']     = $vacioSiCero($registros->filter(
            fn ($r) => $r['valor'] !== null && $r['min'] !== null && $r['valor'] < $r['min']
        )->count());
        $fila['encima']     = $vacioSiCero($registros->filter(
            fn ($r) => $r['valor'] !== null && $r['max'] !== null && $r['valor'] > $r['max']
        )->count());

        return $fila;
    }

    // "12,5" → 12.5; "< a 0.005" / ">100" quedan apenas por debajo / encima
    // del número; un texto sin número (Reactivo, Negativo...) → null
    private function valorNumerico($valor): ?float
    {
        if (!preg_match('/^\s*([<>])?\s*(?:a\s+)?(-?\d+(?:[.,]\d+)?)/i', (string) $valor, $m)) {
            return null;
        }
        $n = (float) str_replace(',', '.', $m[2]);

        return match ($m[1]) {
            '<'     => $n - 1e-9,
            '>'     => $n + 1e-9,
            default => $n,
        };
    }

    // Límites del rango de referencia; si tiene sub-rangos por sexo
    // ("Mujer: hasta", "Varón: hasta", "Hombre:") se usa el del paciente
    private function limitesPorGenero(object $r): array
    {
        $num = fn ($v) => ($v === null || $v === '') ? null : (float) $v;

        $limites = [];
        foreach (['', '_2', '_3', '_4', '_5'] as $i) {
            $limites[] = [
                (string) $r->{"rango{$i}_descripcion"},
                $num($r->{"rango{$i}_minimo"}),
                $num($r->{"rango{$i}_maximo"}),
            ];
        }

        $patron = ['F' => '/MUJER/iu', 'M' => '/VAR[OÓ]N|HOMBRE/iu'][$r->paciente_genero ?? ''] ?? null;
        $elegido = $limites[0];
        if ($patron) {
            foreach ($limites as $l) {
                if (preg_match($patron, $l[0]) && ($l[1] !== null || $l[2] !== null)) {
                    $elegido = $l;
                    break;
                }
            }
        }

        return [$elegido[1], $elegido[2]];
    }

    private function entsPeriodo(?string $dateFrom): array
    {
        $meses = [1=>'ENERO',2=>'FEBRERO',3=>'MARZO',4=>'ABRIL',5=>'MAYO',6=>'JUNIO',
                  7=>'JULIO',8=>'AGOSTO',9=>'SEPTIEMBRE',10=>'OCTUBRE',11=>'NOVIEMBRE',12=>'DICIEMBRE'];

        return [
            'mes'     => $dateFrom ? ($meses[(int) date('n', strtotime($dateFrom))] ?? '') : '',
            'gestion' => $dateFrom ? date('Y', strtotime($dateFrom)) : date('Y'),
        ];
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
        $procedencia = $request->get('procedencia'); // 'HG' | 'EXTERNO' | null
        $esHg       = $this->sqlEsHospitalGeneral();

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
            ->when($procedencia === 'HG', fn ($q) => $q->whereRaw($esHg))
            ->when($procedencia === 'EXTERNO', fn ($q) => $q->whereRaw("NOT {$esHg}"))
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
                's.codigo',
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
                's.establecimiento_salud',
                's.tipo_atencion',
                's.tipo_paciente_externo',
                DB::raw('u.name as usuario_nombre'),
                DB::raw("CASE WHEN {$esHg} THEN 'HG' ELSE 'EXTERNO' END as procedencia")
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
