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
            line-height: 1.15;
        }

        /* 2 columnas */
        .sheet{ width:100%; overflow:hidden; }
        .half{ width:48%; float:left; overflow:hidden; padding:0; }

        /* Ajuste fino */
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
        .bold { font-weight:700; }

        /* Separadores / cajas */
        .hr { border-top: 1.8px solid #111; margin: 2px 0; }
        .box { border: 1px solid #111; padding: 3px 4px; }

        /* Tablas */
        table{ width:100%; border-collapse: collapse; table-layout: fixed; }
        th, td{ border:1px solid #111; padding: 2px 3px; vertical-align: middle; }
        th{ background:#f2f2f2; font-weight:700; font-size: 7.8px; }
        .no-border td, .no-border th{ border:none; padding:0; }
        .clip{ overflow:hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* Form grid (cabecera datos) */
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

        /* Secciones */
        .section{ margin-top: 4px; }
        .section h3{
            margin: 0 0 2px;
            font-size: 8.2px;
            text-transform: uppercase;
            letter-spacing: .2px;
        }

        /* Resultado */
        .res-ok{ font-weight:700; }
        .res-pos{ font-weight:700; color:#c10015; } /* DETECTADO */
        .note{
            border: 1px dashed #444;
            padding: 3px 4px;
            font-size: 7.4px;
            color:#333;
        }

        /* Footer */
        .footer{
            margin-top: 6px;
            font-size: 7px;
            color:#444;
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
    // Helpers simples (evitar null)
    $val = fn($v, $d='—') => ($v === null || $v === '') ? $d : $v;

    $norm = function($v){
        $v = strtoupper(trim((string)$v));
        if ($v === 'DETECTADO') return 'DETECTADO';
        if ($v === 'NO DETECTADO' || $v === 'NO_DETECTADO' || $v === 'NEGATIVO') return 'NO DETECTADO';
        return $v ?: '—';
    };

    $classRes = function($v) use ($norm){
        return $norm($v) === 'DETECTADO' ? 'res-pos' : 'res-ok';
    };

    // Defaults estilo reporte
    $tipoMuestra = $solicitud->muestra_identificacion ?? 'HISOPADO CERVICAL';
    $metodo = 'PCR EN TIEMPO REAL';
    $areaTitulo = 'BIOLOGÍA MOLECULAR';
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
                        <div class="title">HOSPITAL GENERAL "SAN JUAN DE DIOS" - BLOQUE CENTRAL</div>
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

            <!-- ===== DATOS CABECERA ===== -->
            <table class="form-grid" style="margin-top:2px;">
                <tr>
                    <td style="width:22%"><span class="label">LABORATORIO:</span></td>
                    <td style="width:28%"><div class="line clip">{{ $val($solicitud->establecimiento_salud, 'HOSP. GENERAL S.J.D.D. (ORURO)') }}</div></td>

                    <td style="width:22%"><span class="label">FECHA RESULTADO:</span></td>
                    <td style="width:28%"><div class="line clip">{{ $val($solicitud->fecha_finalizacion ?? $solicitud->fecha_envio_analitica ?? $solicitud->fecha_solicitud) }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">PACIENTE:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->paciente_nombre) }}</div></td>

                    <td><span class="label">EDAD:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->paciente_edad) }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">SEXO:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->paciente_genero) }}</div></td>

                    <td><span class="label">SUS/EXT:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->tipo_atencion, 'SUS') }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">CÓDIGO:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->codigo ?? $solicitud->id) }}</div></td>

                    <td><span class="label">NRO. REGISTRO:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->nro_registro) }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">TIPO MUESTRA:</span></td>
                    <td><div class="line clip">{{ $val($tipoMuestra) }}</div></td>

                    <td><span class="label">FECHA RECEPCIÓN:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->fecha_pre_analitica ?? $solicitud->fecha_creacion ?? $solicitud->fecha_solicitud) }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">MÉTODO:</span></td>
                    <td><div class="line clip">{{ $metodo }}</div></td>

                    <td><span class="label">EST. SALUD:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->establecimiento_salud) }}</div></td>
                </tr>
            </table>

            <!-- ===== TÍTULO ESTUDIO ===== -->
            <div class="section center" style="margin-top:6px; font-weight:700; font-size:9px;">
                RESULTADOS DE VIRUS DE PAPILOMA HUMANO POR PCR
            </div>

            <!-- ===== TABLA RESULTADOS ===== -->
            <div class="section" style="margin-top:4px;">
                <table>
                    <thead>
                    <tr>
                        <th style="width:52%;">DETERMINACIÓN</th>
                        <th style="width:24%;" class="center">RESULTADO</th>
                        <th style="width:24%;" class="center">VALORES DE REFERENCIA</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td>Determinación de HPV de alto riesgo*</td>
                        <td class="center {{ $classRes($papiloma->hpv_alto_riesgo ?? null) }}">{{ $norm($papiloma->hpv_alto_riesgo ?? 'NO DETECTADO') }}</td>
                        <td class="center">NO DETECTADO</td>
                    </tr>
                    <tr>
                        <td>Determinación de HPV 16</td>
                        <td class="center {{ $classRes($papiloma->hpv_16 ?? null) }}">{{ $norm($papiloma->hpv_16 ?? 'NO DETECTADO') }}</td>
                        <td class="center">NO DETECTADO</td>
                    </tr>
                    <tr>
                        <td>Determinación de HPV 18</td>
                        <td class="center {{ $classRes($papiloma->hpv_18 ?? null) }}">{{ $norm($papiloma->hpv_18 ?? 'NO DETECTADO') }}</td>
                        <td class="center">NO DETECTADO</td>
                    </tr>
                    <tr>
                        <td>Determinación de HPV 45</td>
                        <td class="center {{ $classRes($papiloma->hpv_45 ?? null) }}">{{ $norm($papiloma->hpv_45 ?? 'NO DETECTADO') }}</td>
                        <td class="center">NO DETECTADO</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <!-- ===== INTERPRETACIÓN ===== -->
            <div class="section">
                <div class="note">
                    <span class="bold">*Interpretación de resultados:</span>
                    <span class="bold">NO DETECTADO</span> (NEGATIVO) &nbsp;;&nbsp;
                    <span class="bold">DETECTADO</span> (POSITIVO)
                </div>
            </div>

            <!-- ===== MÉTODO / GENOTIPOS ===== -->
            <div class="section">
                <div class="small" style="margin-top:3px;">
                    <span class="bold">MÉTODO:</span>
                    Amplificación de ácidos nucleicos (ADN) por PCR en tiempo real.
                </div>
                <div class="small" style="margin-top:2px;">
                    Esta metodología permite la determinación específica de 24 tipos HPV de alto riesgo:
                    26, 30, 31, 33, 34, 35, 39, 51, 52, 53, 56, 58, 59, 66, 67, 68, 69, 70, 73, 82, 97
                    y permite la diferenciación del HPV 16, 18 y 45 en muestras obtenidas de cuello uterino.
                </div>
            </div>

            <!-- ===== OBSERVACIONES ===== -->
            <div class="section">
                <h3>Observaciones</h3>
                <div class="box" style="min-height:28px;">
                    {{ $papiloma->observaciones ?? '' }}
                </div>
            </div>

            <!-- ===== FIRMAS ===== -->
            <table class="no-border" style="margin-top:8px;">
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
                                style="width:80px; height:80px;"
                                alt="QR"
                            >
                        @endif
                    </td>
                    <td class="center" style="width:34%;">
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

            <!-- ===== FOOTER ===== -->
            <div class="footer">
                EMITIDO POR: ORURO, RED URBANA, MUNICIPIO ORURO, LABORATORIO HOSP. GENERAL S.J.D.D. (ORURO)
            </div>

        </div>
    @endforeach

</div>

</body>
</html>
