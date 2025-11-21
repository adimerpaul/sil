<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consentimiento informado</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        td, th { padding: 3px; border: 1px solid #000; }
        .no-border td, .no-border th { border: none; }
        .titulo { text-align: center; font-weight: bold; font-size: 14px; }
        .subtitulo { font-weight: bold; margin-top: 10px; }
        .firma-linea { border-bottom: 1px solid #000; width: 60%; display: inline-block; }
    </style>
</head>
<body>
<div class="titulo">
    FORMULARIO DE CONSENTIMIENTO INFORMADO<br>
    HOSPITAL GENERAL SAN JUAN DE DIOS ORURO
</div>

<table>
    <tr>
        <td>Fecha recepción: {{ $c->fecha_recepcion}}</td>
        <td>Hora recepción: {{ $c->hora_recepcion }}</td>
    </tr>
    <tr>
        <td colspan="2">Nombre completo del paciente: {{ $c->nombre_completo }}</td>
    </tr>
    <tr>
        <td>
{{--            {{$c}}--}}
            Fecha de nac.: {{ $c->fecha_nac }}
        </td>
        <td>Género: {{ $c->genero }}</td>
    </tr>
    <tr>
        <td>Edad: {{ $c->edad }}</td>
        <td>C.I.: {{ $c->ci }}</td>
    </tr>
    <tr>
        <td colspan="2">Dirección: {{ $c->direccion }}</td>
    </tr>
    <tr>
        <td>Fecha de solicitud: {{ optional($c->fecha_solicitud)->format('d/m/Y') }}</td>
        <td>Teléfono: {{ $c->telefono }}</td>
    </tr>
    <tr>
        <td>Discapacidad: {{ $c->discapacidad ? 'SI' : 'NO' }}</td>
        <td>¿Cuál?: {{ $c->discapacidad_cual }}</td>
    </tr>
    <tr>
        <td>Embarazo: {{ $c->embarazo ? 'SI' : 'NO' }}</td>
        <td>FUM: {{ optional($c->fum)->format('d/m/Y') }} / Sem gest.: {{ $c->sem_gest }}</td>
    </tr>
    <tr>
        <td>Medicamento: {{ $c->medicamento ? 'SI' : 'NO' }}</td>
        <td>Tratamiento: {{ $c->tratamiento }}</td>
    </tr>
    <tr>
        <td colspan="2">
            Condición:
            {{ $c->condicion }} @if($c->etapa_gestacion) - Etapa gestación: {{ $c->etapa_gestacion }} @endif
        </td>
    </tr>
</table>

<p class="subtitulo">CONSENTIMIENTO INFORMADO</p>

<p>
    Yo: <span class="firma-linea">{{ $c->declarante_nombre }}</span>
    en mi condición de <span class="firma-linea">{{ $c->declarante_condicion }}</span>,
    'ACEPTO' y DOY MI consentimiento para la toma de muestra
    a requerimiento para el/los examen/nes a realizar, y acepto la responsabilidad de los
    inconvenientes o consecuencias que surjan al no acatar dichas indicaciones y recomendaciones.
</p>

<p>
    Yo: <span class="firma-linea">{{ $c->declarante_nombre }}</span>
    en mi condición de <span class="firma-linea">{{ $c->declarante_condicion }}</span>,
    'RECHAZO' y DOY MI consentimiento para la toma de muestra
    a requerimiento para el/los examen/nes a realizar, y acepto la responsabilidad de los
    inconvenientes o consecuencias que surjan al no acatar dichas indicaciones y recomendaciones.
</p>

<p style="margin-top: 40px;">
    FIRMA: ________________________________
</p>

<p style="margin-top: 10px;">
    Fecha: {{ optional($c->fecha_consentimiento)->format('d/m/Y') }}
</p>
</body>
</html>
