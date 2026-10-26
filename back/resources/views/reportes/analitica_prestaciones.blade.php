<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: letter portrait; margin: 18px 20px; }
        * { box-sizing: border-box; }

        body {
            margin: 0; padding: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 8px;
            color: #111;
        }

        .page-break { page-break-after: always; }
        table { width: 100%; border-collapse: collapse; }

        /* ── Cabecera tipo cuaderno ── */
        .cab td { border: 1px solid #333; padding: 3px; vertical-align: middle; }
        .cab .inst { text-align: center; font-size: 8px; line-height: 1.25; }
        .cab .inst b { font-size: 9px; }
        .cab .titulo { text-align: center; font-size: 12px; font-weight: 700; background: #e8e8e8; }
        .cab .codigo { text-align: center; font-size: 8px; }
        .sub { font-size: 7px; color: #555; margin: 3px 0 5px; }

        /* ── Registro ── */
        .reg th { background: #1b62a9; color: #fff; border: 1px solid #333; padding: 3px 2px; font-size: 7.5px; }
        .reg td { border: 1px solid #333; padding: 3px 3px; vertical-align: top; }
        .reg tr { page-break-inside: avoid; }
        .reg .cod { color: #c62828; font-weight: 700; font-size: 9px; text-align: center; }
        .reg .pac { font-weight: 700; font-size: 8.5px; }
        .reg .dx { color: #444; font-style: italic; margin-top: 1px; }
        .reg .dr { color: #333; margin-top: 1px; }
        .center { text-align: center; }
        .small { font-size: 7px; color: #555; }

        /* ── Validación ── */
        .val { margin-top: 8px; }
        .val td { border: 1px solid #333; padding: 2px; font-size: 6.5px; height: 14px; text-align: center; }
        .val td.lbl { text-align: left; width: 22%; font-weight: 700; }
    </style>
</head>
<body>

{{-- Igual que el cuaderno: hojas de 12 pacientes, cada una con cabecera y
     cuadro de validación (además DOMPDF no soporta tablas muy grandes). --}}
@php
    $paginas = $areas->flatMap(fn ($registros, $area) => $registros->chunk(12)->values()->map(fn ($filas, $i) => [
        'area' => $area,
        'filas' => $filas,
        'hoja' => $i + 1,
        'hojas' => (int) ceil($registros->count() / 12),
        'total' => $registros->count(),
    ]))->values();
@endphp
@forelse($paginas as $pagina)
    @php($area = $pagina['area'])
    @php($registros = $pagina['filas'])
    <div class="{{ $loop->last ? '' : 'page-break' }}">
        <table class="cab">
            <tr>
                <td rowspan="2" style="width:13%; text-align:center;">
                    <img src="{{ public_path('img/logo-hospital.png') }}" style="height:46px;" alt="Logo">
                </td>
                <td class="inst">
                    <b>HOSPITAL GENERAL SAN JUAN DE DIOS ORURO</b><br>
                    SERVICIO DE LABORATORIO DE ANÁLISIS CLÍNICO - MICROBIOLÓGICO<br>
                    DIRECCIÓN: SAN FELIPE, 6 DE OCTUBRE Y TARIJA<br>
                    REGISTRO CONALAB: 001 REGISTRO CODELAB: 000004
                </td>
                <td class="codigo" style="width:16%;">CÓDIGO<br><br>&nbsp;</td>
                <td rowspan="2" style="width:13%; text-align:center;">
                    <img src="{{ public_path('img/logo-labo.png') }}" style="height:46px;" alt="Logo">
                </td>
            </tr>
            <tr>
                <td class="titulo">REGISTRO DE PACIENTES ÁREA: {{ mb_strtoupper($area, 'UTF-8') }}</td>
                <td class="codigo">VERSIÓN: 01</td>
            </tr>
        </table>

        <div class="sub">
            {{ $subtitulo }} &nbsp;|&nbsp; Pacientes del área: <b>{{ $pagina['total'] }}</b>
            &nbsp;|&nbsp; Hoja <b>{{ $pagina['hoja'] }}</b> de {{ $pagina['hojas'] }}
            &nbsp;|&nbsp; Generado: {{ $generado->format('d/m/Y H:i') }}
        </div>

        <table class="reg">
            <thead>
            <tr>
                <th style="width:7%">Código</th>
                <th style="width:8%">Fecha</th>
                <th style="width:7%">N° Pac.</th>
                <th>Paciente / Diagnóstico / Médico</th>
                <th style="width:6%">Edad</th>
                <th style="width:13%">Unidad / Sala</th>
                <th style="width:22%">Prestaciones</th>
                <th style="width:11%">Usuario</th>
            </tr>
            </thead>
            <tbody>
            @foreach($registros as $r)
                <tr>
                    <td class="cod">{{ $r['codigo'] }}</td>
                    <td class="center">{{ $r['fecha'] }}</td>
                    <td class="center">{{ $r['nro_registro'] }}</td>
                    <td>
                        <div class="pac">{{ $r['paciente'] }}</div>
                        @if($r['diagnostico'])<div class="dx">Dx: {{ $r['diagnostico'] }}</div>@endif
                        @if($r['doctor'])<div class="dr">Dr(a). {{ $r['doctor'] }}</div>@endif
                    </td>
                    <td class="center">{{ $r['edad'] }}</td>
                    <td>{{ $r['unidad'] }}</td>
                    <td>{{ $r['prestaciones'] }}</td>
                    <td class="small">{{ $r['usuario'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>

        <table class="val">
            <tr>
                <td class="lbl">VALIDACIÓN DE LA PÁGINA DE RESULTADOS: (COLOQUE EL N° CORRESPONDIENTE)</td>
                @for($i = 0; $i < 5; $i++)
                    <td><b>N°</b></td><td><b>C</b></td><td><b>NC</b></td>
                @endfor
            </tr>
            @foreach([
                'SOLICITUD DE DATOS LEGIBLES Y CONCORDANTES CON LA CORRECCIÓN',
                'ANTES DE COMENZAR CON EL PROCESO SE CORRIÓ CONTROLES DE CALIDAD INTERNOS MISMOS ESTABAN EN RANGO',
                'RESULTADOS REVISADOS Y CONFORMES LISTOS PARA SU ENTREGA',
            ] as $criterio)
                <tr>
                    <td class="lbl">{{ $criterio }}</td>
                    @for($i = 0; $i < 15; $i++)<td></td>@endfor
                </tr>
            @endforeach
        </table>
    </div>
@empty
    <div style="text-align:center; margin-top:40px;">Sin pacientes para los filtros seleccionados.</div>
@endforelse

</body>
</html>
