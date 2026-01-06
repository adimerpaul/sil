<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        *{ box-sizing:border-box; }
        body{ font-family: DejaVu Sans, sans-serif; font-size: 8px; color:#111; margin:0; }
        .center{ text-align:center; }
        .bold{ font-weight:700; }
        .tbl{ width:100%; border-collapse:collapse; }
        .tbl th,.tbl td{ border:1px solid #111; padding:2px 4px; }
        .tbl th{ background:#f3f3f3; }
        .mt-8{ margin-top:8px; }
        .title{ font-size:12px; font-weight:700; margin:4px 0; }
        .chart-wrap{ text-align:center; }
        .chart-img{ width:92%; height:auto; }
    </style>
</head>

<body>
{!! view('components.header', ['solicitud' => $solicitud])->render() !!}

<div class="center title">CITOQUIMICO</div>
<div class="bold center">MUESTRA: LIQUIDO ASCITICO</div>

<div class="mt bold">EXAMEN FISICO</div>
<table>
    @if($quimica->citoquimico_cantidad)
        <tr>
            <td class="col-label">CANTIDAD:</td>
            <td class="col-val">{{ $quimica->citoquimico_cantidad }} ml</td>
        </tr>
    @endif

    @if($quimica->citoquimico_color)
        <tr>
            <td>COLOR:</td>
            <td>{{ $quimica->citoquimico_color }}</td>
        </tr>
    @endif

    @if($quimica->citoquimico_aspecto)
        <tr>
            <td>ASPECTO:</td>
            <td>{{ $quimica->citoquimico_aspecto }}</td>
        </tr>
    @endif
</table>

<div class="mt bold">EXAMEN QUIMICO</div>
<table>
    @if($quimica->citoquimico_glucosa)
        <tr>
            <td>GLUCOSA:</td>
            <td>{{ $quimica->citoquimico_glucosa }} mg/dL</td>
        </tr>
    @endif

    @if($quimica->citoquimico_ph)
        <tr>
            <td>PH:</td>
            <td>{{ $quimica->citoquimico_ph }}</td>
        </tr>
    @endif

    @if($quimica->citoquimico_proteinas_totales)
        <tr>
            <td>PROTEINAS TOTALES:</td>
            <td>{{ $quimica->citoquimico_proteinas_totales }} g/dL</td>
        </tr>
    @endif

    @if($quimica->citoquimico_densidad)
        <tr>
            <td>DENSIDAD:</td>
            <td>{{ $quimica->citoquimico_densidad }}</td>
        </tr>
    @endif

    @if($quimica->citoquimico_albumina)
        <tr>
            <td>ALBUMINA:</td>
            <td>{{ $quimica->citoquimico_albumina }} g/dL</td>
        </tr>
    @endif

    @if($quimica->citoquimico_ldh)
        <tr>
            <td>LDH:</td>
            <td>{{ $quimica->citoquimico_ldh }} U/L</td>
        </tr>
    @endif
</table>

<div class="mt bold">EXAMEN MICROSCOPICO</div>
<table>
    @if($quimica->citoquimico_globulos_blancos)
        <tr>
            <td>GLOBULOS BLANCOS:</td>
            <td>{{ $quimica->citoquimico_globulos_blancos }} x mm3</td>
        </tr>
    @endif
</table>

<div class="mt bold">RECUENTO DIFERENCIAL</div>
<table>
    @if($quimica->citoquimico_polimorfonucleares)
        <tr>
            <td>POLIMORFONUCLEARES:</td>
            <td>{{ $quimica->citoquimico_polimorfonucleares }} %</td>
        </tr>
    @endif

    @if($quimica->citoquimico_mononucleares)
        <tr>
            <td>MONONUCLEARES:</td>
            <td>{{ $quimica->citoquimico_mononucleares }} %</td>
        </tr>
    @endif
</table>


</body>
</html>
