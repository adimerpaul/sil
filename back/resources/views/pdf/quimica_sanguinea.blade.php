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
            font-size: 9px;
            color:#111;
            line-height: 1;
        }

        /* 2 columnas (2 copias: left/right) */
        .sheet{ width:100%; overflow:hidden; }
        .half{
            width:48%;
            float:left;
            overflow:hidden;
            padding:0;
        }
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

        /* Tipos */
        .title { font-weight:700; font-size: 10.2px; text-align:center; }
        .subtitle { font-size: 8px; text-align:center; margin-top: 1px; }
        .muted { color:#555; }
        .small { font-size: 7.6px; }
        .center { text-align:center; }
        .right { text-align:right; }
        .clip{ overflow:hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* Líneas */
        .hr { border-top: 1.8px solid #111; margin: 2px 0; }
        .box { border: 1px solid #111; padding: 3px 4px; }
        .label{ font-weight:700; }
        .line{
            border-bottom: 1px solid #111;
            height: 12px;
            padding: 0 3px;
            font-size: 7.7px;
        }

        /* Tablas */
        table{ width:100%; border-collapse: collapse; table-layout: fixed; }
        th, td{ border:1px solid #111; padding: 1.6px 3px; vertical-align: middle; }
        th{ background:#f2f2f2; font-weight:700; font-size: 7.8px; }
        .no-border td, .no-border th{ border:none; padding:0; }

        .section{ margin-top: 4px; }
        .section h3{
            margin: 0 0 2px;
            font-size: 8.2px;
            text-transform: uppercase;
            letter-spacing: .2px;
        }

        /* Form grid (cabecera) */
        .form-grid td{
            border:none;
            padding: 2px 3px 2px 0;
            vertical-align: bottom;
            font-size: 7.6px;
        }

        /* Fuera de rango */
        .out-range{
            color: #c10015;
            font-weight: 700;
        }

        /* Limpieza float */
        .clearfix::after{ content:""; display:block; clear:both; }
    </style>
</head>

<body>

@php
    $q = $quimica;

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

        // Solo aplica para numéricos (si es texto: reactivo/no reactivo, no marcamos)
        if (!is_numeric($valor)) return false;

        $num = floatval($valor);

        if (!is_null($r->rango_minimo) && $num < $r->rango_minimo) return true;
        if (!is_null($r->rango_maximo) && $num > $r->rango_maximo) return true;

        return false;
    };

    /* =========================
       HELPERS: PRINT
       ========================= */
    $v = function($val){
        return ($val === null || $val === '') ? '' : $val;
    };

    /* =========================
       DEFINICIÓN DE BLOQUES
       (NOMBRE RANGO debe coincidir con AreaRango.rango_nombre)
       ========================= */
    $bloques = [
        [
            'titulo' => 'Química sanguínea básica',
            'cols' => ['ANALITO','RES','UNIDAD','RANGO'],
            'items' => [
                ['Ácido Úrico', 'acido_urico'],
                ['Albúmina', 'albumina'],
                ['Proteínas totales', 'proteinas_totales'],
                ['Glucosa', 'glucosa'],
                ['Urea', 'urea'],
                ['NUS', 'nus'],
                ['Creatinina', 'creatinina'],
            ],
        ],
        [
            'titulo' => 'Enzimas hepáticas y bilirrubinas',
            'cols' => ['ANALITO','RES','UNIDAD','RANGO'],
            'items' => [
                ['Bilirrubina Total', 'bilirrubina_total'],
                ['Bilirrubina Directa', 'bilirrubina_directa'],
                ['Bilirrubina Indirecta', 'bilirrubina_indirecta'],
                ['G.O.T. (TGO)', 'got'],
                ['G.P.T. (TGP)', 'gpt'],
                ['Fosfatasa Alcalina', 'fosfatasa_alcalina'],
                ['GGT', 'ggt'],
                ['Amilasa', 'amilasa'],
            ],
        ],
        [
            'titulo' => 'Perfil lipídico',
            'cols' => ['ANALITO','RES','UNIDAD','RANGO'],
            'items' => [
                ['Colesterol total', 'colesterol_total'],
                ['Triglicéridos', 'trigliceridos'],
                ['HDL Colesterol', 'hdl_colesterol'],
                ['LDL Colesterol', 'ldl_colesterol'],
                ['VLDL Colesterol', 'vldl_colesterol'],
            ],
        ],
        [
            'titulo' => 'Electrolitos y minerales',
            'cols' => ['ANALITO','RES','UNIDAD','RANGO'],
            'items' => [
                ['Sodio', 'sodio'],
                ['Potasio', 'potasio'],
                ['Cloro', 'cloro'],
                ['Calcio', 'calcio'],
                ['Fósforo', 'fosforo'],
                ['Magnesio', 'magnesio'],
                ['LDH', 'ldh'],
                ['Hierro sérico', 'hierro_serico'],
                ['Ferritina', 'ferritina'],
            ],
        ],
        [
            'titulo' => 'Orina de 24 horas',
            'cols' => ['PARÁMETRO','RES','UNIDAD','RANGO'],
            'items' => [
                ['Creatinuria 24 hrs.', 'creatinuria_24h'],
                ['Proteinuria de 24 hrs.', 'proteinuria_24h'],
                ['VOLUMEN', 'volumen_24h'],
            ],
        ],
        [
            'titulo' => 'Control glucémico',
            'cols' => ['PARÁMETRO','RES','UNIDAD','RANGO'],
            'items' => [
                ['Hb glicosilada', 'hb_glicosilada'],
                ['Hb A1C', 'hb_a1c'],
            ],
        ],
        [
            'titulo' => 'Otros / Cinéticos',
            'cols' => ['PRUEBA','RES','UNIDAD','RANGO'],
            'items' => [
                ['CK total', 'ck_total'],
                ['CK-MB', 'ck_mb'],
                ['GOT CINÉTICO', 'got_cinetico'],
                ['GPT CINÉTICO', 'gpt_cinetico'],
            ],
        ],
        [
            'titulo' => 'Pruebas serológicas',
            'cols' => ['PRUEBA','RES','UNIDAD','RANGO/INTERP.'],
            'items' => [
                ['ASO', 'aso'],
                ['FR', 'fr'],
                ['PCR', 'pcr'],
                ['Prueba rápida de VIH', 'prueba_rapida_vih'],
                ['RPR', 'rpr'],
                ['Reacción de Widal', 'reaccion_widal'],
                ['D.C.E.', 'dce'],
            ],
        ],
    ];
@endphp


<div class="sheet clearfix">

    @foreach(['left','right'] as $side)
        <div class="half half-{{ $side }}" style="margin: 10px 6px;">

            {{-- ===== HEADER ===== --}}
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

            {{-- ===== CABECERA ===== --}}
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
                    <td><div class="line clip">{{ $solicitud->muestra_identificacion ?? 'SUERO / SANGRE' }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">EST. DE SALUD:</span></td>
                    <td><div class="line clip">{{ $solicitud->establecimiento_salud ?? '-' }}</div></td>
                    <td><span class="label">CI:</span></td>
                    <td><div class="line clip">{{ $solicitud->paciente_ci ?? '-' }}</div></td>
                </tr>
            </table>

            <div class="section center" style="margin-top:4px; font-weight:700; font-size:9px;">
                QUÍMICA SANGUÍNEA
            </div>

            <div class="center muted small" style="margin-top:1px;">
                Equipo: {{ $q->equipo ?? ($solicitud->muestra_equipo ?? '—') }} · Método: {{ $q->metodo ?? '—' }}
            </div>

            {{-- ===== TABLAS (TODAS) ===== --}}
            @foreach($bloques as $b)
                <div class="section">
                    <h3>{{ $b['titulo'] }}</h3>
                    <table>
                        <thead>
                        <tr>
                            <th style="width:42%;">{{ $b['cols'][0] }}</th>
                            <th style="width:18%;" class="center">{{ $b['cols'][1] }}</th>
                            <th style="width:18%;" class="center">{{ $b['cols'][2] }}</th>
                            <th style="width:22%;" class="center">{{ $b['cols'][3] }}</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($b['items'] as $it)
                            @php
                                $nombre = $it[0];
                                $field  = $it[1];
                                $valor  = $q->{$field} ?? null;
                                $isOut  = $outOfRange($nombre, $valor);
                            @endphp
                            <tr>
                                <td>{{ $nombre }}</td>
                                <td class="center {{ $isOut ? 'out-range' : '' }}">
                                    {{ $v($valor) }}
                                </td>
                                <td class="center">{{ $rangoUnidad($nombre) }}</td>
                                <td class="center">{{ $rangoTexto($nombre) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach

            {{-- ===== OBSERVACIONES ===== --}}
            <div class="section">
                <h3>Observaciones</h3>
                <div class="box" style="min-height:22px;">{{ $q->observaciones ?? '' }}</div>
            </div>

            {{-- ===== FIRMAS ===== --}}
            <table class="no-border" style="margin-top:6px;">
                <tr>
                    <td class="center" style="width:50%;">
                        ___________________________<br>
                        <span class="muted small">Firma / Sello</span>
                    </td>
                    <td class="center" style="width:50%;">
                        ___________________________<br>
                        <span class="muted small">Bioquímico(a) / Responsable</span>
                    </td>
                </tr>
            </table>

        </div>
    @endforeach

</div>

</body>
</html>
