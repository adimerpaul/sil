<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consentimiento informado</title>

    <style>
        @page { size: legal portrait; margin: 7mm 7mm; }

        * { box-sizing: border-box; }

        body{
            margin:0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;           /* MÁS DENSO */
            line-height: 1.02;          /* MÁS DENSO */
            color:#000;
        }

        table{
            width:100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        td, th{
            border:0.6px solid #000;    /* MÁS FINO */
            padding:0.6px 1px;          /* MÁS DENSO */
            vertical-align: middle;
            word-wrap: break-word;
        }

        .no-border td{ border:none; padding:0; }

        .head-title{
            text-align:center;
            font-weight:700;
            font-size: 10px;
            line-height: 1.05;
        }

        .head-sub{
            text-align:center;
            font-weight:700;
            font-size: 8px;
            margin-top: 1px;
        }

        .mini{
            font-size: 7.2px;
            line-height: 1.05;
        }

        .logo{ height: 42px; } /* MÁS PEQUEÑO */

        .label{
            font-weight:700;
            letter-spacing: 0.1px;
        }

        .box{
            display:inline-block;
            width: 8px;
            height: 8px;
            border: 0.8px solid #000;
            vertical-align: middle;
            margin: 0 2px;
            text-align:center;
            line-height: 7px;
            font-size: 8px;
        }

        .line{
            display:inline-block;
            border-bottom:0.8px solid #000;
            width: 100%;
            height: 10px;
            vertical-align: bottom;
        }

        .center{ text-align:center; }
        .right{ text-align:right; }
        .justify{ text-align:justify; }
    </style>
</head>

<body>
@php
    $genero  = strtoupper((string)($c->genero ?? ''));
    $acepta  = strtoupper((string)($c->tipo ?? '')) === 'ACEPTA';
    $rechaza = strtoupper((string)($c->tipo ?? '')) === 'RECHAZA';
//    formatFechaNacimientodmY
    function formatFechaNacimientodmY($fecha) {
        if (!$fecha) return '';
        try {
            $date = new DateTime($fecha);
            return $date->format('d/m/Y');
        } catch (Exception $e) {
            return $fecha; // Devuelve el valor original si no se puede formatear
        }
    }
@endphp

{{-- HEADER LOGOS + TITULO --}}
<table class="no-border" style="margin-bottom:2px;">
    <tr>
        <td style="width:16%;">
            <img src="{{ public_path('img/logo-hospital.png') }}" class="logo">
        </td>

        <td style="width:68%; text-align:center;">
            <div class="head-title">FORMATO FORMULARIO DE CONSENTIMIENTO INFORMADO</div>
            <div class="head-title">HOSPITAL GENERAL SAN JUAN DE DIOS</div>
            <div class="head-sub">SERVICIO DE LABORATORIO DE ANÁLISIS CLÍNICO MICROBIOLÓGICO</div>
            <div class="mini">
                DIRECCIÓN: CALLE DIRECCIÓN: SAN FELIPE ENTRE 6 DE OCTUBRE Y TARIJA<br>
                REGISTRO CONALAB: 001BBBREGISTRO CODELAB: 000004BB
            </div>
        </td>

        <td style="width:16%;" class="right">
            <img src="{{ public_path('img/logo-labo.png') }}" class="logo">
        </td>
    </tr>
</table>

{{-- INSTRUCCIÓN (como la foto) --}}
<table style="margin-bottom:2px;">
    <tr>
        <td class="mini">
            (LLENADO DEL MISMO DEBERA SER CON LETRA IMPRENTA CLARA Y LEGIBLE, BOLÍGRAFO AZUL,
            Y MARCANDO CON X LO QUE CORRESPONDA; EN FORMA COMPLETA)
        </td>
    </tr>
</table>

{{-- DATOS PRINCIPALES (estructura más parecida a la foto) --}}
<table>
    <tr>
        <td style="width:62%;">
            <span class="label">FECHA RECEPCIÓN:</span> {{ formatFechaNacimientodmY($c->fecha_recepcion) }}
        </td>
        <td style="width:38%;">
            <span class="label">HORA DE RECEP:</span> {{ $c->hora_recepcion }}
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <span class="label">NOMBRE COMPLETO PACIENTE:</span> {{ $c->nombre_completo }}
        </td>
    </tr>

    <tr>
        <td style="width:62%;">
            <span class="label">FECHA DE NAC:</span> {{ formatFechaNacimientodmY($c->fecha_nac) }}
        </td>
        <td style="width:38%;">
            <span class="label">GÉNERO:</span>
            F <span class="box">{{ $genero === 'F' ? 'X' : '' }}</span>
            M <span class="box">{{ $genero === 'M' ? 'X' : '' }}</span>
        </td>
    </tr>

    <tr>
        <td style="width:62%;">
            <span class="label">EDAD:</span> {{ $c->edad }}
        </td>
        <td style="width:38%;">
            <span class="label">CI:</span> {{ $c->ci }}
        </td>
    </tr>

    <tr>
        <td style="width:62%;">
            <span class="label">FECHA SOLICITUD:</span> {{ $c->fecha_solicitud }}
        </td>
        <td style="width:38%;">
            <span class="label">TELÉFONO:</span> {{ $c->telefono }}
        </td>
    </tr>

    <tr>
        <td style="width:62%;">
            <span class="label">DISCAP:</span>
            SI <span class="box">{{ !empty($c->discapacidad) ? 'X' : '' }}</span>
            NO <span class="box">{{ empty($c->discapacidad) ? 'X' : '' }}</span>
            <span class="label" style="margin-left:6px;">¿CUÁL?</span> {{ $c->discapacidad_cual }}
        </td>
        <td style="width:38%;">
            <span class="label">EMB</span>
            SI <span class="box">{{ !empty($c->embarazo) ? 'X' : '' }}</span>
            NO <span class="box">{{ empty($c->embarazo) ? 'X' : '' }}</span>
            <span class="label" style="margin-left:6px;">FUM:</span> {{ $c->fum }}
        </td>
    </tr>

    <tr>
        <td style="width:62%;">
            <span class="label">MEDICAMENTO:</span>
            SI <span class="box">{{ !empty($c->medicamento) ? 'X' : '' }}</span>
            NO <span class="box">{{ empty($c->medicamento) ? 'X' : '' }}</span>
        </td>
        <td style="width:38%;">
            <span class="label">TRATAMIENTO:</span> {{ $c->tratamiento }}
        </td>
    </tr>

    <tr>
        <td colspan="2">
            <span class="label">CONDICIÓN:</span>
            BASAL <span class="box">{{ ($c->condicion ?? '') === 'BASAL' ? 'X' : '' }}</span>
            AYUN PROL <span class="box">{{ ($c->condicion ?? '') === 'AYUNO PROL' ? 'X' : '' }}</span>
            POST PRANDIAL <span class="box">{{ ($c->condicion ?? '') === 'POST PRANDIAL' ? 'X' : '' }}</span>
{{--            <span class="label" style="margin-left:10px;">ETAPA DE GESTACIÓN:</span> {{ $c->etapa_gestacion }}--}}
        </td>
    </tr>
</table>

{{-- TITULO CENTRO --}}
<table style="margin-top:2px;">
    <tr>
        <td class="center" style="font-weight:700;">CONSENTIMIENTO INFORMADO</td>
    </tr>
</table>

{{-- TEXTO (más parecido al real) --}}
<table>
    <tr>
        <td class="justify">
            <span class="label">Yo:</span> {{ $c->declarante_nombre }}
            <span class="label">en mi condición de</span>
            {{ ($c->declarante_condicion ?? '') === 'Otros' ? ($c->declarante_condicion_otro ?? '') : ($c->declarante_condicion ?? '') }}
            <br>

            <span class="label">ACEPTO</span> <span class="box">{{ $acepta ? 'X' : '' }}</span>
            <span class="label">RECHAZO</span> <span class="box">{{ $rechaza ? 'X' : '' }}</span>
            la toma de muestra (previa orientación), a requerimiento para el (los) examen(es) a realizarse.
            Y acepto total responsabilidad de los inconvenientes o consecuencias que surjan al no acatar dichas indicaciones y recomendaciones.
        </td>
    </tr>

    <tr>
        <td class="center">
            <br> <br> <br> <br>
            <span class="label">FIRMA / HUELLA:</span>
            <span style="display:inline-block; width:65%; border-bottom:0.8px solid #000; height:10px;"></span>
{{--            fecha--}}
                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                <span class="label">FECHA:</span>
                {{ $c->fecha_consentimiento }}
        </td>
    </tr>
</table>

{{-- BLOQUE INFERIOR (MUESTRAS) --}}
{{--<table style="margin-top:2px;">--}}
{{--    <tr>--}}
{{--        <td style="width:62%;">--}}
{{--            <span class="label">HR TOMA DE MUESTRA:</span> {{ $c->hr_toma_muestra }}--}}
{{--        </td>--}}
{{--        <td style="width:38%;">--}}
{{--            <span class="label">HR RECEP MUESTRA:</span> {{ $c->hr_recep_muestra }}--}}
{{--        </td>--}}
{{--    </tr>--}}

{{--    <tr>--}}
{{--        <td colspan="2">--}}
{{--            <span class="label">MUESTRA:</span>--}}
{{--            SANGRE VENOSA <span class="box">{{ !empty($c->m_sangre_venosa) ? 'X' : '' }}</span>--}}
{{--            &nbsp; SANGRE ARTERIAL <span class="box">{{ !empty($c->m_sangre_arterial) ? 'X' : '' }}</span>--}}
{{--            &nbsp; SANGRE PERIFÉRICA <span class="box">{{ !empty($c->m_sangre_periferica) ? 'X' : '' }}</span>--}}
{{--            <br>--}}
{{--            ORINA <span class="box">{{ !empty($c->m_orina) ? 'X' : '' }}</span>--}}
{{--            <span class="label">HR RECOLECCIÓN:</span> {{ $c->hr_recoleccion_orina }}--}}
{{--            &nbsp;&nbsp;--}}
{{--            HECES <span class="box">{{ !empty($c->m_heces) ? 'X' : '' }}</span>--}}
{{--            <span class="label">HR RECOLECCIÓN:</span> {{ $c->hr_recoleccion_heces }}--}}
{{--            <br>--}}
{{--            LÍQUIDOS <span class="box">{{ !empty($c->m_liquidos) ? 'X' : '' }}</span>--}}
{{--            &nbsp; ESPUTO <span class="box">{{ !empty($c->m_esputo) ? 'X' : '' }}</span>--}}
{{--            &nbsp; SECRECIONES <span class="box">{{ !empty($c->m_secreciones) ? 'X' : '' }}</span>--}}
{{--        </td>--}}
{{--    </tr>--}}

{{--    <tr>--}}
{{--        <td colspan="2">--}}
{{--            <span class="label">OBSERVACIONES:</span> {{ $c->observaciones }}--}}
{{--        </td>--}}
{{--    </tr>--}}

{{--    <tr>--}}
{{--        <td colspan="2">--}}
{{--            <span class="label">RESPONSABLE TOMA DE MUESTRA</span>--}}
{{--            <div style="height:14px;"></div>--}}
{{--            <span class="mini">(SELLO)</span>--}}
{{--        </td>--}}
{{--    </tr>--}}
{{--</table>--}}

</body>
</html>
