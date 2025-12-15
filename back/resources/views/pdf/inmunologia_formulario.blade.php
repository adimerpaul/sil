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

        .hr { border-top: 1.8px solid #111; margin: 2px 0; }
        .small { font-size: 7.6px; }
        .center { text-align:center; }
        .right { text-align:right; }
        .bold{ font-weight:700; }
        .clip{ overflow:hidden; text-overflow: ellipsis; white-space: nowrap; }

        table{ width:100%; border-collapse: collapse; table-layout: fixed; }
        .no-border td, .no-border th{ border:none; padding:0; }

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

        /* Contenido HTML “impreso” */
        .content{
            margin-top: 6px;
            border: 1px solid #111;
            padding: 6px;
            min-height: 210px;
        }
        .content table{ width:100%; border-collapse: collapse; }
        .content td, .content th{ border:1px solid #111; padding: 3px 4px; font-size: 9px; }
        .content h1,.content h2,.content h3{ margin: 4px 0; }
        .content p{ margin: 3px 0; }
        .clearfix::after{ content:""; display:block; clear:both; }
    </style>
</head>
<body>

@php
    $val = fn($v, $d='—') => ($v === null || $v === '') ? $d : $v;
@endphp

<div class="sheet clearfix">
    @foreach(['left','right'] as $side)
        <div class="half half-{{ $side }}" style="margin: 10px 6px;">

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
                    <td><div class="line clip">{{ $val($solicitud->muestra_identificacion, '-') }}</div></td>
                </tr>
                <tr>
                    <td><span class="label">EST. DE SALUD:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->establecimiento_salud, '-') }}</div></td>
                    <td><span class="label">CI:</span></td>
                    <td><div class="line clip">{{ $val($solicitud->paciente_ci, '-') }}</div></td>
                </tr>
            </table>

            <div class="center" style="margin-top:6px; font-weight:700; font-size:10px;">
                INMUNOLOGÍA · {{ $row->nombre ?? 'FORMULARIO' }}
            </div>

            <div class="content">
                {!! $row->html !!}
            </div>

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
