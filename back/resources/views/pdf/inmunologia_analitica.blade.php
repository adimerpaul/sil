<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }

        body {
            margin: 0; padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.08;
            color: #111;
        }

        .muted  { color: #666; }
        .bold   { font-weight: 700; }
        .center { text-align: center; }
        .small  { font-size: 6.6px; }

        .no-border { border-collapse: collapse; width: 100%; }
        .no-border td { border: none; padding: 0; }

        .tbl {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .tbl th, .tbl td {
            border: 1px solid #111;
            padding: 1.5px 3px;
            vertical-align: middle;
        }
        .tbl th {
            background: #f7f7f7;
            font-size: 9px;
        }
        .tbl td { font-size: 9px; }

        .w-analito { width: 34%; }
        .w-res     { width: 14%; }
        .w-unid    { width: 12%; }
        .w-rango   { width: 26%; }
        .w-met     { width: 14%; }
        .out-range { color: #c10015; font-weight: 700; }
        .rango-linea { display: block; line-height: 1.25; }

        /* anchos cuando la tabla lleva columna de interpretación */
        .wi-analito { width: 26%; }
        .wi-res     { width: 11%; }
        .wi-unid    { width: 9%; }
        .wi-rango   { width: 21%; }
        .wi-interp  { width: 22%; }
        .wi-met     { width: 11%; }

        .section-title {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .02em;
            padding: 2px 3px;
            margin-top: 6px;
        }

        .subarea-title {
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .03em;
            background: #eef2f7;
            border-left: 3px solid #333;
            padding: 2px 4px;
            margin-top: 7px;
        }

        .comentario {
            border: 1px solid #111;
            padding: 3px 4px;
            margin-top: 7px;
            font-size: 9px;
        }

        .hr { border-top: 1.5px solid #111; margin: 3px 0; }
    </style>
</head>
<body>
@php
    function inmuno_out_of_range($valor, $min, $max) {
        if ($valor === null || $valor === '') return false;
        if (!is_numeric($valor)) return false; // valores de lista (texto) no se comparan
        $num = floatval($valor);
        if ($min !== null && $num < floatval($min)) return true;
        if ($max !== null && $num > floatval($max)) return true;
        return false;
    }

    // Los valores cuantitativos de inmunologia se imprimen siempre con 3 decimales.
    // Los resultados cualitativos (por ejemplo, POSITIVO/NEGATIVO) se conservan.
    function inmuno_formatear_valor($valor) {
        if ($valor === null || $valor === '') return '';

        return is_numeric($valor)
            ? number_format((float) $valor, 3, '.', '')
            : $valor;
    }

    // Parte un texto libre en líneas, descartando las vacías
    function inmuno_lineas_texto($texto) {
        if ($texto === null || trim($texto) === '') return [];

        return array_values(array_filter(
            array_map('trim', preg_split('/\r\n|\r|\n/', $texto)),
            fn ($linea) => $linea !== ''
        ));
    }

    // Cada sub-rango (y cada línea de la referencia) va en su propia línea
    function inmuno_rango_lineas($rango) {
        $defs = [
            ['rango_descripcion',   'rango_minimo',   'rango_maximo'],
            ['rango_2_descripcion', 'rango_2_minimo', 'rango_2_maximo'],
            ['rango_3_descripcion', 'rango_3_minimo', 'rango_3_maximo'],
            ['rango_4_descripcion', 'rango_4_minimo', 'rango_4_maximo'],
            ['rango_5_descripcion', 'rango_5_minimo', 'rango_5_maximo'],
        ];

        $lineas = [];
        foreach ($defs as [$campoDesc, $campoMin, $campoMax]) {
            $desc = $rango->$campoDesc ?? null;
            $min  = $rango->$campoMin ?? null;
            $max  = $rango->$campoMax ?? null;

            if (($desc === null || $desc === '') && $min === null && $max === null) continue;

            if ($min !== null && $max !== null)  $valor = inmuno_formatear_valor($min) . ' - ' . inmuno_formatear_valor($max);
            elseif ($min !== null)               $valor = '≥ ' . inmuno_formatear_valor($min);
            elseif ($max !== null)               $valor = '≤ ' . inmuno_formatear_valor($max);
            else                                 $valor = '';

            // Sin límites solo se imprime la descripción (sin los dos puntos)
            if ($desc && $valor !== '')  $lineas[] = $desc . ': ' . $valor;
            elseif ($desc)               $lineas[] = $desc;
            elseif ($valor !== '')       $lineas[] = $valor;
        }

        if ($rango->interpretacion) {
            foreach (preg_split('/\r\n|\r|\n/', $rango->interpretacion) as $linea) {
                $linea = trim($linea);
                if ($linea !== '') $lineas[] = $linea;
            }
        }

        return $lineas;
    }

    $realizadoPor = collect($prestaciones)->map(fn($p) => $p->realizado_por)->filter()->first();

    // Fecha de recepción de la muestra y comentario: uno solo para toda la analítica
    $fechaRecepcion = $solicitud->inmunologia_fecha_recepcion
        ? \Carbon\Carbon::parse($solicitud->inmunologia_fecha_recepcion)->format('d/m/Y')
        : null;
    $comentario = trim((string) ($solicitud->inmunologia_comentario ?? ''));
@endphp

{{-- el cuerpo va suelto, no dentro de una celda: DOMPDF no parte una celda entre
     páginas y descartaba en silencio todo lo que no entraba en la primera --}}
<div style="padding: 0 4px;">

            <div style="margin-top:-30px;">
                {!! view('components.headerSinCabeceraPequeno', [
                    'solicitud' => $solicitud,
                    'fecha_solicitud' => now()->format('d/m/Y H:i'),
                    'fecha_muestreo' => $fechaRecepcion,
                    'fecha_muestreo_label' => 'FECHA DE RECEPCIÓN DE LA MUESTRA:',
                    'fecha_muestreo_label_span' => 3,
                ])->render() !!}
            </div>

            <div class="center bold" style="font-size:12px; margin: 5px 0 2px;">INMUNOLOGÍA</div>

            <div class="center small muted" style="margin-bottom: 3px;">
                Pre-analítica (toma de muestra):
                {{ ($fechaPreAnalitica ?? null) ? $fechaPreAnalitica->format('d/m/Y H:i') : '---' }}
            </div>

            @php
                // en la BD conviven variantes ("HORMONAS" / "Hormonas"), se comparan normalizadas
                $claveSubarea = fn ($s) => preg_replace('/\s+/', ' ', mb_strtoupper(trim((string) $s)));
                $subareaActual = null;
            @endphp

            @foreach($prestaciones as $prest)
                {{-- las prestaciones vienen ordenadas por subárea: se titula cada vez que cambia --}}
                @if($claveSubarea($prest->subarea) !== $subareaActual)
                    @php $subareaActual = $claveSubarea($prest->subarea); @endphp
                    <div class="subarea-title">{{ $prest->subarea ?: 'OTROS' }}</div>
                @endif

                <div class="section-title">{{ $prest->nombre }}
                    @if($prest->metodo) <span class="muted" style="font-weight:400;">({{ $prest->metodo }})</span>@endif
                </div>

                @php
                    // La columna de interpretación solo aparece si algún rango la tiene
                    $conInterp = collect($prest->rangos)->contains(fn ($r) => !empty($r->interpretacion_resultado));
                @endphp

                <table class="tbl">
                    <thead>
                        <tr>
                            <th class="{{ $conInterp ? 'wi-analito' : 'w-analito' }}">Analito / Condición</th>
                            <th class="center {{ $conInterp ? 'wi-res' : 'w-res' }}">Resultado</th>
                            <th class="center {{ $conInterp ? 'wi-unid' : 'w-unid' }}">Unidad</th>
                            <th class="{{ $conInterp ? 'wi-rango' : 'w-rango' }}">Rango de referencia</th>
                            @if($conInterp)
                                <th class="wi-interp">Interpretación</th>
                            @endif
                            <th class="center {{ $conInterp ? 'wi-met' : 'w-met' }}">Método</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($prest->rangos as $rango)
                            @php $outRange = inmuno_out_of_range($rango->valor_final, $rango->rango_minimo, $rango->rango_maximo); @endphp
                            <tr>
                                <td>{{ $rango->rango_nombre }}</td>
                                <td class="center {{ $outRange ? 'out-range' : '' }}">{{ inmuno_formatear_valor($rango->valor_final) }}</td>
                                <td class="center">{{ $rango->unidad ?? '' }}</td>
                                <td>
                                    @forelse(inmuno_rango_lineas($rango) as $linea)
                                        <div class="rango-linea">{{ $linea }}</div>
                                    @empty
                                        &nbsp;
                                    @endforelse
                                </td>
                                @if($conInterp)
                                    <td>
                                        @forelse(inmuno_lineas_texto($rango->interpretacion_resultado) as $linea)
                                            <div class="rango-linea">{{ $linea }}</div>
                                        @empty
                                            &nbsp;
                                        @endforelse
                                    </td>
                                @endif
                                <td class="center">{{ $rango->metodo ?? $prest->metodo ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach

            @if($comentario !== '')
                <div class="comentario">
                    <span class="bold">COMENTARIO:</span>
                    @foreach(inmuno_lineas_texto($comentario) as $linea)
                        <div class="rango-linea">{{ $linea }}</div>
                    @endforeach
                </div>
            @endif

            {{-- FIRMAS + QR --}}
            <table class="no-border" style="margin-top: 8px;">
                <tr>
                    <td class="center" style="width:33%">
                        _____________________<br><span class="small muted">Firma</span>
                    </td>
                    <td class="center" style="width:33%">
                        _____________________<br>
                        <span class="small muted">
                            {{ $realizadoPor ?: '---' }}<br>
                            Bioquímico(a)
                        </span>
                    </td>
                    <td class="center" style="width:34%">
                        @if(!empty($qrSvgBase64))
                            <img
                                src="data:image/svg+xml;base64,{{ $qrSvgBase64 }}"
                                style="width:45px; height:45px;"
                                alt="QR"
                            >
                        @endif
                    </td>
                </tr>
            </table>

</div>
</body>
</html>
