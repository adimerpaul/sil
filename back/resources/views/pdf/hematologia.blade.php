<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">

    <style>
        @page { size: letter landscape; margin: 10px 12px; }
        * { box-sizing: border-box; }

        body{
            margin:0; padding:0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color:#111;
            line-height: 1;
        }

        /* 2 columnas */
        .sheet{
            width:100%;
            overflow:hidden;
        }
        .half{
            width:48%;
            float:left;
            overflow:hidden;
            padding:0;
        }

        /* Ajusta tamaño aquí (más grande = más cerca a 1.05) */
        .half-left{
            transform: scale(1.02);
            transform-origin: top left;
            padding-right: 6px;
        }
        .half-right{
            transform: scale(1.02);
            transform-origin: top left;
            padding-left: 6px;
        }

        /* ====== TIPOS ====== */
        .title { font-weight:700; font-size: 10.2px; text-align:center; }
        .subtitle { font-size: 8px; text-align:center; margin-top: 1px; }
        .muted { color:#555; }

        /* ====== LÍNEAS / CAJAS ====== */
        .hr { border-top: 1.8px solid #111; margin: 2px 0; }
        .box { border: 1px solid #111; padding: 3px 4px; }
        .small { font-size: 7.6px; }
        .center { text-align:center; }
        .right { text-align:right; }

        /* ====== TABLAS ====== */
        table{ width:100%; border-collapse: collapse; table-layout: fixed; }
        th, td{ border:1px solid #111; padding: 1.8px 3px; vertical-align: middle; }
        th{ background:#f2f2f2; font-weight:700; font-size: 7.8px; }
        .no-border td, .no-border th{ border:none; padding:0; }

        .section{ margin-top: 4px; }
        .section h3{
            margin: 0 0 2px;
            font-size: 8.2px;
            text-transform: uppercase;
            letter-spacing: .2px;
        }

        /* ====== FORM ====== */
        .form-grid td{
            border:none;
            padding: 2px 3px 2px 0;
            vertical-align: bottom;
            font-size: 7.6px;
        }
        .label{ font-weight:700; }
        .line{
            border-bottom: 1px solid #111;
            height: 12px;
            padding: 0 3px;
            font-size: 7.7px;
        }

        img{ max-width: 100%; }
        .clip{ overflow:hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* ====== FUERA DE RANGO ====== */
        .out-range{
            color: #c10015;
            font-weight: 700;
        }

        /* Limpieza float */
        .clearfix::after{
            content:"";
            display:block;
            clear:both;
        }
    </style>
</head>

<body>

@php
    /* =========================
       HELPERS: RANGOS
       ========================= */
    $rango = function($nombre) use ($rangos) {
        if (!$rangos) return null;
        $n = mb_strtolower(trim($nombre));
        foreach ($rangos as $r) {
            $rn = mb_strtolower(trim($r->rango_nombre ?? ''));
            if ($rn === $n) return $r;
        }
        return null;
    };

    $rangoTexto = function($nombre) use ($rango) {
        $r = $rango($nombre);
        if (!$r) return '';
        if (!is_null($r->rango_minimo) && !is_null($r->rango_maximo)) {
            return $r->rango_minimo.' - '.$r->rango_maximo;
        }
        return $r->interpretacion ?? '';
    };

    $rangoUnidad = function($nombre) use ($rango) {
        $r = $rango($nombre);
        return $r->unidad ?? '';
    };

    $outOfRange = function($nombre, $valor) use ($rango) {
        if ($valor === null || $valor === '') return false;
        $r = $rango($nombre);
        if (!$r) return false;

        $num = floatval($valor);

        if (!is_null($r->rango_minimo) && $num < $r->rango_minimo) return true;
        if (!is_null($r->rango_maximo) && $num > $r->rango_maximo) return true;

        return false;
    };
@endphp

<div class="sheet clearfix">

{{--    <!-- COPIA IZQUIERDA -->--}}
{{--    <div class="half half-left">--}}
{{--        @include('pdf.partials.hematologia_copia', [--}}
{{--            'solicitud' => $solicitud,--}}
{{--            'hematologia' => $hematologia,--}}
{{--            'rangos' => $rangos,--}}
{{--            'rangoTexto' => $rangoTexto,--}}
{{--            'rangoUnidad' => $rangoUnidad,--}}
{{--            'outOfRange' => $outOfRange,--}}
{{--        ])--}}
{{--    </div>--}}

{{--    <!-- COPIA DERECHA -->--}}
{{--    <div class="half half-right">--}}
{{--        @include('pdf.partials.hematologia_copia', [--}}
{{--            'solicitud' => $solicitud,--}}
{{--            'hematologia' => $hematologia,--}}
{{--            'rangos' => $rangos,--}}
{{--            'rangoTexto' => $rangoTexto,--}}
{{--            'rangoUnidad' => $rangoUnidad,--}}
{{--            'outOfRange' => $outOfRange,--}}
{{--        ])--}}
{{--    </div>--}}
    @foreach(['left', 'right'] as $side)
        <div class="half half-{{ $side }}" style="margin: 10px 6px;">
            <!-- ===== HEADER ===== -->
            <table class="no-border">
                <tr>
                    <td style="width:15%;">
                        <img src="{{ public_path('img/logo-hospital.png') }}" style="width:58px;">
                    </td>

                    <td>
                        <div class="title">HOSPITAL GENERAL SAN JUAN DE DIOS ORURO BLOQUE CENTRAL</div>
                        <div class="subtitle muted">LABORATORIO DE ANÁLISIS CLÍNICO - MICROBIOLÓGICO</div>
                        <div class="subtitle muted small">Dirección: San Felipe entre 6 de Octubre y Tarija</div>
                        <div class="subtitle muted small">REGISTRO CONALAB: 001 &nbsp;&nbsp; REGISTRO CODELAB: 000004</div>
                    </td>

                    <td style="width:15%;" class="right">
                        <img src="{{ public_path('img/logo-labo.png') }}" style="width:58px;">
                    </td>
                </tr>
            </table>

            <div class="hr"></div>

            <!-- ===== FORM ===== -->
            <table class="form-grid" style="margin-top:2px;">
                <tr>
                    <td style="width:18%"><span class="label">CÓDIGO:</span></td>
                    <td style="width:32%"><div class="line clip">{{ $solicitud->codigo ?? $solicitud->id }}</div></td>
                    <td style="width:20%"><span class="label">NRO. REGISTRO:</span></td>
                    <td style="width:30%"><div class="line clip">{{ $solicitud->nro_registro ?? '-' }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">PACIENTE:</span></td>
                    <td><div class="line clip">{{ $solicitud->paciente_nombre ?? '-' }}</div></td>
                    <td><span class="label">EDAD:</span></td>
                    <td><div class="line clip">{{ $solicitud->paciente_edad ?? '-' }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">MEDICO SOL.:</span></td>
                    <td><div class="line clip">{{ $solicitud->doctor_nombre ?? '-' }}</div></td>
                    <td><span class="label">SEXO:</span></td>
                    <td><div class="line clip">{{ $solicitud->paciente_genero ?? '-' }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">FECHA SOL. MEDICO:</span></td>
                    <td><div class="line clip">{{ $solicitud->fecha_solicitud ?? '-' }}</div></td>
                    <td><span class="label">TIPO MUESTRA:</span></td>
                    <td><div class="line clip">{{ $solicitud->muestra_identificacion ?? 'SANGRE / COÁGULO' }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">EST. DE SALUD:</span></td>
                    <td><div class="line clip">{{ $solicitud->establecimiento_salud ?? '-' }}</div></td>
                    <td><span class="label">CI:</span></td>
                    <td><div class="line clip">{{ $solicitud->paciente_ci ?? '-' }}</div></td>
                </tr>
            </table>

            <div class="section center" style="margin-top:4px; font-weight:700; font-size:9px;">
                HEMATOLOGÍA
            </div>

            <div class="center muted small" style="margin-top:1px;">
                Equipo: {{ $solicitud->muestra_equipo ?? '—' }} · Método: {{ $hematologia->metodo ?? '—' }}
            </div>

            <!-- ===== HEMOGRAMA BÁSICO ===== -->
            <div class="section">
                <h3>Hemograma básico</h3>
                <table>
                    <thead>
                    <tr>
                        <th style="width:38%;">PRUEBA</th>
                        <th style="width:18%;" class="center">RESULTADO</th>
                        <th style="width:18%;" class="center">UNIDAD</th>
                        <th style="width:26%;" class="center">RANGO</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Glóbulos rojos</td>
                        <td class="center {{ $outOfRange('Glóbulos Rojos', $hematologia->globulos_rojos ?? null) ? 'out-range' : '' }}">
                            {{ $hematologia->globulos_rojos ?? '' }}
                        </td>
                        <td class="center">{{ $rangoUnidad('Glóbulos Rojos') }}</td>
                        <td class="center">{{ $rangoTexto('Glóbulos Rojos') }}</td>
                    </tr>
                    <tr>
                        <td>Glóbulos blancos</td>
                        <td class="center {{ $outOfRange('Glóbulos Blancos', $hematologia->globulos_blancos ?? null) ? 'out-range' : '' }}">
                            {{ $hematologia->globulos_blancos ?? '' }}
                        </td>
                        <td class="center">{{ $rangoUnidad('Glóbulos Blancos') }}</td>
                        <td class="center">{{ $rangoTexto('Glóbulos Blancos') }}</td>
                    </tr>
                    <tr>
                        <td>Plaquetas</td>
                        <td class="center {{ $outOfRange('Plaquetas', $hematologia->plaquetas ?? null) ? 'out-range' : '' }}">
                            {{ $hematologia->plaquetas ?? '' }}
                        </td>
                        <td class="center">{{ $rangoUnidad('Plaquetas') }}</td>
                        <td class="center">{{ $rangoTexto('Plaquetas') }}</td>
                    </tr>
                    <tr>
                        <td>Hemoglobina</td>
                        <td class="center {{ $outOfRange('Hemoglobina', $hematologia->hemoglobina ?? null) ? 'out-range' : '' }}">
                            {{ $hematologia->hemoglobina ?? '' }}
                        </td>
                        <td class="center">{{ $rangoUnidad('Hemoglobina') }}</td>
                        <td class="center">{{ $rangoTexto('Hemoglobina') }}</td>
                    </tr>
                    <tr>
                        <td>Hematocrito</td>
                        <td class="center {{ $outOfRange('Hematocrito', $hematologia->hematocrito ?? null) ? 'out-range' : '' }}">
                            {{ $hematologia->hematocrito ?? '' }}
                        </td>
                        <td class="center">{{ $rangoUnidad('Hematocrito') }}</td>
                        <td class="center">{{ $rangoTexto('Hematocrito') }}</td>
                    </tr>
                    <tr>
                        <td>VCM</td>
                        <td class="center {{ $outOfRange('VCM', $hematologia->vcm ?? null) ? 'out-range' : '' }}">
                            {{ $hematologia->vcm ?? '' }}
                        </td>
                        <td class="center">{{ $rangoUnidad('VCM') }}</td>
                        <td class="center">{{ $rangoTexto('VCM') }}</td>
                    </tr>
                    <tr>
                        <td>HBCM</td>
                        <td class="center {{ $outOfRange('HBCM', $hematologia->hbcm ?? null) ? 'out-range' : '' }}">
                            {{ $hematologia->hbcm ?? '' }}
                        </td>
                        <td class="center">{{ $rangoUnidad('HBCM') }}</td>
                        <td class="center">{{ $rangoTexto('HBCM') }}</td>
                    </tr>
                    <tr>
                        <td>CHCM</td>
                        <td class="center {{ $outOfRange('CHCM', $hematologia->chcm ?? null) ? 'out-range' : '' }}">
                            {{ $hematologia->chcm ?? '' }}
                        </td>
                        <td class="center">{{ $rangoUnidad('CHCM') }}</td>
                        <td class="center">{{ $rangoTexto('CHCM') }}</td>
                    </tr>
                    <tr>
                        <td>Leucocitos totales</td>
                        <td class="center {{ $outOfRange('Leucocitos Totales', $hematologia->leucocitos_totales ?? null) ? 'out-range' : '' }}">
                            {{ $hematologia->leucocitos_totales ?? '' }}
                        </td>
                        <td class="center">{{ $rangoUnidad('Leucocitos Totales') }}</td>
                        <td class="center">{{ $rangoTexto('Leucocitos Totales') }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- ===== DIFERENCIAL + COAGULOGRAMA ===== -->
            <table class="no-border" style="margin-top:4px;">
                <tr>
                    <td style="width:62%; padding-right:4px;">
                        <div class="section">
                            <h3>Recuento diferencial</h3>
                            <table>
                                <thead>
                                <tr>
                                    <th style="width:40%;">CÉLULA</th>
                                    <th style="width:18%;" class="center">%</th>
                                    <th style="width:22%;" class="center">ABS</th>
                                    <th style="width:20%;" class="center">REF%</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr><td>Basófilos</td><td class="center">{{ $hematologia->basofilos_porcentaje ?? '' }}</td><td class="center">{{ $hematologia->basofilos_absoluto ?? '' }}</td><td class="center">0–2</td></tr>
                                <tr><td>Eosinófilos</td><td class="center">{{ $hematologia->eosinofilos_porcentaje ?? '' }}</td><td class="center">{{ $hematologia->eosinofilos_absoluto ?? '' }}</td><td class="center">0–6</td></tr>
                                <tr><td>Cayados</td><td class="center">{{ $hematologia->cayados_porcentaje ?? '' }}</td><td class="center">{{ $hematologia->cayados_absoluto ?? '' }}</td><td class="center">&lt; 6</td></tr>
                                <tr><td>Segmentados</td><td class="center">{{ $hematologia->segmentados_porcentaje ?? '' }}</td><td class="center">{{ $hematologia->segmentados_absoluto ?? '' }}</td><td class="center">40–70</td></tr>
                                <tr><td>Linfocitos</td><td class="center">{{ $hematologia->linfocitos_porcentaje ?? '' }}</td><td class="center">{{ $hematologia->linfocitos_absoluto ?? '' }}</td><td class="center">20–45</td></tr>
                                <tr><td>Monocitos</td><td class="center">{{ $hematologia->monocitos_porcentaje ?? '' }}</td><td class="center">{{ $hematologia->monocitos_absoluto ?? '' }}</td><td class="center">2–10</td></tr>
                                <tr><td>Blastos</td><td class="center">{{ $hematologia->blastos_porcentaje ?? '' }}</td><td class="center">{{ $hematologia->blastos_absoluto ?? '' }}</td><td class="center">0</td></tr>
                                <tr><td>Metamielocitos</td><td class="center">{{ $hematologia->metamielocitos_porcentaje ?? '' }}</td><td class="center">{{ $hematologia->metamielocitos_absoluto ?? '' }}</td><td class="center">0</td></tr>
                                <tr><td>Eritroblastos</td><td class="center">{{ $hematologia->eritroblastos_porcentaje ?? '' }}</td><td class="center">{{ $hematologia->eritroblastos_absoluto ?? '' }}</td><td class="center">0</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </td>

                    <td style="width:38%;">
                        <div class="section">
                            <h3>Coagulograma</h3>
                            <table>
                                <thead>
                                <tr>
                                    <th style="width:48%;">PRUEBA</th>
                                    <th style="width:22%;" class="center">RES</th>
                                    <th style="width:30%;" class="center">REF</th>
                                </tr>
                                </thead>
                                <tbody>
                                <tr><td>TP</td><td class="center">{{ $hematologia->tiempo_protrombina ?? '' }}</td><td class="center">11–15</td></tr>
                                <tr><td>Actividad</td><td class="center">{{ $hematologia->actividad_protrombina ?? '' }}</td><td class="center">70–100</td></tr>
                                <tr><td>INR</td><td class="center">{{ $hematologia->inr ?? '' }}</td><td class="center">0.8–1.2</td></tr>
                                <tr><td>APTT</td><td class="center">{{ $hematologia->aptt ?? '' }}</td><td class="center">24–35</td></tr>
                                <tr><td>Fibrinógeno</td><td class="center">{{ $hematologia->fibrinogeno ?? '' }}</td><td class="center">2.0–4.0</td></tr>
                                <tr><td>V.E.S</td><td class="center">{{ $hematologia->ves ?? '' }}</td><td class="center">&lt; 20</td></tr>
                                </tbody>
                            </table>

                            <div class="section">
                                <h3>Grupo sanguíneo</h3>
                                <table>
                                    <tr>
                                        <td><b>ABO:</b> {{ $hematologia->grupo_sanguineo ?? '' }}</td>
                                        <td><b>Rh:</b> {{ $hematologia->factor_rh ?? '' }}</td>
                                    </tr>
                                </table>
                            </div>

                        </div>
                    </td>
                </tr>
            </table>

            <!-- ===== MORFOLOGÍA ===== -->
            <div class="section">
                <h3>Morfología de eritrocitos</h3>
                <div class="box" style="min-height:26px;">{{ $hematologia->morfologia_eritrocitos ?? '' }}</div>
            </div>

            <!-- ===== FIRMAS ===== -->
            <table class="no-border" style="margin-top:6px;">
                <tr>
                    <td class="center" style="width:33%;">
                        ___________________________<br>
                        <span class="muted small">Firma / Sello</span>
                    </td>
                    <td class="center" style="width:33%;">
                        ___________________________<br>
                        <span class="muted small">Bioquímico(a) / Responsable</span>
                    </td>
                    <td class="center" style="width:34%; position:relative;">
                        @if(!empty($qrSvgBase64))
                            <img
                                src="data:image/svg+xml;base64,{{ $qrSvgBase64 }}"
                                style="width:80px; height:80px;"
                                alt="QR"
                            >
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    @endforeach

</div>

</body>
</html>
