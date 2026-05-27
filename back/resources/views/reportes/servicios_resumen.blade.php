<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 12px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 8.5px; color: #111; }
        .title { font-size: 13px; font-weight: 700; margin-bottom: 2px; }
        .sub { font-size: 8px; color: #555; margin-bottom: 6px; }
        .kpis { display: flex; gap: 18px; margin-bottom: 8px; }
        .kpi { background: #f5f5f5; border: 1px solid #ddd; border-radius: 4px; padding: 4px 10px; }
        .kpi-label { font-size: 7.5px; color: #666; }
        .kpi-val { font-size: 12px; font-weight: 700; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #bbb; padding: 3px 5px; vertical-align: middle; }
        th { background: #e2e2e2; font-weight: 700; font-size: 8px; }
        .right { text-align: right; }
        .center { text-align: center; }
        .m { color: #1565C0; font-weight: 600; }
        .f { color: #AD1457; font-weight: 600; }
        tfoot th { background: #ccc; }
        .sml { font-size: 7.5px; }
    </style>
</head>
<body>

<div class="title">Reporte de Solicitudes por Servicios / Prestaciones</div>
<div class="sub">
    Rango: <strong>{{ $dateFrom }}</strong> — <strong>{{ $dateTo }}</strong>
    &nbsp;|&nbsp; Mostrando: {{ count($rows) }} de {{ $totalCount }} solicitudes
    &nbsp;|&nbsp; Embarazadas: {{ $embarazadas }}
</div>

<table>
    <thead>
    <tr>
        <th class="center">#</th>
        <th>Código</th>
        <th class="center">Fecha</th>
        <th>Paciente</th>
        <th class="center">CI</th>
        <th class="center">Edad</th>
        <th class="center">Gén.</th>
        <th class="center">Emb.</th>
        <th class="center">Cama</th>
        <th>Prestaciones</th>
        <th>Servicios</th>
        <th class="right">Total Bs</th>
        <th>Estado</th>
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $i => $r)
        <tr>
            <td class="center">{{ $i + 1 }}</td>
            <td>{{ $r->codigo_solicitud ?? $r->id }}</td>
            <td class="center">{{ $r->fecha_solicitud }}</td>
            <td>{{ $r->paciente_nombre }}</td>
            <td class="center">{{ $r->paciente_ci }}</td>
            <td class="center">{{ $r->paciente_edad }}</td>
            <td class="center {{ $r->paciente_genero === 'M' ? 'm' : 'f' }}">{{ $r->paciente_genero ?: '—' }}</td>
            <td class="center">{{ $r->paciente_embarazo ? '✓' : '' }}</td>
            <td class="center">{{ $r->cama ?: '—' }}</td>
            <td class="sml">{{ $r->areas_nombres ?: '—' }}</td>
            <td class="sml">{{ $r->servicios_nombres ?: '—' }}</td>
            <td class="right">{{ number_format((float) $r->total_monto, 2) }}</td>
            <td>{{ $r->estado }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th colspan="11" class="right">TOTAL</th>
        <th class="right">{{ number_format($totalMonto, 2) }} Bs</th>
        <th></th>
    </tr>
    </tfoot>
</table>

</body>
</html>
