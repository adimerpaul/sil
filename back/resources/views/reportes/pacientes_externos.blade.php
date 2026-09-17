<!doctype html>
<html lang="es">
<head>
<meta charset="utf-8">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }
body { font-family: DejaVu Sans, sans-serif; font-size: 8px; color: #111; }
table { width: 100%; border-collapse: collapse; page-break-inside: auto; }
th { padding: 3px 4px; border: 1px solid #bf360c; background: #e65100; color: #fff; font-size: 8px; }
td { padding: 2px 4px; border: 1px solid #ccc; }
.c { text-align: center; }
.r { text-align: right; }
.sm { font-size: 7px; }
.total td { background: #e65100; color: #fff; font-weight: 700; border-color: #bf360c; }
.grupo td { background: #fff3e0; font-weight: 700; color: #bf360c; border-color: #ffcc80; }
</style>
</head>
<body style="padding: 15px;">

<p style="font-size:9px; font-weight:700;">HOSPITAL GENERAL SAN JUAN DE DIOS</p>
<p style="font-size:13px; font-weight:700; margin-bottom:2px;">
    Reporte de Pacientes Externos
</p>
<p style="font-size:8px; color:#555; margin-bottom:8px;">
    Rango: <b>{{ $dateFrom }}</b> &mdash; <b>{{ $dateTo }}</b>
    &nbsp;|&nbsp; Pacientes externos: <b>{{ $rows->count() }}</b>
    &nbsp;|&nbsp; Establecimientos: {{ $grupos->count() }}
    &nbsp;|&nbsp; Total Bs: <b>{{ number_format($totalMonto, 2) }}</b>
</p>

{{-- Resumen por establecimiento --}}
<p style="font-size:10px; font-weight:700; margin-bottom:3px;">Resumen por establecimiento de salud</p>
<table style="width:65%; margin-bottom:12px;">
<thead>
<tr>
    <th class="c">#</th>
    <th style="text-align:left;">Establecimiento de salud</th>
    <th class="c">Solicitudes</th>
    <th class="c">%</th>
    <th class="r">Total Bs</th>
</tr>
</thead>
<tbody>
@foreach($grupos->values() as $i => $g)
<tr style="background:{{ $i % 2 === 0 ? '#ffffff' : '#f5f5f5' }};">
    <td class="c">{{ $i + 1 }}</td>
    <td>{{ $g['establecimiento'] }}</td>
    <td class="c">{{ $g['cantidad'] }}</td>
    <td class="c">{{ $g['porcentaje'] }}</td>
    <td class="r">{{ number_format($g['monto'], 2) }}</td>
</tr>
@endforeach
</tbody>
<tfoot>
<tr class="total">
    <td colspan="2" class="r">TOTAL</td>
    <td class="c">{{ $rows->count() }}</td>
    <td class="c">{{ $rows->count() ? 100 : 0 }}</td>
    <td class="r">{{ number_format($totalMonto, 2) }}</td>
</tr>
</tfoot>
</table>

{{-- Detalle agrupado por establecimiento --}}
<p style="font-size:10px; font-weight:700; margin-bottom:3px;">Detalle de solicitudes</p>
<table>
<thead>
<tr>
    <th class="c">#</th>
    <th>Código</th>
    <th class="c">Fecha</th>
    <th class="c">Tipo</th>
    <th>Paciente</th>
    <th class="c">CI</th>
    <th class="c">Edad</th>
    <th class="c">Gén.</th>
    <th>Programa</th>
    <th>Prestaciones</th>
    <th>Servicios</th>
    <th class="r">Total Bs</th>
    <th>Estado</th>
</tr>
</thead>
<tbody>
@php $n = 0; @endphp
@foreach($rows->groupBy('establecimiento_nombre') as $establecimiento => $items)
<tr class="grupo" style="page-break-inside:avoid;">
    <td colspan="11">{{ $establecimiento }} ({{ $items->count() }})</td>
    <td class="r">{{ number_format($items->sum(fn ($r) => (float) $r->total_monto), 2) }}</td>
    <td></td>
</tr>
@foreach($items as $r)
@php $n++; @endphp
<tr style="background:{{ $n % 2 === 0 ? '#f5f5f5' : '#ffffff' }}; page-break-inside:avoid;">
    <td class="c">{{ $n }}</td>
    <td>{{ $r->codigo_solicitud ?? $r->id }}</td>
    <td class="c">{{ $r->fecha_solicitud }}</td>
    <td class="c">{{ $r->tipo_atencion === 'SI' ? 'SUS' : 'EXT' }}</td>
    <td>{{ $r->paciente_nombre }}</td>
    <td class="c">{{ $r->paciente_ci }}</td>
    <td class="c">{{ $r->paciente_edad }}</td>
    <td class="c" style="color:{{ $r->paciente_genero === 'M' ? '#1565C0' : '#AD1457' }}; font-weight:600;">{{ $r->paciente_genero ?: '-' }}</td>
    <td class="sm">{{ $r->tipo_paciente_externo ?: '-' }}</td>
    <td class="sm">{{ $r->areas_nombres ?: '-' }}</td>
    <td class="sm">{{ $r->servicios_nombres ?: '-' }}</td>
    <td class="r">{{ number_format((float) $r->total_monto, 2) }}</td>
    <td>{{ $r->estado }}</td>
</tr>
@endforeach
@endforeach
</tbody>
<tfoot>
<tr class="total">
    <td colspan="11" class="r">TOTAL</td>
    <td class="r">{{ number_format($totalMonto, 2) }}</td>
    <td></td>
</tr>
</tfoot>
</table>

</body>
</html>
