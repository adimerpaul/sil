<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

// Layout (empty arrays are skipped — do NOT use them):
// Row 1 : DGCF-R1.06 code + version
// Row 2 : Hospital name (merged A2:L2)
// Row 3 : Title (merged)
// Row 4 : Period (merged)
// Row 5 : Currency note (merged)
// Row 6 : Main headers — Nº, Descripción, Unidad, Precio | Cantidad (E6:H6) | Valores (I6:L6)
// Row 7 : Sub-headers — Saldo Ini, Entradas, Salidas, Saldo Fin × 2
// Row 8…N : Data rows
// Row N+1 : Totals row

class DetalleAlmacenExport implements FromArray, WithColumnWidths, WithEvents, WithStyles, WithTitle
{
    private const DATA_START = 8;

    public function __construct(
        private array $rows,
        private array $meta,
    ) {}

    public function array(): array
    {
        $periodo = $this->meta['periodo'] ?: 'DEL 1 DE ENERO AL 31 DE DICIEMBRE';

        $data = [
            // Row 1
            ['DGCF-R1.06', '', '', '', '', '', '', '', '', '', '', '', 'Versión 01'],
            // Row 2
            ['HOSPITAL GENERAL SAN JUAN DE DIOS ORURO'],
            // Row 3
            ['DETALLE DE ALMACENES-FARMACIA (BIENES DE CONSUMO)'],
            // Row 4
            [$periodo],
            // Row 5
            ['(Expresado en Bolivianos)'],
            // Row 6 — main headers (Cantidad spans E-H, Valores spans I-L)
            ['Nº', 'Partida presupuestaria', 'Descripción (Item)', 'Unidad de medida', 'Precio Unitario',
                'Cantidad', '', '', '',
                'Valores', '', '', ''],
            // Row 7 — sub-headers
            ['', '', '', '', '',
                'Saldo Inicial', 'Entradas', 'Salidas', 'Saldo Final',
                'Saldo Inicial', 'Entradas', 'Salidas', 'Saldo Final'],
        ];

        $dataStart = self::DATA_START;

        foreach ($this->rows as $i => $row) {
            $r = $dataStart + $i;
            $data[] = [
                $row['nro'],
                $row['subpartida_codigo'],
                $row['descripcion'],
                $row['unidad'],
                $row['precio_unitario'],
                $row['cant_saldo_ini'],
                $row['cant_entradas'],
                $row['cant_salidas'],
                $row['cant_saldo_final'],
                $row['val_saldo_ini'],
                $row['val_entradas'],
                $row['val_salidas'],
                $row['val_saldo_final'],
            ];
        }

        if ($this->rows) {
            $dataEnd = $dataStart + count($this->rows) - 1;
            $data[] = [
                '', '', 'TOTAL', '', '',
                "=SUM(F{$dataStart}:F{$dataEnd})",
                "=SUM(G{$dataStart}:G{$dataEnd})",
                "=SUM(H{$dataStart}:H{$dataEnd})",
                "=SUM(I{$dataStart}:I{$dataEnd})",
                "=SUM(J{$dataStart}:J{$dataEnd})",
                "=SUM(K{$dataStart}:K{$dataEnd})",
                "=SUM(L{$dataStart}:L{$dataEnd})",
                "=SUM(M{$dataStart}:M{$dataEnd})",
            ];
        } else {
            $data[] = ['', '', 'TOTAL', '', '', 0, 0, 0, 0, 0, 0, 0, 0];
        }

        return $data;
    }

    public function title(): string
    {
        return 'DGCF-R1.06 Detalle';
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5, 'B' => 16, 'C' => 65, 'D' => 12, 'E' => 13,
            'F' => 12, 'G' => 11, 'H' => 10, 'I' => 12,
            'J' => 15, 'K' => 14, 'L' => 14, 'M' => 15,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['size' => 9]],
            2 => ['font' => ['bold' => true, 'size' => 11],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            3 => ['font' => ['bold' => true, 'size' => 10],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            4 => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
            5 => ['alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                foreach (['A2:M2', 'A3:M3', 'A4:M4', 'A5:M5'] as $range) {
                    $sheet->mergeCells($range);
                }

                // Group headers span row 6
                $sheet->mergeCells('F6:I6');
                $sheet->mergeCells('J6:M6');
                // Fixed columns (A-D) span rows 6-7
                foreach (['A', 'B', 'C', 'D', 'E'] as $col) {
                    $sheet->mergeCells("{$col}6:{$col}7");
                }

                $headerStyle = [
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 9],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F5EA8']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '0A4A8A']]],
                ];
                $sheet->getStyle('A6:M7')->applyFromArray($headerStyle);
                $sheet->getRowDimension(6)->setRowHeight(22);
                $sheet->getRowDimension(7)->setRowHeight(22);

                // Group color overrides
                $sheet->getStyle('F6:I7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1A73C4');
                $sheet->getStyle('J6:M7')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('156A3C');

                $dataStart = self::DATA_START;
                $dataEnd = $dataStart + count($this->rows) - 1;
                $totalRow = $dataEnd + 1;

                if ($dataEnd >= $dataStart) {
                    $sheet->getStyle("A{$dataStart}:M{$dataEnd}")->applyFromArray([
                        'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'B8D0EA']]],
                        'font' => ['size' => 8],
                    ]);
                    for ($r = $dataStart; $r <= $dataEnd; $r++) {
                        if ($r % 2 === 0) {
                            $sheet->getStyle("A{$r}:M{$r}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F5F9FF');
                        }
                    }
                    $sheet->getStyle("E{$dataStart}:M{$dataEnd}")->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                }

                // Totals row: label (A-D), quantity blue (E-H), values yellow (I-L)
                $sheet->getStyle("A{$totalRow}:E{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '0A4A8A']]],
                ]);
                $sheet->getStyle("F{$totalRow}:I{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '0F5EA8']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '0A4A8A']]],
                ]);
                $sheet->getStyle("F{$totalRow}:I{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getStyle("J{$totalRow}:M{$totalRow}")->applyFromArray([
                    'font' => ['bold' => true, 'color' => ['rgb' => '000000'], 'size' => 11],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFD700']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => 'B8860B']]],
                ]);
                $sheet->getStyle("J{$totalRow}:M{$totalRow}")->getNumberFormat()->setFormatCode('#,##0.00');
                $sheet->getRowDimension($totalRow)->setRowHeight(18);
            },
        ];
    }
}
