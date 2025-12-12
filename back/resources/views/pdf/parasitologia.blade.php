<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: letter landscape; margin: 10px 12px; }
        * { box-sizing: border-box; }
        body{ margin:0; padding:0; font-family: DejaVu Sans, sans-serif; font-size: 10px; color:#111; line-height: 1; }

        .sheet{ width:100%; overflow:hidden; }
        .half{ width:48%; float:left; overflow:hidden; padding:0; }
        .half-left{ transform: scale(1.02); transform-origin: top left; padding-right: 6px; }
        .half-right{ transform: scale(1.02); transform-origin: top left; padding-left: 6px; }

        .title { font-weight:700; font-size: 10.2px; text-align:center; }
        .subtitle { font-size: 8px; text-align:center; margin-top: 1px; }
        .muted { color:#555; }
        .small { font-size: 7.6px; }

        .hr { border-top: 1.8px solid #111; margin: 2px 0; }
        .box { border: 1px solid #111; padding: 3px 4px; }
        .center { text-align:center; }
        .right { text-align:right; }

        table{ width:100%; border-collapse: collapse; table-layout: fixed; }
        th, td{ border:1px solid #111; padding: 1.8px 3px; vertical-align: middle; }
        th{ background:#f2f2f2; font-weight:700; font-size: 7.8px; }
        .no-border td, .no-border th{ border:none; padding:0; }

        .form-grid td{ border:none; padding: 2px 3px 2px 0; vertical-align: bottom; font-size: 7.6px; }
        .label{ font-weight:700; }
        .line{ border-bottom: 1px solid #111; height: 12px; padding: 0 3px; font-size: 7.7px; }

        .section{ margin-top: 4px; }
        .section h3{ margin: 0 0 2px; font-size: 8.2px; text-transform: uppercase; letter-spacing: .2px; }

        .clearfix::after{ content:""; display:block; clear:both; }
        img{ max-width: 100%; }
        .clip{ overflow:hidden; text-overflow: ellipsis; white-space: nowrap; }
    </style>
</head>

<body>
@php
    $p = $parasitologia;
    $tipo = $p->tipo ?? 'SIMPLE';
@endphp

<div class="sheet clearfix">
    @foreach(['left','right'] as $side)
        <div class="half half-{{ $side }}" style="margin: 10px 6px;">

            <!-- HEADER -->
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

            <!-- DATOS -->
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
                    <td><div class="line clip">{{ $solicitud->muestra_identificacion ?? 'HECES FECALES' }}</div></td>
                </tr>

                <tr>
                    <td><span class="label">EST. DE SALUD:</span></td>
                    <td><div class="line clip">{{ $solicitud->establecimiento_salud ?? '-' }}</div></td>
                    <td><span class="label">CI:</span></td>
                    <td><div class="line clip">{{ $solicitud->paciente_ci ?? '-' }}</div></td>
                </tr>
            </table>

            <div class="section center" style="margin-top:4px; font-weight:700; font-size:9px;">
                PARASITOLOGÍA
            </div>

            <div class="center muted small" style="margin-top:1px;">
                Tipo: <b>{{ $tipo }}</b>
            </div>

            <!-- MACROSCOPÍA -->
            <div class="section">
                <h3>Macroscopía</h3>
                <table>
                    <thead>
                    <tr>
                        <th style="width:22%;">OLOR</th>
                        <th style="width:22%;">COLOR</th>
                        <th style="width:22%;">CONSISTENCIA</th>
                        <th style="width:34%;">BACTERIAS</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="center">{{ $p->olor ?? '' }}</td>
                        <td class="center">{{ $p->color ?? '' }}</td>
                        <td class="center">{{ $p->consistencia ?? '' }}</td>
                        <td class="center">{{ $p->bacterias ?? '' }}</td>
                    </tr>
                    </tbody>
                </table>

                <div class="section">
                    <h3>Otros</h3>
                    <div class="box" style="min-height:20px;">{{ $p->otros ?? '' }}</div>
                </div>
            </div>

            <!-- COPROPARASITOLÓGICO -->
            <div class="section">
                <h3>Coproparasitológico</h3>

                @if($tipo === 'SERIADO')
                    <table>
                        <thead>
                        <tr>
                            <th style="width:33%;">DESCRIPCIÓN MUESTRA 1</th>
                            <th style="width:33%;">DESCRIPCIÓN MUESTRA 2</th>
                            <th style="width:34%;">DESCRIPCIÓN MUESTRA 3</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td>{{ $p->descripcion_muestra_1 ?? '' }}</td>
                            <td>{{ $p->descripcion_muestra_2 ?? '' }}</td>
                            <td>{{ $p->descripcion_muestra_3 ?? '' }}</td>
                        </tr>
                        </tbody>
                    </table>
                @else
                    <table>
                        <thead>
                        <tr>
                            <th>DESCRIPCIÓN MUESTRA</th>
                        </tr>
                        </thead>
                        <tbody>
                        <tr>
                            <td style="height:26px;">{{ $p->descripcion_muestra ?? '' }}</td>
                        </tr>
                        </tbody>
                    </table>
                @endif

                <table style="margin-top:4px;">
                    <thead>
                    <tr>
                        <th style="width:20%;">SANGRE OCULTA</th>
                        <th style="width:26%;">PRUEBA RÁPIDA ROTAVIRUS</th>
                        <th style="width:18%;">MOCO FECAL</th>
                        <th style="width:18%;">TEST BENEDICT</th>
                        <th style="width:18%;">REACCIÓN</th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr>
                        <td class="center">{{ $p->sangre_oculta ?? '' }}</td>
                        <td class="center">{{ $p->prueba_rapida_rotavirus ?? '' }}</td>
                        <td class="center">{{ $p->moco_fecal ?? '' }}</td>
                        <td class="center">{{ $p->test_benedict ?? '' }}</td>
                        <td class="center">{{ $p->reaccion ?? '' }}</td>
                    </tr>
                    </tbody>
                </table>

                <div class="section">
                    <h3>Otros exámenes</h3>
                    <div class="box" style="min-height:22px;">{{ $p->otros_examenes ?? '' }}</div>
                </div>
            </div>

            <!-- FIRMAS -->
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
