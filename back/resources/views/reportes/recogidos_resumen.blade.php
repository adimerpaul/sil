<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        @page { size: letter landscape; margin: 10px 12px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 9px; color: #111; }
        .title { font-size: 13px; font-weight: 700; text-align: center; }
        .meta { font-size: 8px; color: #444; margin-top: 4px; text-align: center; }
        .kpis { margin-top: 6px; font-size: 9px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; table-layout: fixed; }
        th, td { border: 1px solid #222; padding: 4px; vertical-align: top; }
        th { background: #eceff3; text-align: center; font-size: 8px; }
        .center { text-align: center; }
        .small { font-size: 8px; color: #555; }
    </style>
</head>
<body>
<div class="title">{{ strtoupper($titulo) }}</div>
<div class="meta">
    Generado: {{ $generado->format('d/m/Y H:i') }}
    @if($tipo === 'dia') | Día: {{ $date }} @endif
    @if($from || $to) | Rango: {{ $from ?: '-' }} a {{ $to ?: '-' }} @endif
    @if($area) | Área: {{ $area->title ?: $area->name }} @endif
    @if($search) | Búsqueda: "{{ $search }}" @endif
</div>
<div class="kpis">
    <b>Total:</b> {{ $totales['total'] }} |
    <b>Recogidos:</b> {{ $totales['recogidos'] }} |
    <b>Pendientes:</b> {{ $totales['pendientes'] }} |
    <b>Realizados:</b> {{ $totales['realizados'] }}
</div>

<table>
    <thead>
    <tr>
{{--        <th>#</th>--}}
        <th>Código</th>
        <th>Nro Registro</th>
        <th>Fecha Solicitud Medico</th>
        <th>Fecha Creacion Solicitud</th>

        <th>Paciente</th>
        <th>Medico Solicitante</th>
        <th>Área</th>
        <th>Prestacion</th>
        <th>Estado</th>
        <th>Recogido por</th>
        <th>CI</th>
        <th>Teléfono</th>
        <th>Parentesco</th>
        <th>Fecha/Hora Recogido</th>
    </tr>
    </thead>
    <tbody>
    @foreach($rows as $r)
        <tr>
{{--            <td class="center">{{ $r->id }}</td>--}}
            <td class="center">{{ $r->codigo ?: '-' }}</td>
            <td class="center">{{ $r->nro_registro ?: '-' }}</td>
            <td class="center">{{ $r->fecha_solicitud ? \Carbon\Carbon::parse($r->fecha_solicitud)->format('d/m/Y') : '-' }}</td>
            <td class="center">{{ $r->fecha_creacion ? \Carbon\Carbon::parse($r->fecha_creacion)->format('d/m/Y H:i') : '-' }}</td>
            <td>{{ $r->paciente_nombre ?: '-' }}</td>
            <td>{{ $r->doctor_nombre ?: '-' }}</td>
            <td>{{ $r->area_title ?: ($r->area_name ?: '-') }}</td>
            <td>{{ $r->servicio_nombre_pivot ?: ($r->servicio_nombre_catalogo ?: '-') }}</td>
            <td class="center">
                @if($r->fue_recogido) RECOGIDO
                @elseif($r->realizado === 'REALIZADO') ACTIVO
                @else PENDIENTE
                @endif
            </td>
            <td>{{ $r->recogido_por_personal ?: '-' }}</td>
            <td class="center">{{ $r->ci_recogido ?: '-' }}</td>
            <td class="center">{{ $r->telefono_recogido ?: '-' }}</td>
            <td class="center">{{ $r->grado_parentesco ?: '-' }}</td>
            <td class="center">{{ $r->recogido_en_dia ? \Carbon\Carbon::parse($r->recogido_en_dia)->format('d/m/Y H:i') : '-' }}</td>
        </tr>
    @endforeach
    </tbody>
</table>

<div class="small" style="margin-top:6px;">Sistema SIL - Reporte de Recogidos</div>
</body>
</html>

