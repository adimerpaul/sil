<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>{{ $title }}</title>
    <style>
        @page {
            margin: 24px 28px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            color: #172033;
            font-size: 10px;
            line-height: 1.35;
        }

        .header {
            border-bottom: 3px solid #0f5ea8;
            padding-bottom: 10px;
            margin-bottom: 12px;
        }

        .brand {
            color: #0f5ea8;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .8px;
        }

        h1 {
            margin: 2px 0 0;
            font-size: 21px;
            color: #111827;
        }

        .meta {
            color: #64748b;
            font-size: 9px;
            text-align: right;
        }

        .row {
            width: 100%;
            display: table;
        }

        .col {
            display: table-cell;
            vertical-align: top;
        }

        .summary {
            width: 100%;
            margin: 12px 0;
            border-collapse: separate;
            border-spacing: 8px 0;
        }

        .summary td {
            background: #eef6ff;
            border: 1px solid #c8e0f7;
            border-radius: 6px;
            padding: 9px 10px;
        }

        .summary .label {
            color: #64748b;
            display: block;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        .summary .value {
            color: #0f172a;
            display: block;
            font-size: 14px;
            font-weight: bold;
        }

        .filters {
            margin: 8px 0 12px;
            width: 100%;
            border-collapse: collapse;
        }

        .filters td {
            border: 1px solid #dbe4ee;
            padding: 5px 7px;
        }

        .filters .label {
            width: 85px;
            background: #f8fafc;
            color: #475569;
            font-weight: bold;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
        }

        .items th {
            background: #0f5ea8;
            color: #ffffff;
            font-size: 8px;
            padding: 6px 5px;
            text-align: left;
            text-transform: uppercase;
        }

        .items td {
            border-bottom: 1px solid #dbe4ee;
            padding: 5px;
            vertical-align: top;
        }

        .items tr:nth-child(even) td {
            background: #f8fbff;
        }

        .right {
            text-align: right;
        }

        .muted {
            color: #64748b;
            font-size: 8px;
        }

        .code {
            color: #0f5ea8;
            font-weight: bold;
            white-space: nowrap;
        }

        .empty {
            border: 1px dashed #cbd5e1;
            color: #64748b;
            margin-top: 20px;
            padding: 22px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="row">
            <div class="col">
                <div class="brand">Sistema de almacén</div>
                <h1>{{ $title }}</h1>
                <div class="muted">Clasificador presupuestario 2026</div>
            </div>
            <div class="col meta">
                Generado: {{ now()->format('d/m/Y H:i') }}<br>
                Tipo: {{ $existente ? 'Solo material existente' : 'Todos los items' }}
            </div>
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>
                <span class="label">Items</span>
                <span class="value">{{ number_format($summary['items'] ?? 0, 0, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Cantidad total</span>
                <span class="value">{{ number_format($summary['cantidad'] ?? 0, 2, ',', '.') }}</span>
            </td>
            <td>
                <span class="label">Registros impresos</span>
                <span class="value">{{ number_format($items->count(), 0, ',', '.') }}</span>
            </td>
        </tr>
    </table>

    <table class="filters">
        <tr>
            <td class="label">Grupo</td>
            <td>{{ $filters['grupo'] }}</td>
            <td class="label">Partida</td>
            <td>{{ $filters['partida'] }}</td>
        </tr>
        <tr>
            <td class="label">Subpartida</td>
            <td>{{ $filters['subpartida'] }}</td>
            <td class="label">Busqueda</td>
            <td>{{ $filters['busqueda'] }}</td>
        </tr>
    </table>

    @if ($items->isEmpty())
        <div class="empty">No existen registros para los filtros seleccionados.</div>
    @else
        <table class="items">
            <thead>
                <tr>
                    <th style="width: 28%">Item</th>
                    <th style="width: 9%">Unidad</th>
                    <th style="width: 9%" class="right">Cantidad</th>
                    <th style="width: 9%" class="right">P.U.</th>
                    <th style="width: 45%">Clasificador</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($items as $item)
                    <tr>
                        <td>{{ $item->nombre }}</td>
                        <td>{{ $item->unidad_medida ?: '-' }}</td>
                        <td class="right">{{ number_format($item->cantidad ?? 0, 2, ',', '.') }}</td>
                        <td class="right">{{ number_format($item->precio_unitario ?? 0, 2, ',', '.') }}</td>
                        <td>
                            <span class="code">{{ $item->subpartida?->codigo }}</span>
                            {{ $item->subpartida?->nombre }}<br>
                            <span class="muted">
                                {{ $item->subpartida?->partida?->codigo }} - {{ $item->subpartida?->partida?->nombre }} |
                                {{ $item->subpartida?->partida?->grupo?->codigo }} - {{ $item->subpartida?->partida?->grupo?->nombre }}
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</body>
</html>
