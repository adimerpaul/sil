<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">

    <style>

        * { box-sizing: border-box; }

        body{
            margin:0; padding:0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 7px;
            line-height: 1.08;
            color:#111;
        }

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

        .w-analito{ width:44%; }
        .w-res{ width:16%; }
        .w-unid{ width:14%; }
        .w-rango{ width:26%; }
    </style>
</head>

<body>
@php
    // Esperado: $solicitud, $quimica, $rangos
    $q = $quimica ?? $q ?? null;

    /* =========================
       MAPA DE RANGOS
       ========================= */
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

    /* =========================
       HELPERS: SERVICIOS (igual que el front)
       ========================= */
    $norm = function($v){
        $v = (string)($v ?? '');
        $v = preg_replace('/\s+/u', ' ', trim($v));
        return mb_strtolower($v);
    };

    $canServicios = function($can) use ($solicitud, $norm){
        $servicios = $solicitud->servicios ?? [];
        if(!is_iterable($servicios)) return false;

        $targets = is_array($can) ? $can : [$can];
        $wanted = array_map($norm, $targets);

        foreach ($servicios as $s) {
            $sn = $norm($s->nombre ?? '');
            if (in_array($sn, $wanted, true)) return true;
        }
        return false;
    };

    $hasAnyServicios = function($list) use ($canServicios){
        $arr = is_array($list) ? $list : [$list];
        foreach($arr as $x){
            if($canServicios($x)) return true;
        }
        return false;
    };

    /* =========================
       FLAGS (mismas listas que el front)
       ========================= */
    $showBasica = $hasAnyServicios([
        'ÁCIDO ÚRICO',
        'ALBUMINA',
        'PROTEINAS TOTALES',
        'GLICEMIA',
        'UREA',
        'NITROGENO UREICO SERICO (NUS)',
        'CREATININA SÉRICA',
        'PERFIL RENAL (CREATININA SÉRICA, ÁCIDO ÚRICO, UREA)',
        'PROTEINOGRAMA (PROTEÍNAS TOTALES, ALBÚMINA, GLOBULINA)',
        'CLEARENCE DE CREATININA'
    ]);

    $showHepatico = $hasAnyServicios([
        'BILIRRUBINAS TOTALES Y FRACCIONADAS',
        'TRANSAMINASAS GOT',
        'TRANSAMINASAS GPT',
        'FOSFATASA ALCALINA',
        'GAMA GLUTAMIL TRANSFERASA (GGT)',
        'AMILASA',
        'PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)'
    ]);

    $showLipidico = $hasAnyServicios([
        'COLESTEROL',
        'TRIGLICÉRIDOS',
        'HDLc, LDLc, VLDLc',
        'PERFIL LIPÍDICO O LIPIDOGRAMA (COLESTEROL, TRIGLICERIDOS, HDLc,LDLc,VLDLc)'
    ]);

    $showElectro = $hasAnyServicios([
        'ELECTROLITOS (SODIO, POTASIO, CLORO) (NA,K,CL)',
        'IONOGRAMA (NA,K,CL,CA,Mg,P)',
        'CALCIO',
        'FÓSFORO',
        'MAGNESIO',
        'HIERRO'
    ]);

    $showOrina24 = $hasAnyServicios([
        'CREATININA EN ORINA (CREATINURIA)',
        'PROTEINURIA 24 HRS',
        'CLEARENCE DE CREATININA'
    ]);

    $showOtros = $hasAnyServicios([
        'CK TOTAL',
        'CK MB',
        'LACTATO DESHIDROGENASA ( LDH )',
        'LIPASA'
    ]);

    $showGluco = $hasAnyServicios([
        'HEMOGLOBINA GLICOSILADA A1c'
    ]);

    $showSero = $hasAnyServicios([
        'ASTO O ASO',
        'FACTOR REUMATOIDEO (FR)',
        'PCR CUALITATIVO (PROTEÍNA C REACTIVA)',
        'PRUEBA RAPIDA PARA VIH',
        'PRUEBA RAPIDA PARA SIFILIS',
        'PRUEBA RAPIDA PARA CHAGAS',
        'PRUEBA RAPIDA PARA HEPATITIS B',
        'PRUEBA RAPIDA PARA HEPATITIS C',
        'PRUEBA RAPIDA PARA TROPONINA',
        'REACCIÓN DE WIDAL',
        'RPR- VDRL',
        'TEST DE EMBARAZO EN SUERO (GONADOTROFINA CORIÓNICA HUMANA CUALITATIVO)',
        'CLEARENCE DE CREATININA'
    ]);

    $showObs = $hasAnyServicios([
        'PERFIL RENAL (CREATININA SÉRICA, ÁCIDO ÚRICO, UREA)',
        'PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)',
        'PERFIL LIPÍDICO O LIPIDOGRAMA (COLESTEROL, TRIGLICERIDOS, HDLc,LDLc,VLDLc)',
        'IONOGRAMA (NA,K,CL,CA,Mg,P)',
        'ELECTROLITOS (SODIO, POTASIO, CLORO) (NA,K,CL)',
        'ÁCIDO ÚRICO','ALBUMINA','PROTEINAS TOTALES','GLICEMIA','UREA','NITROGENO UREICO SERICO (NUS)','CREATININA SÉRICA',
        'BILIRRUBINAS TOTALES Y FRACCIONADAS','TRANSAMINASAS GOT','TRANSAMINASAS GPT','FOSFATASA ALCALINA','GAMA GLUTAMIL TRANSFERASA (GGT)','AMILASA',
        'COLESTEROL','TRIGLICÉRIDOS','HDLc, LDLc, VLDLc','HEMOGLOBINA GLICOSILADA A1c',
        'ASTO O ASO','FACTOR REUMATOIDEO (FR)','PCR CUALITATIVO (PROTEÍNA C REACTIVA)','PRUEBA RAPIDA PARA VIH','RPR- VDRL','REACCIÓN DE WIDAL'
    ]);
    $showCito = $hasAnyServicios([
    'CITOQUÍMICO LÍQUIDO CEFALORRAQUÍDEO Y OTROS LÍQUIDOS'
]);

@endphp

<table>
    <tr>
{{--        @foreach(['izq','der'] as $side)--}}
{{--        @endforeach--}}
        <td style="width:50%; vertical-align:top; padding:0 4px;">

            {!! view('components.headerSinCabeceraPequeno', ['solicitud' => $solicitud, 'fecha_solicitud'=>$q->created_at])->render() !!}

            <div class="center bold" style="font-size:12px; margin:2px 0; margin-top: 5px">QUÍMICA SANGUÍNEA</div>
            <div class="center small muted">
                Método: {{ val($q,'metodo') ?: '—' }} &nbsp; • &nbsp;
                Equipo: {{ $q->equipo == 'Otros' ? $q->equipo_otro : $q->equipo ?? '—' }}
            </div>

            <!-- ===================== 2 COLUMNAS PRINCIPALES ===================== -->
            <table class="layout">
                <tr>

                    <!-- IZQUIERDA -->
                    <td class="col">

                        {{-- ===================== QUÍMICA BÁSICA / RENAL ===================== --}}
                        @if($showBasica)
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

                                        @if($canServicios(['ÁCIDO ÚRICO','PERFIL RENAL (CREATININA SÉRICA, ÁCIDO ÚRICO, UREA)','GLUCOSA']))
                                            <tr>
                                                <td>Ácido Úrico</td>
                                                <td class="center">{{ val($q,'acido_urico') }}</td>
                                                <td class="center">{{ rangoTexto('Acido Urico',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Acido Urico',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['ALBUMINA','PROTEINOGRAMA (PROTEÍNAS TOTALES, ALBÚMINA, GLOBULINA)']))
                                            <tr>
                                                <td>Albúmina</td>
                                                <td class="center">{{ val($q,'albumina') }}</td>
                                                <td class="center">{{ rangoTexto('Albumina',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Albumina',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['PROTEINAS TOTALES','PROTEINOGRAMA (PROTEÍNAS TOTALES, ALBÚMINA, GLOBULINA)']))
                                            <tr>
                                                <td>Proteínas totales</td>
                                                <td class="center">{{ val($q,'proteinas_totales') }}</td>
                                                <td class="center">{{ rangoTexto('Proteinas totales',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Proteinas totales',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios('PROTEINOGRAMA (PROTEÍNAS TOTALES, ALBÚMINA, GLOBULINA)'))
                                            <tr>
                                                <td>Globulina</td>
                                                <td class="center">{{ val($q,'globulina') }}</td>
                                                <td class="center">{{ rangoTexto('Globulina',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Globulina',$rangosMap) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Relación A/G</td>
                                                <td class="center">{{ val($q,'relacion_ag') }}</td>
                                                <td class="center">{{ rangoTexto('Relación A/G',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Relación A/G',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['GLICEMIA','PRUEBA DE TOLERANCIA A LA GLUCOSA (3 MEDICIONES) (PTG)','PRUEBA DE TOLERANCIA A LA GLUCOSA (4 MEDICIONES) (PTG)']))
                                            <tr>
                                                <td>Glucosa</td>
                                                <td class="center">{{ val($q,'glucosa') }}</td>
                                                <td class="center">{{ rangoTexto('Glucosa',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Glucosa',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['UREA','PERFIL RENAL (CREATININA SÉRICA, ÁCIDO ÚRICO, UREA)']))
                                            <tr>
                                                <td>Urea</td>
                                                <td class="center">{{ val($q,'urea') }}</td>
                                                <td class="center">{{ rangoTexto('Urea',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Urea',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['NITROGENO UREICO SERICO (NUS)','GLUCOSA']))
                                            <tr>
                                                <td>NUS</td>
                                                <td class="center">{{ val($q,'nus') }}</td>
                                                <td class="center">{{ rangoTexto('NUS',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('NUS',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['CREATININA SÉRICA','PERFIL RENAL (CREATININA SÉRICA, ÁCIDO ÚRICO, UREA)','CLEARENCE DE CREATININA']))
                                            <tr>
                                                <td>Creatinina</td>
                                                <td class="center">{{ val($q,'creatinina') }}</td>
                                                <td class="center">{{ rangoTexto('Creatinina',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Creatinina',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- ===================== PERFIL LIPÍDICO ===================== --}}
                        @if($showLipidico)
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

                                        @if($canServicios(['COLESTEROL','PERFIL LIPÍDICO O LIPIDOGRAMA (COLESTEROL, TRIGLICERIDOS, HDLc,LDLc,VLDLc)']))
                                            <tr>
                                                <td>Colesterol total</td>
                                                <td class="center">{{ val($q,'colesterol_total') }}</td>
                                                <td class="center">{{ rangoTexto('Colesterol total',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Colesterol total',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['TRIGLICÉRIDOS','PERFIL LIPÍDICO O LIPIDOGRAMA (COLESTEROL, TRIGLICERIDOS, HDLc,LDLc,VLDLc)']))
                                            <tr>
                                                <td>Triglicéridos</td>
                                                <td class="center">{{ val($q,'trigliceridos') }}</td>
                                                <td class="center">{{ rangoTexto('Triglicéridos',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Triglicéridos',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['HDLc, LDLc, VLDLc','PERFIL LIPÍDICO O LIPIDOGRAMA (COLESTEROL, TRIGLICERIDOS, HDLc,LDLc,VLDLc)']))
                                            <tr>
                                                <td>HDL</td>
                                                <td class="center">{{ val($q,'hdl_colesterol') }}</td>
                                                <td class="center">{{ rangoTexto('HDL Colesterol',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('HDL Colesterol',$rangosMap) }}</td>
                                            </tr>
                                            <tr>
                                                <td>LDL</td>
                                                <td class="center">{{ val($q,'ldl_colesterol') }}</td>
                                                <td class="center">{{ rangoTexto('LDL Colesterol',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('LDL Colesterol',$rangosMap) }}</td>
                                            </tr>
                                            <tr>
                                                <td>VLDL</td>
                                                <td class="center">{{ val($q,'vldl_colesterol') }}</td>
                                                <td class="center">{{ rangoTexto('VLDL Colesterol',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('VLDL Colesterol',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- ===================== OTROS (CK/LDH/LIPASA) ===================== --}}
                        @if($showOtros)
                            <div class="block">
                                <div class="title">Otros</div>
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
                                        @if($canServicios('CK TOTAL'))
                                            <tr>
                                                <td>CK-Total</td>
                                                <td class="center">{{ val($q,'ck_total') }}</td>
                                                <td class="center">{{ rangoTexto('CK-Total',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('CK-Total',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios('CK MB'))
                                            <tr>
                                                <td>CK MB</td>
                                                <td class="center">{{ val($q,'ck_mb') }}</td>
                                                <td class="center">{{ rangoTexto('CK MB',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('CK MB',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios('LACTATO DESHIDROGENASA ( LDH )'))
                                            <tr>
                                                <td>LDH</td>
                                                <td class="center">{{ val($q,'ldh') }}</td>
                                                <td class="center">{{ rangoTexto('LDH',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('LDH',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios('LIPASA'))
                                            <tr>
                                                <td>Lipasa</td>
                                                <td class="center">{{ val($q,'lipasa') }}</td>
                                                <td class="center">{{ rangoTexto('Lipasa',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Lipasa',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                    </td>

                    <!-- DERECHA -->
                    <td class="col">

                        {{-- ===================== HEPÁTICO ===================== --}}
                        @if($showHepatico)
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

                                        @if($canServicios(['BILIRRUBINAS TOTALES Y FRACCIONADAS','PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)']))
                                            <tr>
                                                <td>Bilirrubina Total</td>
                                                <td class="center">{{ val($q,'bilirrubina_total') }}</td>
                                                <td class="center">{{ rangoTexto('Bilirrubina Total',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Bilirrubina Total',$rangosMap) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Bilirrubina Directa</td>
                                                <td class="center">{{ val($q,'bilirrubina_directa') }}</td>
                                                <td class="center">{{ rangoTexto('Bilirrubina Directa',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Bilirrubina Directa',$rangosMap) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Bilirrubina Indirecta</td>
                                                <td class="center">{{ val($q,'bilirrubina_indirecta') }}</td>
                                                <td class="center">{{ rangoTexto('Bilirrubina Indirecta',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Bilirrubina Indirecta',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['TRANSAMINASAS GOT','PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)']))
                                            <tr>
                                                <td>G.O.T. (TGO)</td>
                                                <td class="center">{{ val($q,'got') }}</td>
                                                <td class="center">{{ rangoTexto('G.O.T. (TGO)',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('G.O.T. (TGO)',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['TRANSAMINASAS GPT','PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)']))
                                            <tr>
                                                <td>G.P.T. (TGP)</td>
                                                <td class="center">{{ val($q,'gpt') }}</td>
                                                <td class="center">{{ rangoTexto('G.P.T. (TGP)',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('G.P.T. (TGP)',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['FOSFATASA ALCALINA','PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)']))
                                            <tr>
                                                <td>Fosfatasa Alcalina</td>
                                                <td class="center">{{ val($q,'fosfatasa_alcalina') }}</td>
                                                <td class="center">{{ rangoTexto('Fosfatasa Alcalina',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Fosfatasa Alcalina',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['GAMA GLUTAMIL TRANSFERASA (GGT)','PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)']))
                                            <tr>
                                                <td>GGT</td>
                                                <td class="center">{{ val($q,'ggt') }}</td>
                                                <td class="center">{{ rangoTexto('GGT',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('GGT',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios('AMILASA'))
                                            <tr>
                                                <td>Amilasa</td>
                                                <td class="center">{{ val($q,'amilasa') }}</td>
                                                <td class="center">{{ rangoTexto('Amilasa',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Amilasa',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- ===================== ELECTROLITOS / MINERALES ===================== --}}
                        @if($showElectro)
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

                                        @if($canServicios(['ELECTROLITOS (SODIO, POTASIO, CLORO) (NA,K,CL)','IONOGRAMA (NA,K,CL,CA,Mg,P)']))
                                            <tr>
                                                <td>Sodio</td>
                                                <td class="center">{{ val($q,'sodio') }}</td>
                                                <td class="center">{{ rangoTexto('Sodio',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Sodio',$rangosMap) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Potasio</td>
                                                <td class="center">{{ val($q,'potasio') }}</td>
                                                <td class="center">{{ rangoTexto('Potasio',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Potasio',$rangosMap) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Cloro</td>
                                                <td class="center">{{ val($q,'cloro') }}</td>
                                                <td class="center">{{ rangoTexto('Cloro',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Cloro',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['CALCIO','IONOGRAMA (NA,K,CL,CA,Mg,P)']))
                                            <tr>
                                                <td>Calcio</td>
                                                <td class="center">{{ val($q,'calcio') }}</td>
                                                <td class="center">{{ rangoTexto('Calcio',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Calcio',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['FÓSFORO','IONOGRAMA (NA,K,CL,CA,Mg,P)']))
                                            <tr>
                                                <td>Fósforo</td>
                                                <td class="center">{{ val($q,'fosforo') }}</td>
                                                <td class="center">{{ rangoTexto('Fósforo',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Fósforo',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['MAGNESIO','IONOGRAMA (NA,K,CL,CA,Mg,P)']))
                                            <tr>
                                                <td>Magnesio</td>
                                                <td class="center">{{ val($q,'magnesio') }}</td>
                                                <td class="center">{{ rangoTexto('Magnesio',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Magnesio',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios('HIERRO'))
                                            <tr>
                                                <td>Hierro sérico</td>
                                                <td class="center">{{ val($q,'hierro_serico') }}</td>
                                                <td class="center">{{ rangoTexto('Hierro sérico',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Hierro sérico',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                    </td>
                </tr>
            </table>

            <!-- ===================== BLOQUES ABAJO ===================== -->
            <table class="layout" style="margin-top:3px;">
                <tr>
                    <td style="width:100%; padding:0 5px; vertical-align:top;">

                        {{-- ORINA 24H + GLUCÉMICO (en 2 columnas) --}}
                        @if($showOrina24 || $showGluco)
                            <table class="layout">
                                <tr>

                                    <td class="col">
                                        @if($showOrina24)
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

                                                        @if($canServicios('CREATININA EN ORINA (CREATINURIA)'))
                                                            <tr>
                                                                <td>Creatinuria 24 hrs.</td>
                                                                <td class="center">{{ val($q,'creatinuria_24h') }}</td>
                                                                <td class="center">{{ rangoTexto('Creatinuria 24 hrs.',$rangosMap) }}</td>
                                                                <td class="center">{{ rangoUnidad('Creatinuria 24 hrs.',$rangosMap) }}</td>
                                                            </tr>
                                                        @endif

                                                        @if($canServicios('PROTEINURIA 24 HRS'))
                                                            <tr>
                                                                <td>Proteinuria de 24 hrs.</td>
                                                                <td class="center">{{ val($q,'proteinuria_24h') }}</td>
                                                                <td class="center">{{ rangoTexto('Proteinuria de 24 hrs.',$rangosMap) }}</td>
                                                                <td class="center">{{ rangoUnidad('Proteinuria de 24 hrs.',$rangosMap) }}</td>
                                                            </tr>
                                                        @endif

                                                        @if($canServicios(['PROTEINURIA 24 HRS','CREATININA EN ORINA (CREATINURIA)','CLEARENCE DE CREATININA']))
                                                            <tr>
                                                                <td>Volumen 24 h</td>
                                                                <td class="center">{{ val($q,'volumen_24h') }}</td>
                                                                <td class="center">{{ rangoTexto('VOLUMEN',$rangosMap) }}</td>
                                                                <td class="center">{{ rangoUnidad('VOLUMEN',$rangosMap) }}</td>
                                                            </tr>
                                                        @endif

                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
                                    </td>

                                    <td class="col">
                                        @if($showGluco)
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
                                                        @if($canServicios('HEMOGLOBINA GLICOSILADA A1c'))
                                                            <tr>
                                                                <td>Hb Glicosilada</td>
                                                                <td class="center">{{ val($q,'hb_glicosilada') }}</td>
                                                                <td class="center">{{ rangoTexto('Hb Glicosilada',$rangosMap) }}</td>
                                                                <td class="center">{{ rangoUnidad('Hb Glicosilada',$rangosMap) }}</td>
                                                            </tr>
                                                            <tr>
                                                                <td>Hb A1C</td>
                                                                <td class="center">{{ val($q,'hb_a1c') }}</td>
                                                                <td class="center">{{ rangoTexto('Hb A1C',$rangosMap) }}</td>
                                                                <td class="center">{{ rangoUnidad('Hb A1C',$rangosMap) }}</td>
                                                            </tr>
                                                        @endif
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                        @endif
                                    </td>

                                </tr>
                            </table>
                        @endif

                        {{-- ===================== SEROLÓGICOS ===================== --}}
                        @if($showSero)
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

                                        @if($canServicios('ASTO O ASO'))
                                            <tr>
                                                <td>ASO</td>
                                                <td class="center">{{ val($q,'aso') }}</td>
                                                <td class="center">{{ rangoTexto('ASO',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('ASO',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios('FACTOR REUMATOIDEO (FR)'))
                                            <tr>
                                                <td>FR</td>
                                                <td class="center">{{ val($q,'fr') }}</td>
                                                <td class="center">{{ rangoTexto('FR',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('FR',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['PCR CUALITATIVO (PROTEÍNA C REACTIVA)','TEST DE EMBARAZO EN SUERO (GONADOTROFINA CORIÓNICA HUMANA CUALITATIVO)']))
                                            <tr>
                                                <td>PCR</td>
                                                <td class="center">{{ val($q,'pcr') }}</td>
                                                <td class="center">{{ rangoTexto('PCR',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('PCR',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['PRUEBA RAPIDA PARA VIH','TEST DE EMBARAZO EN SUERO (GONADOTROFINA CORIÓNICA HUMANA CUALITATIVO)']))
                                            <tr>
                                                <td>Prueba rápida VIH</td>
                                                <td class="center">{{ val($q,'prueba_rapida_vih') }}</td>
                                                <td class="center">{{ rangoTexto('Prueba rápida VIH',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Prueba rápida VIH',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios(['RPR- VDRL','TEST DE EMBARAZO EN SUERO (GONADOTROFINA CORIÓNICA HUMANA CUALITATIVO)']))
                                            <tr>
                                                <td>RPR / VDRL</td>
                                                <td class="center">{{ val($q,'rpr') }}</td>
                                                <td class="center">{{ rangoTexto('RPR / VDRL',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('RPR / VDRL',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios('REACCIÓN DE WIDAL'))
                                            <tr>
                                                <td>Reacción de Widal</td>
                                                <td class="center">{{ val($q,'reaccion_widal') }}</td>
                                                <td class="center">{{ rangoTexto('Reacción de Widal',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Reacción de Widal',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canServicios('CLEARENCE DE CREATININA'))
                                            <tr>
                                                <td>DCE</td>
                                                <td class="center">{{ val($q,'dce') }}</td>
                                                <td class="center">{{ rangoTexto('DCE',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('DCE',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- ===================== OBSERVACIONES ===================== --}}
                        @if($showObs)
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
                        @endif
{{--                        @if($showCito)--}}
{{--                            <div class="block">--}}
{{--                                <div class="title">Citoquímico</div>--}}
{{--                                <div class="body">--}}
{{--                                    <table class="tbl">--}}
{{--                                        <thead>--}}
{{--                                        <tr>--}}
{{--                                            <th style="width: 10px">Parámetro</th>--}}
{{--                                            <th class="w-res">Res</th>--}}
{{--                                            <th class="w-rango">Rango</th>--}}
{{--                                            <th class="w-unid">Unid</th>--}}
{{--                                        </tr>--}}
{{--                                        </thead>--}}
{{--                                        <tbody>--}}

{{--                                        @if($canServicios('CITOQUÍMICO LÍQUIDO CEFALORRAQUÍDEO Y OTROS LÍQUIDOS'))--}}
{{--                                            <tr>--}}
{{--                                                <td>Cantidad (ml)</td>--}}
{{--                                                <td class="center">{{ val($q,'citoquimico_cantidad') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('Cantidad (ml)',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('Cantidad (ml)',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}

{{--                                            <tr>--}}
{{--                                                <td>Color</td>--}}
{{--                                                <td class="center">{{ val($q,'citoquimico_color') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('Color',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('Color',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}

{{--                                            <tr>--}}
{{--                                                <td>Aspecto</td>--}}
{{--                                                <td class="center">{{ val($q,'citoquimico_aspecto') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('Aspecto',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('Aspecto',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}

{{--                                            <tr>--}}
{{--                                                <td>Glucosa</td>--}}
{{--                                                <td class="center">{{ val($q,'citoquimico_glucosa') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('Glucosa',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('Glucosa',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}

{{--                                            <tr>--}}
{{--                                                <td>pH</td>--}}
{{--                                                <td class="center">{{ val($q,'citoquimico_ph') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('pH',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('pH',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}

{{--                                            <tr>--}}
{{--                                                <td>Proteínas totales</td>--}}
{{--                                                <td class="center">{{ val($q,'citoquimico_proteinas_totales') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('Proteínas totales',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('Proteínas totales',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}
{{--                                            <tr>--}}
{{--                                                densidad--}}
{{--                                                <td>Densidad</td>--}}
{{--                                                <td class="center">{{ val($q,'citoquimico_densidad') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('Densidad',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('Densidad',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}

{{--                                            <tr>--}}
{{--                                                <td>Albúmina</td>--}}
{{--                                                <td class="center">{{ val($q,'citoquimico_albumina') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('Albumina',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('Albumina',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}

{{--                                            <tr>--}}
{{--                                                <td>LDH</td>--}}
{{--                                                <td class="center">{{ val($q,'citoquimico_ldh') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('LDH',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('LDH',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}

{{--                                            <tr>--}}
{{--                                                <td>Glóbulos blancos</td>--}}
{{--                                                <td class="center">{{ val($q,'citoquimico_globulos_blancos') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('Glóbulos blancos',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('Glóbulos blancos',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}

{{--                                            <tr>--}}
{{--                                                <td>Polimorfonucleares (%)</td>--}}
{{--                                                <td class="center">{{ val($q,'citoquimico_polimorfonucleares') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('Polimorfonucleares (%)',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('Polimorfonucleares (%)',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}

{{--                                            <tr>--}}
{{--                                                <td>Mononucleares (%)</td>--}}
{{--                                                <td class="center">{{ val($q,'citoquimico_mononucleares') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('Mononucleares (%)',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('Mononucleares (%)',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}
{{--                                        @endif--}}

{{--                                        </tbody>--}}
{{--                                    </table>--}}
{{--                                </div>--}}
{{--                            </div>--}}
{{--                        @endif--}}


                        {{-- ===================== FIRMAS + QR ===================== --}}
                        <table class="no-border" style="margin-top:4px;">
                            <tr>
                                <td class="center" style="width:33%">
                                    _____________________<br><span class="small muted">Firma</span>
                                </td>
                                <td class="center" style="width:33%">
                                    _____________________<br>
                                    <span class="small muted">
                                        {{$q->user? $q->user->name:'---'}} <br>
                                        Bioquímico(a)
                                    </span>
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
    </tr>
</table>

</body>
</html>
