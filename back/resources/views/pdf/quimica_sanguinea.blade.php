<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">

    <style>
        @page { size: letter landscape; margin: 6px 8px; }

        * { box-sizing: border-box; }

        body{
            margin:0; padding:0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7.2px;
            line-height: 1.08;
            color:#111;
        }

        .hr{ border-top:1.4px solid #111; margin:2px 0; }
        .muted{ color:#666; }
        .bold{ font-weight:700; }
        .center{ text-align:center; }
        .right{ text-align:right; }
        .small{ font-size:6.6px; }

        .no-border{ border-collapse:collapse; width:100%; }
        .no-border td{ border:none; padding:0; }

        .layout{
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
        }
        .col{
            width:50%;
            vertical-align:top;
            padding:0 5px;
        }

        /* secciones */
        .block{
            border:1px solid #111;
            margin:3px 0;
        }
        .block .title{
            background:#f2f2f2;
            border-bottom:1px solid #111;
            padding:2px 3px;
            font-size:7.6px;
            font-weight:700;
            text-transform:uppercase;
            letter-spacing:.02em;
        }
        .block .body{ padding:2px 3px; }

        .tbl{
            width:100%;
            border-collapse:collapse;
            table-layout:fixed;
        }
        .tbl th, .tbl td{
            border:1px solid #111;
            padding:1.5px 2px;
            vertical-align:middle;
        }
        .tbl th{
            background:#f7f7f7;
            font-size:7px;
        }
        .tbl td{ font-size:7.1px; }

        /* columnas en tabla analitos */
        .w-analito{ width:44%; }
        .w-res{ width:16%; }
        .w-unid{ width:14%; }
        .w-rango{ width:26%; }

        /* evitar cortes */
        table, tr, td, th, .block { page-break-inside: avoid; }
    </style>
</head>

<body>
@php
    // Esperado: $solicitud y $quimica (o $q) y $rangos (array)
    $q = $quimica ?? $q ?? null;

    $rangosMap = [];
    foreach(($rangos ?? []) as $r){
      $key = mb_strtolower(trim($r->rango_nombre ?? ''));
      if($key) $rangosMap[$key] = $r;
    }

    function keyNom($s){ return mb_strtolower(trim($s ?? '')); }

    function rangoTexto($name, $map){
      $k = keyNom($name);
      if(!isset($map[$k])) return '';
      $r = $map[$k];
      if($r->rango_minimo !== null && $r->rango_maximo !== null){
        return $r->rango_minimo.' - '.$r->rango_maximo;
      }
      return $r->interpretacion ?? '';
    }
    function rangoUnidad($name, $map){
      $k = keyNom($name);
      if(!isset($map[$k])) return '';
      return $map[$k]->unidad ?? '';
    }
    function val($obj, $field){
      if(!$obj) return '';
      return $obj->$field ?? '';
    }
@endphp
<table style="width: 100%; border-collapse:collapse; table-layout:fixed;">
    <tr>
            @foreach(['izq','der'] as $side)
            <td style="width: 50%; vertical-align:top; padding:0 4px;">
                <!-- ===================== HEADER ===================== -->
                <table class="no-border">
                    <tr>
                        <td style="width:12%">
                            @if(file_exists(public_path('img/logo-hospital.png')))
                                <img src="{{ public_path('img/logo-hospital.png') }}" style="width:48px">
                            @endif
                        </td>
                        <td class="center">
                            <div style="font-size:9px;font-weight:700;">HOSPITAL GENERAL SAN JUAN DE DIOS ORURO</div>
                            <div class="small muted">LABORATORIO DE ANÁLISIS CLÍNICO - MICROBIOLÓGICO</div>
                            <div class="small muted">San Felipe entre 6 de Octubre y Tarija</div>
                        </td>
                        <td style="width:12%" class="right">
                            @if(file_exists(public_path('img/logo-labo.png')))
                                <img src="{{ public_path('img/logo-labo.png') }}" style="width:48px">
                            @endif
                        </td>
                    </tr>
                </table>

                <div class="hr"></div>

                <!-- ===================== DATOS PACIENTE ===================== -->
                <table class="tbl" style="margin-bottom:3px;">
                    <tr>
                        <td style="width:10%" class="bold">Paciente</td>
                        <td style="width:40%">{{ $solicitud->paciente_nombre ?? optional($solicitud->paciente)->nombre_completo ?? '-' }}</td>
                        <td style="width:8%" class="bold">CI</td>
                        <td style="width:18%">{{ $solicitud->paciente_ci ?? optional($solicitud->paciente)->ci ?? '-' }}</td>
                        <td style="width:8%" class="bold">N°</td>
                        <td style="width:16%">{{ $solicitud->nro_registro ?? $solicitud->id ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="bold">Edad</td>
                        <td>{{ $solicitud->paciente_edad ?? optional($solicitud->paciente)->edad ?? '-' }}</td>
                        <td class="bold">Sexo</td>
                        <td>{{ $solicitud->paciente_genero ?? optional($solicitud->paciente)->genero ?? '-' }}</td>
                        <td class="bold">Fecha</td>
                        <td>{{ $solicitud->fecha_solicitud ?? $solicitud->date ?? '-' }}</td>
                    </tr>
                    <tr>
                        <td class="bold">Médico</td>
                        <td colspan="3">{{ $solicitud->doctor_nombre ?? optional($solicitud->doctor)->nombre ?? '-' }}</td>
                        <td class="bold">Estado</td>
                        <td>{{ $solicitud->estado ?? '-' }}</td>
                    </tr>
                </table>

                <div class="center bold" style="font-size:8px; margin:2px 0;">QUÍMICA SANGUÍNEA</div>
                <div class="center small muted">
                    Método: {{ val($q,'metodo') ?: '—' }} &nbsp; • &nbsp; Equipo: {{ val($q,'equipo') ?: '—' }}
                </div>

                <!-- ===================== 2 COLUMNAS PRINCIPALES ===================== -->
                <table class="layout">
                    <tr>
                        <!-- IZQUIERDA -->
                        <td class="col">

                            <!-- Química sanguínea básica -->
                            <div class="block">
                                <div class="title">Química sanguínea básica</div>
                                <div class="body">
                                    <table class="tbl">
                                        <thead>
                                        <tr>
                                            <th class="w-analito">Analito</th>
                                            <th class="w-res">Res</th>
                                            <th class="w-rango">Rango</th>
                                            <th class="w-unid">Unid</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $basica = [
                                              ['Ácido Úrico','acido_urico'],
                                              ['Albúmina','albumina'],
                                              ['Proteínas totales','proteinas_totales'],
                                              ['Glucosa','glucosa'],
                                              ['Urea','urea'],
                                              ['NUS','nus'],
                                              ['Creatinina','creatinina'],
                                            ];
                                        @endphp
                                        @foreach($basica as [$label,$field])
                                            <tr>
                                                <td>{{ $label }}</td>
                                                <td class="center">{{ val($q,$field) }}</td>
                                                <td class="center">{{ rangoTexto($label,$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad($label,$rangosMap) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Perfil lipídico -->
                            <div class="block">
                                <div class="title">Perfil lipídico</div>
                                <div class="body">
                                    <table class="tbl">
                                        <thead>
                                        <tr>
                                            <th class="w-analito">Analito</th>
                                            <th class="w-res">Res</th>
                                            <th class="w-rango">Rango</th>
                                            <th class="w-unid">Unid</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $lipidico = [
                                              ['Colesterol total','colesterol_total'],
                                              ['Triglicéridos','trigliceridos'],
                                              ['HDL Colesterol','hdl_colesterol'],
                                              ['LDL Colesterol','ldl_colesterol'],
                                              ['VLDL Colesterol','vldl_colesterol'],
                                            ];
                                        @endphp
                                        @foreach($lipidico as [$label,$field])
                                            <tr>
                                                <td>{{ $label }}</td>
                                                <td class="center">{{ val($q,$field) }}</td>
                                                <td class="center">{{ rangoTexto($label,$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad($label,$rangosMap) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </td>

                        <!-- DERECHA -->
                        <td class="col">

                            <!-- Enzimas hepáticas y bilirrubinas -->
                            <div class="block">
                                <div class="title">Enzimas hepáticas y bilirrubinas</div>
                                <div class="body">
                                    <table class="tbl">
                                        <thead>
                                        <tr>
                                            <th class="w-analito">Analito</th>
                                            <th class="w-res">Res</th>
                                            <th class="w-rango">Rango</th>
                                            <th class="w-unid">Unid</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $hepatico = [
                                              ['Bilirrubina Total','bilirrubina_total'],
                                              ['Bilirrubina Directa','bilirrubina_directa'],
                                              ['Bilirrubina Indirecta','bilirrubina_indirecta'],
                                              ['G.O.T. (TGO)','got'],
                                              ['G.P.T. (TGP)','gpt'],
                                              ['Fosfatasa Alcalina','fosfatasa_alcalina'],
                                              ['GGT','ggt'],
                                              ['Amilasa','amilasa'],
                                            ];
                                        @endphp
                                        @foreach($hepatico as [$label,$field])
                                            <tr>
                                                <td>{{ $label }}</td>
                                                <td class="center">{{ val($q,$field) }}</td>
                                                <td class="center">{{ rangoTexto($label,$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad($label,$rangosMap) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Electrolitos y minerales -->
                            <div class="block">
                                <div class="title">Electrolitos y minerales</div>
                                <div class="body">
                                    <table class="tbl">
                                        <thead>
                                        <tr>
                                            <th class="w-analito">Analito</th>
                                            <th class="w-res">Res</th>
                                            <th class="w-rango">Rango</th>
                                            <th class="w-unid">Unid</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $electro = [
                                              ['Sodio','sodio'],
                                              ['Potasio','potasio'],
                                              ['Cloro','cloro'],
                                              ['Calcio','calcio'],
                                              ['Fósforo','fosforo'],
                                              ['Magnesio','magnesio'],
                                              ['LDH','ldh'],
                                              ['Hierro sérico','hierro_serico'],
                                            ];
                                        @endphp
                                        @foreach($electro as [$label,$field])
                                            <tr>
                                                <td>{{ $label }}</td>
                                                <td class="center">{{ val($q,$field) }}</td>
                                                <td class="center">{{ rangoTexto($label,$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad($label,$rangosMap) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </td>
                    </tr>
                </table>

                <!-- ===================== BLOQUES FULL ANCHO (ABAJO) ===================== -->
                <table class="layout" style="margin-top:3px;">
                    <tr>
                        <td style="width:100%; padding:0 5px; vertical-align:top;">

                            <!-- Orina 24 horas + Control glucémico (en 2 columnas abajo) -->
                            <table class="layout">
                                <tr>
                                    <td class="col">

                                        <div class="block">
                                            <div class="title">Orina de 24 horas</div>
                                            <div class="body">
                                                <table class="tbl">
                                                    <thead>
                                                    <tr>
                                                        <th class="w-analito">Parámetro</th>
                                                        <th class="w-res">Res</th>
                                                        <th class="w-rango">Rango</th>
                                                        <th class="w-unid">Unid</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @php
                                                        $orina24 = [
                                                          ['Creatinuria 24 hrs.','creatinuria_24h'],
                                                          ['Proteinuria de 24 hrs.','proteinuria_24h'],
                                                          ['Volumen 24 h','volumen_24h'],
                                                        ];
                                                    @endphp
                                                    @foreach($orina24 as [$label,$field])
                                                        <tr>
                                                            <td>{{ $label }}</td>
                                                            <td class="center">{{ val($q,$field) }}</td>
                                                            <td class="center">{{ rangoTexto($label,$rangosMap) }}</td>
                                                            <td class="center">{{ rangoUnidad($label,$rangosMap) }}</td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    </td>

                                    <td class="col">

                                        <div class="block">
                                            <div class="title">Control glucémico</div>
                                            <div class="body">
                                                <table class="tbl">
                                                    <thead>
                                                    <tr>
                                                        <th class="w-analito">Parámetro</th>
                                                        <th class="w-res">Res</th>
                                                        <th class="w-rango">Rango</th>
                                                        <th class="w-unid">Unid</th>
                                                    </tr>
                                                    </thead>
                                                    <tbody>
                                                    @php
                                                        $gluco = [
                                                          ['Hb glicosilada','hb_glicosilada'],
                                                          ['Hb A1C','hb_a1c'],
                                                        ];
                                                    @endphp
                                                    @foreach($gluco as [$label,$field])
                                                        <tr>
                                                            <td>{{ $label }}</td>
                                                            <td class="center">{{ val($q,$field) }}</td>
                                                            <td class="center">{{ rangoTexto($label,$rangosMap) }}</td>
                                                            <td class="center">{{ rangoUnidad($label,$rangosMap) }}</td>
                                                        </tr>
                                                    @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>

                                    </td>
                                </tr>
                            </table>

                            <!-- Serológicos full ancho -->
                            <div class="block">
                                <div class="title">Pruebas serológicas</div>
                                <div class="body">
                                    <table class="tbl">
                                        <thead>
                                        <tr>
                                            <th style="width:36%;">Prueba</th>
                                            <th style="width:20%;">Res</th>
                                            <th style="width:28%;">Rango / Interpretación</th>
                                            <th style="width:16%;">Unid</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @php
                                            $sero = [
                                              ['ASO','aso'],
                                              ['FR','fr'],
                                              ['PCR','pcr'],
                                              ['Prueba rápida de VIH','prueba_rapida_vih'],
                                              ['RPR','rpr'],
                                              ['Reacción de Widal','reaccion_widal'],
                                              ['D.C.E.','dce'],
                                            ];
                                        @endphp
                                        @foreach($sero as [$label,$field])
                                            <tr>
                                                <td>{{ $label }}</td>
                                                <td class="center">{{ val($q,$field) }}</td>
                                                <td class="center">{{ rangoTexto($label,$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad($label,$rangosMap) }}</td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Observaciones / Metodo / Equipo -->
                            <div class="block">
                                <div class="title">Observaciones / Método / Equipo</div>
                                <div class="body">
                                    <table class="tbl">
                                        <tr>
                                            <td style="width:14%" class="bold">Observaciones</td>
                                            <td style="width:86%">{{ val($q,'observaciones') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="bold">Método</td>
                                            <td>{{ val($q,'metodo') }}</td>
                                        </tr>
                                        <tr>
                                            <td class="bold">Equipo</td>
                                            <td>{{ val($q,'equipo') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <!-- Firmas -->
                            <table class="no-border" style="margin-top:4px;">
                                <tr>
                                    <td class="center" style="width:33%">
                                        _____________________<br><span class="small muted">Firma</span>
                                    </td>
                                    <td class="center" style="width:33%">
                                        _____________________<br><span class="small muted">Bioquímico(a)</span>
                                    </td>
                                    <td class="center" style="width:34%">
                                        @if(!empty($qrSvgBase64))
                                            <img
                                                src="data:image/svg+xml;base64,{{ $qrSvgBase64 }}"
                                                style="width:45px; height:45px;"
                                                alt="QR"
                                            >
                                        @endif
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>
                </table>
            </td>
            @endforeach
    </tr>
</table>
</body>
</html>
