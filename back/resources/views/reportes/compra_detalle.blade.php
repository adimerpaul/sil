<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Compra #{{ $compra->id }}</title>
    <style>
        @page { margin: 22px 24px; }

        body {
            font-family: Helvetica, Arial, sans-serif;
            color: #172033;
            font-size: 10px;
            line-height: 1.3;
        }

        .header {
            border-bottom: 2px solid #0f5ea8;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .brand {
            color: #0f5ea8;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: .6px;
        }

        h1 {
            margin: 2px 0 0;
            font-size: 18px;
            color: #111827;
        }

        .row { clear: both; width: 100%; }
        .row:after { clear: both; content: ""; display: block; }
        .col-left { float: left; width: 65%; }
        .col-right { float: right; width: 35%; text-align: right; color: #64748b; }

        .badge {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge-activo { background: #dcfce7; color: #166534; }
        .badge-anulado { background: #fee2e2; color: #991b1b; }

        .meta {
            border: 1px solid #dbe4ee;
            background: #f8fafc;
            padding: 8px 10px;
            margin: 10px 0;
        }

        .meta-row { width: 100%; }
        .meta-row:after { clear: both; content: ""; display: block; }
        .meta-cell {
            float: left;
            width: 33%;
            padding: 3px 4px;
            box-sizing: border-box;
        }
        .meta-label {
            color: #64748b;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: .4px;
            font-weight: bold;
            display: block;
        }
        .meta-value {
            color: #0f172a;
            font-size: 11px;
            font-weight: bold;
            text-transform: capitalize;
            display: block;
        }

        h2 {
            margin: 12px 0 6px;
            font-size: 12px;
            color: #0f5ea8;
            border-bottom: 1px solid #c8e0f7;
            padding-bottom: 3px;
        }

        table.items {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
        }
        table.items th {
            background: #0f5ea8;
            color: #fff;
            text-align: left;
            padding: 5px 6px;
            font-size: 9px;
            text-transform: uppercase;
        }
        table.items td {
            padding: 5px 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10px;
            vertical-align: middle;
        }
        table.items tr:nth-child(even) td { background: #f8fafc; }
        .right { text-align: right; }
        .center { text-align: center; }
        .producto-nombre { text-transform: capitalize; font-weight: bold; }
        .muted { color: #64748b; font-size: 8px; }

        .totals-wrap {
            width: 100%;
            margin-top: 14px;
        }
        .totals-wrap:after { clear: both; content: ""; display: block; }
        table.totals {
            width: 50%;
            float: right;
            border-collapse: collapse;
            border: 1px solid #c8e0f7;
        }
        table.totals td {
            padding: 6px 10px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 11px;
        }
        table.totals tr:last-child td { border-bottom: none; }
        table.totals td.label { color: #475569; }
        table.totals td.value {
            text-align: right;
            font-weight: bold;
            color: #0f172a;
            white-space: nowrap;
        }
        table.totals tr.total td {
            background: #0f5ea8;
            color: #fff;
            font-size: 14px;
            font-weight: bold;
            padding: 8px 10px;
        }
        table.totals tr.total td.value { color: #fff; }

        .footer {
            margin-top: 30px;
            clear: both;
            border-top: 1px solid #e2e8f0;
            padding-top: 8px;
            text-align: center;
        }
        .footer-brand {
            color: #0f5ea8;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .footer-meta {
            color: #64748b;
            font-size: 8px;
            margin-top: 2px;
        }

        .signatures {
            margin-top: 50px;
            clear: both;
            width: 100%;
        }
        .signatures:after { clear: both; content: ""; display: block; }
        .sign-box {
            float: left;
            width: 45%;
            text-align: center;
            border-top: 1px solid #475569;
            padding-top: 4px;
            font-size: 9px;
            color: #475569;
        }
        .sign-box.right { float: right; }
    </style>
</head>
<body>

<div class="header">
    <div class="row">
        <div class="col-left">
            <div class="brand">SILL · Almacén</div>
            <h1>Comprobante de compra #{{ $compra->id }}</h1>
            <div class="muted">
                Emitido el {{ \Carbon\Carbon::parse($compra->fecha_hora)->format('d/m/Y H:i') }}
                @if ($compra->user) · Registrado por {{ $compra->user->name }} @endif
            </div>
        </div>
        <div class="col-right">
            <span class="badge {{ $compra->estado === 'ACTIVO' ? 'badge-activo' : 'badge-anulado' }}">
                {{ $compra->estado }}
            </span>
            <div class="muted" style="margin-top:4px;">
                {{ $compra->tipo_registro }}
            </div>
        </div>
    </div>
</div>

<div class="meta">
    <div class="meta-row">
        <div class="meta-cell">
            <span class="meta-label">Proveedor</span>
            <span class="meta-value">{{ optional($compra->proveedor)->nombre ?? $compra->nombre ?? 'Sin proveedor' }}</span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Carnet / NIT</span>
            <span class="meta-value">{{ optional($compra->proveedor)->carnet ?? $compra->carnet ?? '-' }}</span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">N° factura</span>
            <span class="meta-value">{{ $compra->nro_factura ?: '-' }}</span>
        </div>
    </div>
    <div class="meta-row">
        <div class="meta-cell">
            <span class="meta-label">Motivo</span>
            <span class="meta-value">{{ strtolower($compra->motivo_registro ?? '-') }}</span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Tipo de pago</span>
            <span class="meta-value">{{ strtolower($compra->tipo_pago ?: 'ninguno') }}</span>
        </div>
        <div class="meta-cell">
            <span class="meta-label">Items</span>
            <span class="meta-value">{{ count($compra->detalles) }} producto(s)</span>
        </div>
    </div>
    @if ($compra->comentario)
        <div class="meta-row">
            <div class="meta-cell" style="width:100%;">
                <span class="meta-label">Comentario</span>
                <span class="meta-value">{{ $compra->comentario }}</span>
            </div>
        </div>
    @endif
</div>

<h2>Detalle de productos</h2>

<table class="items">
    <thead>
        <tr>
            <th style="width: 6%;">#</th>
            <th style="width: 38%;">Producto</th>
            <th style="width: 8%;" class="center">Cant.</th>
            <th style="width: 12%;" class="right">P. unit.</th>
            <th style="width: 14%;" class="right">Total</th>
            <th style="width: 10%;">Lote</th>
            <th style="width: 12%;" class="center">Vence</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($compra->detalles as $idx => $det)
            <tr>
                <td>{{ $idx + 1 }}</td>
                <td>
                    <div class="producto-nombre">{{ $det->nombre ?? optional($det->producto)->nombre ?? '-' }}</div>
                    @if (optional($det->producto)->unidad_medida)
                        <div class="muted">{{ $det->producto->unidad_medida }}</div>
                    @endif
                </td>
                <td class="center">{{ $det->cantidad }}</td>
                <td class="right">{{ number_format((float) $det->precio, 2, ',', '.') }} Bs</td>
                <td class="right"><b>{{ number_format((float) $det->total, 2, ',', '.') }} Bs</b></td>
                <td>{{ $det->lote ?: '-' }}</td>
                <td class="center">{{ $det->fecha_vencimiento ? \Carbon\Carbon::parse($det->fecha_vencimiento)->format('d/m/Y') : '-' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7" class="center muted">Sin productos</td>
            </tr>
        @endforelse
    </tbody>
</table>

<div class="totals-wrap">
    <table class="totals">
        <tr>
            <td class="label">Subtotal</td>
            <td class="value">{{ number_format((float) $compra->total, 2, ',', '.') }} Bs</td>
        </tr>
        <tr>
            <td class="label">Items</td>
            <td class="value">{{ count($compra->detalles) }}</td>
        </tr>
        <tr class="total">
            <td class="label">Total</td>
            <td class="value">{{ number_format((float) $compra->total, 2, ',', '.') }} Bs</td>
        </tr>
    </table>
</div>

<div class="signatures">
    <div class="sign-box">
        Firma del responsable
    </div>
    <div class="sign-box right">
        Firma del proveedor
    </div>
</div>

<div class="footer">
    <div class="footer-brand">Hospital General</div>
    <div class="footer-meta">Documento generado el {{ now()->format('d/m/Y H:i') }}</div>
</div>

</body>
</html>
