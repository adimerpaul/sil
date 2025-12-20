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
        .sheet{ width:100%; overflow:hidden; }
        .half{ width:48%; float:left; overflow:hidden; padding:0; }

        /* Ajuste */
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

        /* Líneas / cajas */
        .hr { border-top: 1.8px solid #111; margin: 2px 0; }
        .box { border: 1px solid #111; padding: 3px 4px; }
        .small { font-size: 7.6px; }
        .center { text-align:center; }
        .right { text-align:right; }
        .bold{ font-weight:700; }

        /* Tablas */
        table{ width:100%; border-collapse: collapse; table-layout: fixed; }
        th, td{ border:1px solid #111; padding: 1.8px 3px; vertical-align: middle; }
        th{ background:#f2f2f2; font-weight:700; font-size: 7.8px; }
        .no-border td, .no-border th{ border:none; padding:0; }

        /* Form */
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

        /* Estilo tabla “lista” como el reporte */
        .list th, .list td{ border:none; }
        .list thead th{
            text-transform: uppercase;
            background: transparent;
            font-size: 7.6px;
            padding-top: 6px;
            padding-bottom: 6px;
            border-bottom: 1px solid #cfcfcf;
        }
        .list tbody td{
            border-bottom: 1px solid #e6e6e6;
            padding: 3.2px 2px;
            font-size: 8px;
        }
        .col-path{ width:54%; font-style: italic; color:#2b2b2b; }
        .col-res{ width:23%; text-align:center; }
        .col-ref{ width:23%; text-align:right; color:#555; }

        .res-pos{ font-weight:700; color: #c00; }
        .res-neg{ color:#666; }

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
    // Helpers simples
    $val = fn($v, $d='—') => ($v === null || $v === '') ? $d : $v;

    $norm = function($v){
        $v = strtoupper(trim((string)$v));
        if ($v === 'DETECTADO') return 'DETECTADO';
        if ($v === 'NO DETECTADO' || $v === 'NO_DETECTADO' || $v === 'NEGATIVO' || $v === '') return 'NO DETECTADO';
        return $v ?: 'NO DETECTADO';
    };

    $isPos = fn($v) => $norm($v) === 'DETECTADO';

    $areaTitulo = 'BIOLOGÍA MOLECULAR';
    $metodo = 'QPCR - RT EN TIEMPO REAL';

    $tipoMuestra = $solicitud->muestra_identificacion ?? 'HISOPADO COMBINADO';

    // filas (como la imagen)
    $rows = [
        ['Virus Sincitial Respiratorio A, B', 'vrs_ab'],
        ['Influenza B', 'influenza_b'],
        ['Influenza A', 'influenza_a'],
        ['SARS CoV-2', 'sars_cov_2'],
        ['Streptococcus pyogenes', 'streptococcus_pyogenes'],
        ['Adenovirus', 'adenovirus'],
        ['Rhinovirus', 'rhinovirus'],
        ['Coronavirus 229E/OC43', 'coronavirus_229e_oc43'],
        ['Parainfluenza 1,2', 'parainfluenza_1_2'],
        ['Coronavirus NL63/HKU1', 'coronavirus_nl63_hku1'],
        ['Parainfluenza 3,4', 'parainfluenza_3_4'],
        ['Haemophilus influenzae', 'haemophilus_influenzae'],
        ['Bordetella pertussis', 'bordetella_pertussis'],
        ['Streptococcus pneumoniae', 'streptococcus_pneumoniae'],
        ['Bocavirus', 'bocavirus'],
        ['Mycoplasma pneumoniae', 'mycoplasma_pneumoniae'],
        ['Metapneumovirus', 'metapneumovirus'],
        ['Enterovirus', 'enterovirus'],
        ['Legionella pneumophila', 'legionella_pneumophila'],
    ];
@endphp

<div class="sheet clearfix">

    @foreach(['left', 'right'] as $side)
        <div class="half half-{{ $side }}" style="margin: 10px 6px;">

            <!-- ===== HEADER ===== -->
            <table class="no-border">
                <tr>
                    <td style="width:15%;">
                        <img src="{{ public_path('img/logo-hospital.png') }}" style="width:58px;">
                    </td>

                    <td>
                        <div class="title">HOSPITAL GENERAL SAN JUAN DE DIOS - BLOQUE CENTRAL</div>
                        <div class="subtitle muted">SERVICIO DE LABORATORIO DE ANÁLISIS CLÍNICO - MICROBIOLÓGICO</div>
                        <div class="subtitle muted bold">ÁREA: {{ $areaTitulo }}</div>
                        <div class="subtitle muted small">Dirección: San Felipe entre 6 de Octubre y Tarija</div>
                        <div class="subtitle muted small">REGISTRO CONALAB: 001 &nbsp;&nbsp; REGISTRO CODELAB: 000004</div>
                    </td>

                    <td style="width:15%;" class="right">
                        <img src="{{ public_path('img/logo-labo.png') }}" style="width:58px;">
                    </td>
                </tr>
            </table>

            <div class="hr"></div>

            <!-- ===== FORM (igual Hematología) ===== -->
            <table class="form-grid" style="margin-top:2px;">
                <tr>
                    <td style="width:18%"><span class="label">CÓDIGO:</span></td>
                    <td style="width:32%"><div class="line clip">{{ $val($solicitud->codigo ?? $solicitud->id) }}</div></td>
                    <td style="width:20%"><span class="label">NRO. REGISTRO:</span></td>
                    <td style="width:30%"><div class="line clip">{{ $val($solicitud->nro_registro, '-') }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">PACIENTE:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->paciente_nombre, '-') }}</div></td>
                    <td><span class="label">EDAD:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->paciente_edad, '-') }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">MEDICO SOL.:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->doctor_nombre, '-') }}</div></td>
                    <td><span class="label">SEXO:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->paciente_genero, '-') }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">FECHA SOL. MEDICO:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->fecha_solicitud, '-') }}</div></td>
                    <td><span class="label">TIPO MUESTRA:</span></td>
                    <td><div class="line clip">{{ $val($tipoMuestra) }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">EST. DE SALUD:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->establecimiento_salud, '-') }}</div></td>
                    <td><span class="label">CI:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->paciente_ci, '-') }}</div></td>
                </tr>
            </table>

            <!-- ===== TITULO ===== -->
            <div class="center" style="margin-top:6px; font-weight:700; font-size:9px;">
                RESULTADOS PANEL RESPIRATORIO POR PCR
            </div>

            <div class="center muted small" style="margin-top:1px;">
                Método: {{ $metodo }}
            </div>

            <!-- ===== TABLA RESULTADOS (lista como imagen) ===== -->
            <div style="margin-top:6px;">
                <table class="list">
                    <thead>
                    <tr>
                        <th class="col-path" style="text-align:left;">PATÓGENO</th>
                        <th class="col-res center">RESULTADO</th>
                        <th class="col-ref right">VALORES DE REFERENCIA</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($rows as $r)
                        @php
                            $label = $r[0];
                            $key = $r[1];
                            $value = $norm($panel?->{$key} ?? 'NO DETECTADO');
                        @endphp
                        <tr>
                            <td class="col-path">{{ $label }}</td>
                            <td class="col-res {{ $isPos($value) ? 'res-pos' : 'res-neg' }}">{{ $value }}</td>
                            <td class="col-ref">NO DETECTADO</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- ===== INTERPRETACIÓN ===== -->
            <div class="small" style="margin-top:8px;">
                <span class="bold">* Interpretación de resultados:</span>
                NO DETECTADO (NEGATIVO); DETECTADO (POSITIVO)
            </div>

            <!-- ===== OBS ===== -->
            <div style="margin-top:6px;">
                <span class="bold small">OBSERVACIONES:</span>
                <div class="box" style="min-height:24px;">{{ $panel->observaciones ?? '' }}</div>
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
                    <td class="center" style="width:34%;">
                        @if(!empty($qrSvgBase64))
                            <img
                                src="data:image/svg+xml;base64,{{ $qrSvgBase64 }}"
                                style="width:50px; height:50px;"
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
