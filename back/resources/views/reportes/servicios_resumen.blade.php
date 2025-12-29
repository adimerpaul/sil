<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { margin: 14px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #111; }
        .title { font-size: 14px; font-weight: 700; }
        .muted { color: #555; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #333; padding: 6px; vertical-align: top; }
        th { background: #f2f2f2; text-align: left; }
        .right { text-align: right; }
        .small { font-size: 9px; }
    </style>
</head>
<body>
<div class="title">Reporte de Servicios</div>
<div class="muted small">
    Rango: {{ $dateFrom }} a {{ $dateTo }}
    @if($userId) | Usuario ID: {{ $userId }} @endif
</div>

<table>
    <thead>
    <tr>
        <th>Servicio</th>
        <th>Área</th>
        <th class="right">Cantidad</th>
        <th class="right">Solicitudes</th>
        <th class="right">Precio prom.</th>
        <th class="right">Total Bs</th>
    </tr>
    </thead>
    <tbody>
    @php $sum = 0; @endphp
    @foreach($rows as $r)
        @php $sum += (float)$r->total_monto; @endphp
        <tr>
            <td>{{ $r->servicio_nombre }}</td>
            <td>{{ $r->area_nombre }}</td>
            <td class="right">{{ (int)$r->cantidad }}</td>
            <td class="right">{{ (int)$r->solicitudes }}</td>
            <td class="right">{{ number_format((float)$r->precio_promedio, 2) }}</td>
            <td class="right">{{ number_format((float)$r->total_monto, 2) }}</td>
        </tr>
    @endforeach
    </tbody>
    <tfoot>
    <tr>
        <th colspan="5" class="right">TOTAL</th>
        <th class="right">{{ number_format($sum, 2) }}</th>
    </tr>
    </tfoot>
</table>
</body>
</html>
