<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consentimiento informado</title>

    <style>
        /* HOJA OFICIO (LEGAL) */
        @page {
            size: legal portrait;
            margin: 10mm 10mm;
        }

        * { box-sizing: border-box; }

        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
            line-height: 1.12;
            color: #000;
        }

        /* CONTENEDOR QUE LIMITA A 3/4 DE LA HOJA */
        .page-75 {
            height: 75%;
            overflow: hidden; /* fuerza el corte */
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td, th {
            border: 0.8px solid #000;
            padding: 1px 2px;
            vertical-align: top;
            word-wrap: break-word;
        }

        .no-border td, .no-border th {
            border: none;
            padding: 0;
        }

        .titulo {
            text-align: center;
            font-weight: bold;
            font-size: 10px;
            line-height: 1.12;
        }

        .subtitulo {
            text-align: center;
            font-weight: bold;
            font-size: 9px;
            margin: 3px 0;
        }

        .cuadro {
            display: inline-block;
            width: 8px;
            height: 8px;
            border: 1px solid #000;
            line-height: 8px;
            font-size: 7px;
            text-align: center;
            vertical-align: middle;
        }

        .firma-linea {
            display: inline-block;
            min-width: 145px;
            border-bottom: 1px solid #000;
        }

        .logo {
            height: 65px;
        }

        p {
            margin: 3px 0;
            text-align: justify;
        }

        .centrado { text-align: center; }
    </style>
</head>

<body>
@php
    $genero  = strtoupper((string) ($c->genero ?? ''));
    $acepta  = ($c->tipo ?? '') === 'ACEPTA';
    $rechaza = ($c->tipo ?? '') === 'RECHAZA';
@endphp

<div class="page-75">

    {{-- ENCABEZADO --}}
    <table class="no-border">
        <tr>
            <td style="width:18%;">
                <img src="{{ public_path('img/logo-hospital.png') }}" class="logo">
            </td>
            <td style="width:64%; text-align:center;">
                <div class="titulo">
                    FORMATO FORMULARIO DE CONSENTIMIENTO INFORMADO<br>
                    HOSPITAL GENERAL “SAN JUAN DE DIOS”<br>
                    SERVICIO DE LABORATORIO DE ANÁLISIS CLÍNICO MICROBIOLÓGICO
                </div>
            </td>
            <td style="width:18%; text-align:right;">
                <img src="{{ public_path('img/logo-labo.png') }}" class="logo">
            </td>
        </tr>
    </table>

    <table>
        <tr>
            <td>FECHA RECEPCIÓN: {{ $c->fecha_recepcion }}</td>
            <td>HORA: {{ $c->hora_recepcion }}</td>
        </tr>

        <tr>
            <td colspan="2">NOMBRE COMPLETO: {{ $c->nombre_completo }}</td>
        </tr>

        <tr>
            <td>FECHA NAC.: {{ $c->fecha_nac }}</td>
            <td>
                GÉNERO:
                F <span class="cuadro">{{ $genero === 'F' ? 'X' : '' }}</span>
                M <span class="cuadro">{{ $genero === 'M' ? 'X' : '' }}</span>
                OTRO <span class="cuadro">{{ $genero && !in_array($genero,['F','M']) ? 'X' : '' }}</span>
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
                NO <span class="cuadro">{{ !$c->discapacidad ? 'X' : '' }}</span>
            </td>
            <td>¿CUÁL?: {{ $c->discapacidad_cual }}</td>
        </tr>

        <tr>
            <td>
                EMB.:
                SI <span class="cuadro">{{ $c->embarazo ? 'X' : '' }}</span>
                NO <span class="cuadro">{{ !$c->embarazo ? 'X' : '' }}</span>
            </td>
            <td>FUM: {{ $c->fum }} — SEM GEST.: {{ $c->sem_gest }}</td>
        </tr>

        <tr>
            <td>
                MEDICAMENTO:
                SI <span class="cuadro">{{ $c->medicamento ? 'X' : '' }}</span>
                NO <span class="cuadro">{{ !$c->medicamento ? 'X' : '' }}</span>
            </td>
            <td>TRATAMIENTO: {{ $c->tratamiento }}</td>
        </tr>

        <tr>
            <td colspan="2">
                CONDICIÓN:
                BASAL <span class="cuadro">{{ $c->condicion==='BASAL'?'X':'' }}</span>
                AYUNO <span class="cuadro">{{ $c->condicion==='AYUNO PROL'?'X':'' }}</span>
                POST <span class="cuadro">{{ $c->condicion==='POST PRANDIAL'?'X':'' }}</span>
            </td>
        </tr>
    </table>

    <div class="subtitulo">CONSENTIMIENTO INFORMADO</div>

    <p>
        Yo <span class="firma-linea">{{ $c->declarante_nombre }}</span>,
        en mi condición de
        <span class="firma-linea">
      {{ $c->declarante_condicion === 'Otros'
        ? $c->declarante_condicion_otro
        : $c->declarante_condicion }}
    </span>,
        luego de recibir la información correspondiente:
    </p>

    <p>
        ACEPTA <span class="cuadro">{{ $acepta?'X':'' }}</span>
        RECHAZA <span class="cuadro">{{ $rechaza?'X':'' }}</span>
        la toma de muestra, asumiendo total responsabilidad por las consecuencias.
    </p>

    <table class="no-border" style="margin-top:50px;">
        <tr>
            <td class="centrado" style="width:60%;">
                ......................................................<br>
                FIRMA / HUELLA
            </td>
            <td class="centrado" style="width:40%;">
                FECHA: {{ $c->fecha_consentimiento }}
            </td>
        </tr>
    </table>

</div>
</body>
</html>
