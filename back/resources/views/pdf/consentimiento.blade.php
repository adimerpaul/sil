<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consentimiento informado</title>
    <style>
        * {
            box-sizing: border-box;
        }
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 15px 25px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td, th {
            padding: 2px 3px;
            border: 1px solid #000;
            vertical-align: top;
        }
        .no-border td, .no-border th {
            border: none;
        }
        .titulo {
            text-align: center;
            font-weight: bold;
            font-size: 12px;
        }
        .subtitulo {
            font-weight: bold;
            text-align: center;
            margin-top: 6px;
            margin-bottom: 4px;
        }
        .firma-linea {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 180px;
        }
        .cuadro {
            display: inline-block;
            width: 9px;
            height: 9px;
            border: 1px solid #000;
            text-align: center;
            line-height: 9px;
            font-size: 8px;
        }
        .mt-5 { margin-top: 5px; }
        .mt-10 { margin-top: 10px; }
        .centrado { text-align: center; }
    </style>
</head>
<body>
@php
    $genero = strtoupper((string) $c->genero);
    $acepta = $c->tipo === 'ACEPTA';
    $rechaza = $c->tipo === 'RECHAZA';
@endphp

{{-- ENCABEZADO CON LOGOS --}}
<table class="no-border">
    <tr>
        <td style="width: 20%; text-align: left;" valign="top">
            {{-- LOGO IZQUIERDO --}}
            <img src="{{ public_path('img/logo-hospital.png') }}" style="height:100px;" alt="Logo">
        </td>
        <td style="width: 60%; text-align: center;" valign="top">
            <div class="titulo">
                FORMATO FORMULARIO DE CONSENTIMIENTO INFORMADO<br>
                HOSPITAL GENERAL “SAN JUAN DE DIOS”<br>
                SERVICIO DE LABORATORIO DE ANÁLISIS CLÍNICO MICROBIOLÓGICO
            </div>
        </td>
        <td style="width: 20%; text-align: right;" valign="top">
            {{-- LOGO DERECHO (OPCIONAL) --}}
            <img src="{{ public_path('img/logo-salud.png') }}" style="height:100px;" alt="Logo">
        </td>
    </tr>
</table>

<br>

<table>
    <tr>
        <td style="width: 50%;">FECHA RECEPCIÓN: {{ $c->fecha_recepcion }}</td>
        <td>HORA DE RECEP.: {{ $c->hora_recepcion }}</td>
    </tr>
    <tr>
        <td colspan="2">
            NOMBRE COMPLETO PACIENTE: {{ $c->nombre_completo }}
        </td>
    </tr>
    <tr>
        <td>FECHA DE NAC.: {{ $c->fecha_nac }}</td>
        <td>
            GÉNERO:
            F <span class="cuadro">{{ $genero === 'F' ? 'X' : '' }}</span>
            &nbsp;M <span class="cuadro">{{ $genero === 'M' ? 'X' : '' }}</span>
            &nbsp;OTRO <span class="cuadro">{{ $genero && $genero !== 'F' && $genero !== 'M' ? 'X' : '' }}</span>
        </td>
    </tr>
    <tr>
        <td>EDAD: {{ $c->edad }}</td>
        <td>CI: {{ $c->ci }}</td>
    </tr>
    <tr>
        <td colspan="2">DIRECCIÓN: {{ $c->direccion }}</td>
    </tr>
    <tr>
        <td>FECHA SOLICITUD: {{ $c->fecha_solicitud }}</td>
        <td>TELÉFONO: {{ $c->telefono }}</td>
    </tr>
    <tr>
        <td>
            DISCAP.:
            SI <span class="cuadro">{{ $c->discapacidad ? 'X' : '' }}</span>
            &nbsp;NO <span class="cuadro">{{ $c->discapacidad ? '' : 'X' }}</span>
        </td>
        <td>¿CUÁL?: {{ $c->discapacidad_cual }}</td>
    </tr>
    <tr>
        <td>
            EMB.:
            SI <span class="cuadro">{{ $c->embarazo ? 'X' : '' }}</span>
            &nbsp;NO <span class="cuadro">{{ $c->embarazo ? '' : 'X' }}</span>
        </td>
        <td>FUM: {{ $c->fum }} &nbsp;&nbsp; SEM GEST.: {{ $c->sem_gest }}</td>
    </tr>
    <tr>
        <td>
            MEDICAMENTO:
            SI <span class="cuadro">{{ $c->medicamento ? 'X' : '' }}</span>
            &nbsp;NO <span class="cuadro">{{ $c->medicamento ? '' : 'X' }}</span>
        </td>
        <td>TRATAMIENTO: {{ $c->tratamiento }}</td>
    </tr>
    <tr>
        <td colspan="2">
            CONDICIÓN:
            BASAL <span class="cuadro">{{ $c->condicion === 'BASAL' ? 'X' : '' }}</span>
            &nbsp;AYUNO PROL. <span class="cuadro">{{ $c->condicion === 'AYUNO PROL' ? 'X' : '' }}</span>
            &nbsp;POST PRANDIAL <span class="cuadro">{{ $c->condicion === 'POST PRANDIAL' ? 'X' : '' }}</span>
            &nbsp;ETAPA GESTACIÓN: {{ $c->etapa_gestacion }}
        </td>
    </tr>
</table>

<div class="subtitulo mt-5">CONSENTIMIENTO INFORMADO</div>

<p style="text-align: justify; margin-top: 4px;">
    Yo: <span class="firma-linea">{{ $c->declarante_nombre }}</span>,
    en mi condición de (Ej. mamá, tío, etc.):
    <span class="firma-linea">
        {{ $c->declarante_condicion === 'Otros'
            ? $c->declarante_condicion_otro
            : $c->declarante_condicion }}
    </span>,
    después de haber recibido la orientación correspondiente sobre la toma de muestra y
    los exámenes a realizar:
</p>

<p style="text-align: justify; margin-top: 4px;">
    ACEPTA
    <span class="cuadro">{{ $acepta ? 'X' : '' }}</span>
    &nbsp;&nbsp;
    RECHAZA
    <span class="cuadro">{{ $rechaza ? 'X' : '' }}</span>
    la toma de muestra a requerimiento para el/los examen(es) a realizarse, asumiendo la
    total responsabilidad de los inconvenientes o consecuencias que pudieran surgir por no
    acatar las indicaciones y recomendaciones realizadas.
</p>

<table class="no-border mt-10">
    <tr>
        <td class="centrado">
            ..............................................................<br>
            FIRMA DEL DECLARANTE O HUELLA
        </td>
        <td class="centrado">
            FECHA: {{ $c->fecha_consentimiento }}
        </td>
    </tr>
</table>

</body>
</html>
