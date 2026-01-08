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

        .sheet{ width:100%; overflow:hidden; }
        .half{ width:48%; float:left; overflow:hidden; padding:0; }

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

        .title { font-weight:700; font-size: 10.2px; text-align:center; }
        .subtitle { font-size: 8px; text-align:center; margin-top: 1px; }
        .muted { color:#555; }

        .hr { border-top: 1.8px solid #111; margin: 2px 0; }
        .box { border: 1px solid #111; padding: 3px 4px; }
        .small { font-size: 7.6px; }
        .center { text-align:center; }
        .right { text-align:right; }

        table{ width:100%; border-collapse: collapse; table-layout: fixed; }
        th, td{ border:1px solid #111; padding: 1.8px 3px; vertical-align: middle; }
        th{ background:#f2f2f2; font-weight:700; font-size: 7.8px; }
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

        .res-pos{ font-weight:700;color: RED; }
        .res-neg{ color:#555; }

        .clearfix::after{
            content:"";
            display:block;
            clear:both;
        }
    </style>
</head>

<body>

@php
    $norm = function($v){
        $v = strtoupper(trim((string)$v));
        return $v === 'DETECTADO' ? 'DETECTADO' : 'NO DETECTADO';
    };

    $rows = [
        ['Chlamydia trachomatis', 'chlamydia_trachomatis'],
        ['Mycoplasma genitalium', 'mycoplasma_genitalium'],
        ['Neisseria gonorrhoeae', 'neisseria_gonorrhoeae'],
        ['Trichomonas vaginalis', 'trichomonas_vaginalis'],
        ['Ureaplasma urealyticum', 'ureaplasma_urealyticum'],
        ['Ureaplasma parvum', 'ureaplasma_parvum'],
        ['Mycoplasma hominis', 'mycoplasma_hominis'],
        ['HSV-1', 'hsv_1'],
        ['HSV-2', 'hsv_2'],
        ['Treponema pallidum', 'treponema_pallidum'],
        ['Candida albicans', 'candida_albicans'],
        ['Gardnerella vaginalis', 'gardnerella_vaginalis'],
    ];
@endphp

<div class="sheet clearfix">

    @foreach(['left','right'] as $side)
        <div class="half half-{{ $side }}" style="margin:10px 6px;">

            {!! view('components.header', ['solicitud' => $solicitud])->render() !!}
            <!-- TITULO -->
            <div class="center" style="margin-top:6px; font-weight:700; font-size:9px;">
                PANEL INFECCIONES DE TRANSMISIÓN SEXUAL (ITS) POR PCR
            </div>

            <div class="center muted small" style="margin-top:1px;">
                Método: Reacción en Cadena de la Polimerasa en Tiempo Real
            </div>

            <!-- TABLA RESULTADOS (MISMO ESTILO HEMATOLOGÍA) -->
            <div style="margin-top:6px;">
                <table>
                    <thead>
                    <tr>
                        <th style="width:45%">PRUEBA</th>
                        <th style="width:25%" class="center">RESULTADO</th>
                        <th style="width:30%" class="center">VALORES DE REFERENCIA</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($rows as $r)
                        @php
                            $value = $norm($panel?->{$r[1]} ?? null);
                        @endphp
                        <tr>
                            <td>{{ $r[0] }}</td>
                            <td class="center {{ $value === 'DETECTADO' ? 'res-pos' : 'res-neg' }}">
                                {{ $value }}
                            </td>
                            <td class="center">NO DETECTADO</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <div class="small" style="margin-top:6px;">
                <b>*Interpretación:</b> NO DETECTADO (NEGATIVO) / DETECTADO (POSITIVO)
            </div>

            <div style="margin-top:6px;">
                <b class="small">OBSERVACIONES:</b>
                <div class="box" style="min-height:24px;">{{ $panel->observaciones ?? '' }}</div>
            </div>

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
                    <td class="center" style="width:33%;">
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
