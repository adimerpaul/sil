<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">

    <style>

        * { box-sizing: border-box; }

        body{
            margin:0; padding:0;
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
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
            /*border:1px solid #111;*/
            /*margin:3px 0;*/
        }
        .block .title{
            /*background:#f2f2f2;*/
            /*border-bottom:1px solid #111;*/
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
            font-size: 9px;
        }
        .tbl td{ font-size: 9px; }

        .w-analito{ width:44%; }
        .w-res{ width:16%; }
        .w-unid{ width:14%; }
        .w-rango{ width:26%; }
        .out-range{ color:#c10015; font-weight:700; }
    </style>
</head>

<body>
@php
    // Esperado: $solicitud, $quimica, $rangos
    $q = $quimica ?? $q ?? null;

    /* =========================
       MAPA DE RANGOS (normaliza acentos)
       ========================= */
    $normalizeRango = function($s) {
        $s = mb_strtolower(trim((string)($s ?? '')));
        if (function_exists('iconv')) {
            $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
            if ($t !== false && $t !== '') $s = $t;
        }
        // iconv//TRANSLIT deja marcas sueltas (ó => 'o, ñ => ~n) según la plataforma
        $s = preg_replace('/[`\'"^~]/u', '', $s);
        return preg_replace('/\s+/u', ' ', $s);
    };
    $rangosMap = [];
    foreach(($rangos ?? []) as $r){
        $key = $normalizeRango($r->rango_nombre ?? '');
        if($key) $rangosMap[$key] = $r;
    }

    if (!function_exists('keyNom')) {
        function keyNom($s){
            $s = mb_strtolower(trim((string)($s ?? '')));
            if (function_exists('iconv')) {
                $t = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $s);
                if ($t !== false && $t !== '') $s = $t;
            }
            // iconv//TRANSLIT deja marcas sueltas (ó => 'o, ñ => ~n) según la plataforma
            $s = preg_replace('/[`\'"^~]/u', '', $s);
            return preg_replace('/\s+/u', ' ', $s);
        }
    }

    function rangoTexto($name, $map){
        $k = keyNom($name);
        if(!isset($map[$k])) return '';
        $r = $map[$k];
        if($r->interpretacion) return $r->interpretacion;
        if($r->rango_minimo !== null && $r->rango_maximo !== null){
            return $r->rango_minimo.' - '.$r->rango_maximo;
        }
        return '';
    }

    function outOfRangeQ($name, $valor, $map){
        if($valor === null || $valor === '') return false;
        $k = keyNom($name);
        if(!isset($map[$k])) return false;
        $r = $map[$k];
        $num = floatval($valor);
        if(!is_null($r->rango_minimo) && $num < $r->rango_minimo) return true;
        if(!is_null($r->rango_maximo) && $num > $r->rango_maximo) return true;
        return false;
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

    function hasVal($obj, $field){
        if(!$obj) return false;
        return isset($obj->$field) && $obj->$field !== '';
    }

    function valorConFormato($obj, $field, $suffix = ''){
        if(!$obj || !isset($obj->$field) || $obj->$field === '') return '';
        return trim($obj->$field . ($suffix ? ' ' . $suffix : ''));
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
       HELPERS: VISIBILIDAD POR CONFIGURACIÓN (datos_quimica_sanguinea)
       Mismo criterio que la captura en el front:
       - Sin dato configurado o sin prestaciones asignadas => siempre visible.
       - Con prestaciones asignadas => visible solo si la solicitud incluye alguna.
       - Dato inactivo => nunca visible.
       ========================= */
    $datosConfig = collect($datos ?? []);
    $servicioIdsSolicitud = collect($solicitud->servicios ?? [])->pluck('id')->all();

    $canVariable = function($variable) use ($datosConfig, $servicioIdsSolicitud){
        $dato = $datosConfig->firstWhere('variable', $variable);
        if(!$dato) return true;
        if(!$dato->activo) return false;

        $ids = collect($dato->prestaciones ?? [])->pluck('id')->all();
        if(!count($ids)) return true;

        return count(array_intersect($ids, $servicioIdsSolicitud)) > 0;
    };

    $canAny = function($variables) use ($canVariable){
        foreach((array) $variables as $v){
            if($canVariable($v)) return true;
        }
        return false;
    };

    /* =========================
       FLAGS (mismas listas que el front)
       ========================= */
    $showBasica = $canAny([
        'acido_urico','albumina','proteinas_totales','glucosa','urea','nus','creatinina','globulina','relacion_ag'
    ]);

    $showHepatico = $canAny([
        'bilirrubina_total','bilirrubina_directa','bilirrubina_indirecta','got','gpt','fosfatasa_alcalina','ggt','amilasa'
    ]);

    $showLipidico = $canAny([
        'trigliceridos','colesterol_total','hdl_colesterol','ldl_colesterol','vldl_colesterol'
    ]);

    $showElectro = $canAny([
        'sodio','potasio','cloro','calcio','fosforo','magnesio','hierro_serico','trf'
    ]);

    $showOrina24 = $canAny([
        'creatinuria_24h','creatinuria_casual','proteinuria_24h','volumen_24h','dce','microalbuminuria'
    ]);

    $showOtros = $canAny([
        'ck_total','ck_mb','ldh','lipasa'
    ]);

    $showGluco = $canAny(['hb_glicosilada']);

    $showSero = $canAny([
        'aso','fr','pcr','test_embarazo',
        'prueba_rapida_hepatitis_b','prueba_rapida_hepatitis_c','prueba_rapida_chagas',
        'prueba_rapida_vih','prueba_rapida_sifilis','prueba_rapida_troponina','rpr',
        'reaccion_widal_o','reaccion_widal_h','reaccion_widal_a','reaccion_widal_b'
    ]);

    $showGasometria = $canAny(['gasometria_tipo','gasometria_muestra_estado']);

    $showObs = $hasAnyServicios([
        'PERFIL RENAL (CREATININA SÉRICA, ÁCIDO ÚRICO, UREA)',
        'PERFIL HEPÁTICO O HEPATOGRAMA (BILIRRUBINAS TOTALES Y FRACCIONADAS, FOSFATASA ALCALINA, GOT, GPT, GGT, TP)',
        'PERFIL LIPÍDICO O LIPIDOGRAMA (COLESTEROL, TRIGLICERIDOS, HDLc,LDLc,VLDLc)',
        'IONOGRAMA (NA,K,CL,CA,Mg,P)',
        'ELECTROLITOS (SODIO, POTASIO, CLORO)',
        'ÁCIDO ÚRICO','ALBUMINA','PROTEINAS TOTALES','GLICEMIA','UREA','NITROGENO UREICO SERICO (NUS)','CREATININA SÉRICA',
        'BILIRRUBINAS TOTALES Y FRACCIONADAS','TRANSAMINASAS GOT- (ALT)','TRANSAMINASAS GPT','FOSFATASA ALCALINA','GAMA GLUTAMIL TRANSFERASA (GGT)','AMILASA',
        'COLESTEROL','TRIGLICÉRIDOS','HDLc, LDLc, VLDLc','HEMOGLOBINA GLICOSILADA A1c',
        'ASTO O ASO','FACTOR REUMATOIDEO (FR)','PCR CUALITATIVO (PROTEÍNA C REACTIVA)','PRUEBA RAPIDA PARA VIH','RPR- VDRL','REACCIÓN DE WIDAL'
    ]);
    $showCito = $canAny([
        'tipo_de_muestra','citoquimico_cantidad','citoquimico_color','citoquimico_aspecto',
        'citoquimico_ph','citoquimico_densidad','citoquimico_glucosa','citoquimico_proteinas_totales',
        'citoquimico_ldh','citoquimico_globulos_blancos','citoquimico_polimorfonucleares',
        'citoquimico_mononucleares','citoquimico_observaciones'
    ]);
    $hasCitoData =
        hasVal($q, 'citoquimico_cantidad') ||
        hasVal($q, 'citoquimico_color') ||
        hasVal($q, 'citoquimico_aspecto') ||
        hasVal($q, 'citoquimico_glucosa') ||
        hasVal($q, 'citoquimico_ph') ||
        hasVal($q, 'citoquimico_proteinas_totales') ||
        hasVal($q, 'citoquimico_densidad') ||
        hasVal($q, 'citoquimico_albumina') ||
        hasVal($q, 'citoquimico_ldh') ||
        hasVal($q, 'citoquimico_globulos_blancos') ||
        hasVal($q, 'citoquimico_polimorfonucleares') ||
        hasVal($q, 'citoquimico_mononucleares') ||
        hasVal($q, 'tipo_de_muestra');

@endphp

<table>
    <tr>
{{--        @foreach(['izq','der'] as $side)--}}
{{--        @endforeach--}}
        <td style="width:50%; vertical-align:top; padding:0 4px;">

            <div style="margin-top:-30px;">
                {!! view('components.headerSinCabeceraPequeno', ['solicitud' => $solicitud, 'fecha_solicitud'=>$q->created_at])->render() !!}
            </div>

            <div class="center bold" style="font-size:12px; margin:2px 0; margin-top: 5px">QUÍMICA SANGUÍNEA</div>
            <div class="center small muted">
                Método: {{ val($q,'metodo') ?: '—' }} &nbsp; • &nbsp;
                Equipo: {{ $q->equipo == 'Otros' ? $q->equipo_otro : $q->equipo ?? '—' }}
                @if(val($q,'metodo2') || val($q,'equipo2'))
                &nbsp; | &nbsp;
                Método 2: {{ val($q,'metodo2') ?: '—' }} &nbsp; • &nbsp;
                Equipo 2: {{ val($q,'equipo2') ?: '—' }}
                @endif
                @if(val($q,'metodo3') || val($q,'equipo3'))
                &nbsp; | &nbsp;
                Método 3: {{ val($q,'metodo3') ?: '—' }} &nbsp; • &nbsp;
                Equipo 3: {{ val($q,'equipo3') ?: '—' }}
                @endif
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

                                        @if($canVariable('acido_urico'))
                                            <tr>
                                                <td>Ácido Úrico</td>
                                                <td class="center {{ outOfRangeQ('Acido Urico',val($q,'acido_urico'),$rangosMap)?'out-range':'' }}">{{ val($q,'acido_urico') }}</td>
                                                <td class="center">{{ rangoTexto('Acido Urico',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Acido Urico',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('albumina'))
                                            <tr>
                                                <td>Albúmina</td>
                                                <td class="center {{ outOfRangeQ('Albumina',val($q,'albumina'),$rangosMap)?'out-range':'' }}">{{ val($q,'albumina') }}</td>
                                                <td class="center">{{ rangoTexto('Albumina',$rangosMap) ?: '3.5 - 5.3' }}</td>
                                                <td class="center">{{ rangoUnidad('Albumina',$rangosMap) ?: 'g/dl' }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('proteinas_totales'))
                                            <tr>
                                                <td>Proteínas totales</td>
                                                <td class="center {{ outOfRangeQ('Proteinas totales',val($q,'proteinas_totales'),$rangosMap)?'out-range':'' }}">{{ val($q,'proteinas_totales') }}</td>
                                                <td class="center">{{ rangoTexto('Proteinas totales',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Proteinas totales',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canAny(['globulina','relacion_ag']))
                                            <tr>
                                                <td>Globulina</td>
                                                <td class="center {{ outOfRangeQ('Globulina',val($q,'globulina'),$rangosMap)?'out-range':'' }}">{{ val($q,'globulina') }}</td>
                                                <td class="center">{{ rangoTexto('Globulina',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Globulina',$rangosMap) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Relación A/G</td>
                                                <td class="center {{ outOfRangeQ('Relación A/G',val($q,'relacion_ag'),$rangosMap)?'out-range':'' }}">{{ val($q,'relacion_ag') }}</td>
                                                <td class="center">{{ rangoTexto('Relación A/G',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Relación A/G',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('glucosa'))
                                            <tr>
                                                <td>Glucosa</td>
                                                <td class="center {{ outOfRangeQ('Glucosa',val($q,'glucosa'),$rangosMap)?'out-range':'' }}">{{ val($q,'glucosa') }}</td>
                                                <td class="center">{{ rangoTexto('Glucosa',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Glucosa',$rangosMap) ?: 'mg/dl' }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('urea'))
                                            <tr>
                                                <td>Urea</td>
                                                <td class="center {{ outOfRangeQ('Urea',val($q,'urea'),$rangosMap)?'out-range':'' }}">{{ val($q,'urea') }}</td>
                                                <td class="center">{{ rangoTexto('Urea',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Urea',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('nus'))
                                            <tr>
                                                <td>NUS</td>
                                                <td class="center {{ outOfRangeQ('NUS',val($q,'nus'),$rangosMap)?'out-range':'' }}">{{ val($q,'nus') }}</td>
                                                <td class="center">{{ rangoTexto('NUS',$rangosMap) ?: '6 - 20' }}</td>
                                                <td class="center">{{ rangoUnidad('NUS',$rangosMap) ?: 'mg/dl' }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('creatinina'))
                                            <tr>
                                                <td>Creatinina</td>
                                                <td class="center {{ outOfRangeQ('Creatinina',val($q,'creatinina'),$rangosMap)?'out-range':'' }}">{{ val($q,'creatinina') }}</td>
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

                                        @if($canVariable('colesterol_total'))
                                            <tr>
                                                <td>Colesterol total</td>
                                                <td class="center {{ outOfRangeQ('Colesterol total',val($q,'colesterol_total'),$rangosMap)?'out-range':'' }}">{{ val($q,'colesterol_total') }}</td>
                                                <td class="center">{{ rangoTexto('Colesterol total',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Colesterol total',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('trigliceridos'))
                                            <tr>
                                                <td>Triglicéridos</td>
                                                <td class="center {{ outOfRangeQ('Triglicéridos',val($q,'trigliceridos'),$rangosMap)?'out-range':'' }}">{{ val($q,'trigliceridos') }}</td>
                                                <td class="center">{{ rangoTexto('Triglicéridos',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Triglicéridos',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canAny(['hdl_colesterol','ldl_colesterol','vldl_colesterol']))
                                            <tr>
                                                <td>HDL</td>
                                                <td class="center {{ outOfRangeQ('HDL Colesterol',val($q,'hdl_colesterol'),$rangosMap)?'out-range':'' }}">{{ val($q,'hdl_colesterol') }}</td>
                                                <td class="center">{{ rangoTexto('HDL Colesterol',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('HDL Colesterol',$rangosMap) }}</td>
                                            </tr>
                                            <tr>
                                                <td>LDL</td>
                                                <td class="center {{ outOfRangeQ('LDL Colesterol',val($q,'ldl_colesterol'),$rangosMap)?'out-range':'' }}">{{ val($q,'ldl_colesterol') }}</td>
                                                <td class="center">{{ rangoTexto('LDL Colesterol',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('LDL Colesterol',$rangosMap) }}</td>
                                            </tr>
                                            <tr>
                                                <td>VLDL</td>
                                                <td class="center {{ outOfRangeQ('VLDL Colesterol',val($q,'vldl_colesterol'),$rangosMap)?'out-range':'' }}">{{ val($q,'vldl_colesterol') }}</td>
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
                                        @if($canVariable('ck_total'))
                                            <tr>
                                                <td>CK-Total</td>
                                                <td class="center {{ outOfRangeQ('CK-Total',val($q,'ck_total'),$rangosMap)?'out-range':'' }}">{{ val($q,'ck_total') }}</td>
                                                <td class="center">{{ rangoTexto('CK-Total',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('CK-Total',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('ck_mb'))
                                            <tr>
                                                <td>CK MB</td>
                                                <td class="center {{ outOfRangeQ('CK-MB',val($q,'ck_mb'),$rangosMap)?'out-range':'' }}">{{ val($q,'ck_mb') }}</td>
                                                <td class="center">{{ rangoTexto('CK-MB',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('CK-MB',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('ldh'))
                                            <tr>
                                                <td>LDH</td>
                                                <td class="center {{ outOfRangeQ('LDH',val($q,'ldh'),$rangosMap)?'out-range':'' }}">{{ val($q,'ldh') }}</td>
                                                <td class="center">{{ rangoTexto('LDH',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('LDH',$rangosMap) ?: 'U/L' }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('lipasa'))
                                            <tr>
                                                <td>Lipasa</td>
                                                <td class="center {{ outOfRangeQ('Lipasa',val($q,'lipasa'),$rangosMap)?'out-range':'' }}">{{ val($q,'lipasa') }}</td>
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

                                        @if($canAny(['bilirrubina_total','bilirrubina_directa','bilirrubina_indirecta']))
                                            <tr>
                                                <td>Bilirrubina Total</td>
                                                <td class="center {{ outOfRangeQ('Bilirrubina Total',val($q,'bilirrubina_total'),$rangosMap)?'out-range':'' }}">{{ val($q,'bilirrubina_total') }}</td>
                                                <td class="center">{{ rangoTexto('Bilirrubina Total',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Bilirrubina Total',$rangosMap) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Bilirrubina Directa</td>
                                                <td class="center {{ outOfRangeQ('Bilirrubina Directa',val($q,'bilirrubina_directa'),$rangosMap)?'out-range':'' }}">{{ val($q,'bilirrubina_directa') }}</td>
                                                <td class="center">{{ rangoTexto('Bilirrubina Directa',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Bilirrubina Directa',$rangosMap) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Bilirrubina Indirecta</td>
                                                <td class="center {{ outOfRangeQ('Bilirrubina Indirecta',val($q,'bilirrubina_indirecta'),$rangosMap)?'out-range':'' }}">{{ val($q,'bilirrubina_indirecta') }}</td>
                                                <td class="center">{{ rangoTexto('Bilirrubina Indirecta',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Bilirrubina Indirecta',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('got'))
                                            <tr>
                                                <td>G.O.T. (TGO)</td>
                                                <td class="center {{ outOfRangeQ('G.O.T. (TGO)',val($q,'got'),$rangosMap)?'out-range':'' }}">{{ val($q,'got') }}</td>
                                                <td class="center">{{ rangoTexto('G.O.T. (TGO)',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('G.O.T. (TGO)',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('gpt'))
                                            <tr>
                                                <td>G.P.T. (TGP)</td>
                                                <td class="center {{ outOfRangeQ('G.P.T. (TGP)',val($q,'gpt'),$rangosMap)?'out-range':'' }}">{{ val($q,'gpt') }}</td>
                                                <td class="center">{{ rangoTexto('G.P.T. (TGP)',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('G.P.T. (TGP)',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('fosfatasa_alcalina'))
                                            <tr>
                                                <td>Fosfatasa Alcalina</td>
                                                <td class="center {{ outOfRangeQ('Fosfatasa Alcalina',val($q,'fosfatasa_alcalina'),$rangosMap)?'out-range':'' }}">{{ val($q,'fosfatasa_alcalina') }}</td>
                                                <td class="center">{{ rangoTexto('Fosfatasa Alcalina',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Fosfatasa Alcalina',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('ggt'))
                                            <tr>
                                                <td>GGT</td>
                                                <td class="center {{ outOfRangeQ('GGT',val($q,'ggt'),$rangosMap)?'out-range':'' }}">{{ val($q,'ggt') }}</td>
                                                <td class="center">{{ rangoTexto('GGT',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('GGT',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('amilasa'))
                                            <tr>
                                                <td>Amilasa</td>
                                                <td class="center {{ outOfRangeQ('Amilasa',val($q,'amilasa'),$rangosMap)?'out-range':'' }}">{{ val($q,'amilasa') }}</td>
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

                                        @if($canAny(['sodio','potasio','cloro']))
                                            <tr>
                                                <td>Sodio</td>
                                                <td class="center {{ outOfRangeQ('Sodio',val($q,'sodio'),$rangosMap)?'out-range':'' }}">{{ val($q,'sodio') }}</td>
                                                <td class="center">{{ rangoTexto('Sodio',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Sodio',$rangosMap) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Potasio</td>
                                                <td class="center {{ outOfRangeQ('Potasio',val($q,'potasio'),$rangosMap)?'out-range':'' }}">{{ val($q,'potasio') }}</td>
                                                <td class="center">{{ rangoTexto('Potasio',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Potasio',$rangosMap) }}</td>
                                            </tr>
                                            <tr>
                                                <td>Cloro</td>
                                                <td class="center {{ outOfRangeQ('Cloro',val($q,'cloro'),$rangosMap)?'out-range':'' }}">{{ val($q,'cloro') }}</td>
                                                <td class="center">{{ rangoTexto('Cloro',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Cloro',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('calcio'))
                                            <tr>
                                                <td>Calcio</td>
                                                <td class="center {{ outOfRangeQ('Calcio',val($q,'calcio'),$rangosMap)?'out-range':'' }}">{{ val($q,'calcio') }}</td>
                                                <td class="center">{{ rangoTexto('Calcio',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Calcio',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('fosforo'))
                                            <tr>
                                                <td>Fósforo</td>
                                                <td class="center {{ outOfRangeQ('Fósforo',val($q,'fosforo'),$rangosMap)?'out-range':'' }}">{{ val($q,'fosforo') }}</td>
                                                <td class="center">{{ rangoTexto('Fósforo',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Fósforo',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('magnesio'))
                                            <tr>
                                                <td>Magnesio</td>
                                                <td class="center {{ outOfRangeQ('Magnesio',val($q,'magnesio'),$rangosMap)?'out-range':'' }}">{{ val($q,'magnesio') }}</td>
                                                <td class="center">{{ rangoTexto('Magnesio',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Magnesio',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('hierro_serico'))
                                            <tr>
                                                <td>Hierro sérico</td>
                                                <td class="center {{ outOfRangeQ('Hierro sérico',val($q,'hierro_serico'),$rangosMap)?'out-range':'' }}">{{ val($q,'hierro_serico') }}</td>
                                                <td class="center">{{ rangoTexto('Hierro sérico',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Hierro sérico',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('trf'))
                                            <tr>
                                                <td>Transferrina (TRF)</td>
                                                <td class="center {{ outOfRangeQ('Transferrina',val($q,'trf'),$rangosMap)?'out-range':'' }}">{{ val($q,'trf') }}</td>
                                                <td class="center">{{ rangoTexto('Transferrina',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Transferrina',$rangosMap) }}</td>
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
                                                <div class="title">Química básica en orina</div>
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

                                                        @if($canVariable('creatinuria_24h'))
                                                            <tr>
                                                                <td>Creatinuria 24 hrs.</td>
                                                                <td class="center {{ outOfRangeQ('Creatinuria 24 hrs.',val($q,'creatinuria_24h'),$rangosMap)?'out-range':'' }}">{{ val($q,'creatinuria_24h') }}</td>
                                                                <td class="center">{{ rangoTexto('Creatinuria 24 hrs.',$rangosMap) }}</td>
                                                                <td class="center">{{ rangoUnidad('Creatinuria 24 hrs.',$rangosMap) }}</td>
                                                            </tr>
                                                        @endif

                                                        @if($canVariable('creatinuria_casual'))
                                                            <tr>
                                                                <td>Creatinuria Casual</td>
                                                                <td class="center {{ outOfRangeQ('Creatinuria Casual',val($q,'creatinuria_casual'),$rangosMap)?'out-range':'' }}">{{ val($q,'creatinuria_casual') }}</td>
                                                                <td class="center">{{ rangoTexto('Creatinuria Casual',$rangosMap) }}</td>
                                                                <td class="center">{{ rangoUnidad('Creatinuria Casual',$rangosMap) }}</td>
                                                            </tr>
                                                        @endif

                                                        @if($canVariable('proteinuria_24h'))
                                                            <tr>
                                                                <td>Proteinuria de 24 hrs.</td>
                                                                <td class="center {{ outOfRangeQ('Proteinuria de 24 hrs.',val($q,'proteinuria_24h'),$rangosMap)?'out-range':'' }}">{{ val($q,'proteinuria_24h') }}</td>
                                                                <td class="center">{{ rangoTexto('Proteinuria de 24 hrs.',$rangosMap) }}</td>
                                                                <td class="center">{{ rangoUnidad('Proteinuria de 24 hrs.',$rangosMap) }}</td>
                                                            </tr>
                                                        @endif

                                                        @if($canVariable('volumen_24h'))
                                                            <tr>
                                                                <td>Volumen 24 h</td>
                                                                <td class="center {{ outOfRangeQ('VOLUMEN',val($q,'volumen_24h'),$rangosMap)?'out-range':'' }}">{{ val($q,'volumen_24h') }}</td>
                                                                <td class="center">{{ rangoTexto('VOLUMEN',$rangosMap) }}</td>
                                                                <td class="center">{{ rangoUnidad('VOLUMEN',$rangosMap) }}</td>
                                                            </tr>
                                                        @endif
                                                        @if($canVariable('dce'))
                                                            <tr>
                                                                <td>DCE</td>
                                                                <td class="center {{ outOfRangeQ('DCE',val($q,'dce'),$rangosMap)?'out-range':'' }}">{{ val($q,'dce') }}</td>
                                                                <td class="center">{{ rangoTexto('DCE',$rangosMap) ?: 'H: 97-137 / M: 88-128' }}</td>
                                                                <td class="center">{{ rangoUnidad('DCE',$rangosMap) ?: 'ml/min' }}</td>
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
                                                        @if($canVariable('hb_glicosilada'))
                                                            <tr>
                                                                <td>Hb Glicosilada A1C</td>
                                                                <td class="center {{ outOfRangeQ('Hb glicosilada (HbA1c)',val($q,'hb_glicosilada'),$rangosMap)?'out-range':'' }}">{{ val($q,'hb_glicosilada') }}</td>
                                                                <td class="center">{{ rangoTexto('Hb glicosilada (HbA1c)',$rangosMap) ?: '3.5 - 5.8' }}</td>
                                                                <td class="center">{{ rangoUnidad('Hb glicosilada (HbA1c)',$rangosMap) ?: '%' }}</td>
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

                                        @if($canVariable('aso'))
                                            <tr>
                                                <td>ASO O ASTO</td>
                                                <td class="center">
                                                    {{ round((float)val($q,'aso_valor')) }}
                                                    {{ val($q,'aso') }}
                                                    @if(val($q,'aso_dilucion'))
                                                        <br><small>{{ val($q,'aso_dilucion') }}</small>
                                                    @endif
                                                </td>
                                                <td class="center">{{ rangoTexto('ASO',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('ASO',$rangosMap) }}</td>
                                            </tr>
                                        @endif
{{--                                        @if($canServicios('ASTO O ASO') || hasVal($q,'aso_valor'))--}}
{{--                                            <tr>--}}
{{--                                                <td>ASO valor</td>--}}
{{--                                                <td class="center">{{ valorConFormato($q,'aso_valor') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('ASO',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('ASO',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}
{{--                                        @endif--}}

                                        @if($canVariable('fr'))
                                            <tr>
                                                <td>FR</td>
                                                <td class="center">
                                                    {{ round((float)val($q,'fr_valor')) }}
                                                    {{ val($q,'fr') }}
                                                    @if(val($q,'fr_dilucion'))
                                                        <br><small>{{ val($q,'fr_dilucion') }}</small>
                                                    @endif
                                                </td>
                                                <td class="center">{{ rangoTexto('FR',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('FR',$rangosMap) }}</td>
                                            </tr>
                                        @endif
{{--                                        @if($canServicios('FACTOR REUMATOIDEO (FR)') || hasVal($q,'fr_valor'))--}}
{{--                                            <tr>--}}
{{--                                                <td>FR valor</td>--}}
{{--                                                <td class="center">{{ valorConFormato($q,'fr_valor') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('FR',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('FR',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}
{{--                                        @endif--}}
{{--                                        Test de embarazo--}}
                                        @if($canVariable('test_embarazo'))
                                            <tr>
                                                <td>Test embarazo</td>
                                                <td class="center">
                                                    {{ val($q,'test_embarazo') }}
                                                    @if(val($q,'test_embarazo_fum'))
                                                        <br><small>F.U.M.: {{ \Carbon\Carbon::parse(val($q,'test_embarazo_fum'))->format('d/m/Y') }}</small>
                                                    @endif
                                                </td>
                                                <td class="center">{{ rangoTexto('Test de embarazo',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Test de embarazo',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('pcr'))
                                            <tr>
                                                <td>PCR</td>
                                                <td class="center">
                                                    {{ round((float)val($q,'pcr_valor')) }}
                                                    {{ val($q,'pcr') }}
                                                    @if(val($q,'pcr_dilucion'))
                                                        <br><small>{{ val($q,'pcr_dilucion') }}</small>
                                                    @endif
                                                </td>
                                                <td class="center">{{ rangoTexto('PCR',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('PCR',$rangosMap) }}</td>
                                            </tr>
                                        @endif
{{--                                        @if($canServicios(['PCR CUALITATIVO (PROTEÍNA C REACTIVA)']) || hasVal($q,'pcr_valor'))--}}
{{--                                            <tr>--}}
{{--                                                <td>PCR valor</td>--}}
{{--                                                <td class="center">{{ valorConFormato($q,'pcr_valor') }}</td>--}}
{{--                                                <td class="center">{{ rangoTexto('PCR',$rangosMap) }}</td>--}}
{{--                                                <td class="center">{{ rangoUnidad('PCR',$rangosMap) }}</td>--}}
{{--                                            </tr>--}}
{{--                                        @endif--}}

                                        @if($canVariable('prueba_rapida_vih'))
                                            <tr>
                                                <td>Prueba rápida VIH</td>
                                                <td class="center">{{ val($q,'prueba_rapida_vih') }}</td>
                                                <td class="center">{{ rangoTexto('Prueba rápida VIH',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Prueba rápida VIH',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('prueba_rapida_hepatitis_b'))
                                            <tr>
                                                <td>
                                                    Prueba rápida Hepatitis B
                                                </td>
                                                <td class="center">{{ val($q,'prueba_rapida_hepatitis_b') }}</td>
                                                <td class="center">{{ rangoTexto('Hepatitis B',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Hepatitis B',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('prueba_rapida_hepatitis_c'))
                                            <tr>
                                                <td>Prueba rápida Hepatitis C</td>
                                                <td class="center">{{ val($q,'prueba_rapida_hepatitis_c') }}</td>
                                                <td class="center">{{ rangoTexto('Hepatitis C',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Hepatitis C',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('prueba_rapida_chagas'))
                                            <tr>
                                                <td>Chagas</td>
                                                <td class="center">{{ val($q,'prueba_rapida_chagas') }}</td>
                                                <td class="center">{{ rangoTexto('Chagas',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Chagas',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('prueba_rapida_sifilis'))
                                            <tr>
                                                <td>Sífilis</td>
                                                <td class="center">{{ val($q,'prueba_rapida_sifilis') }}</td>
                                                <td class="center">{{ rangoTexto('Prueba rápida Sífilis',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Prueba rápida Sífilis',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('rpr'))
                                            <tr>
                                                <td>RPR / VDRL</td>
                                                <td class="center">
                                                    {{ val($q,'rpr') }}
                                                    @if(val($q,'rpr_dilucion'))
                                                        <br><small>{{ val($q,'rpr_dilucion') }}</small>
                                                    @endif
                                                </td>
                                                <td class="center">{{ rangoTexto('RPR/VDRL',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('RPR/VDRL',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canVariable('prueba_rapida_troponina'))
                                            <tr>
                                                <td>Troponina</td>
                                                <td class="center">{{ val($q,'prueba_rapida_troponina') }}</td>
                                                <td class="center">{{ rangoTexto('Troponina',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Troponina',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        @if($canAny(['reaccion_widal_o','reaccion_widal_h','reaccion_widal_a','reaccion_widal_b']) && hasVal($q,'reaccion_widal'))
                                            <tr>
                                                <td>Reacción de Widal</td>
                                                <td class="center">{{ val($q,'reaccion_widal') }}</td>
                                                <td class="center">{{ rangoTexto('Reacción de Widal',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Reacción de Widal',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('reaccion_widal_o'))
                                            <tr>
                                                <td>Widal O</td>
                                                <td class="center">
                                                    {{ val($q,'reaccion_widal_o') }}{{ hasVal($q,'reaccion_widal_o_valor') ? ' / '.val($q,'reaccion_widal_o_valor') : '' }}
                                                </td>
                                                <td class="center">{{ rangoTexto('Reacción de Widal O',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Reacción de Widal O',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('reaccion_widal_h'))
                                            <tr>
                                                <td>Widal H</td>
                                                <td class="center">
                                                    {{ val($q,'reaccion_widal_h') }}{{ hasVal($q,'reaccion_widal_h_valor') ? ' / '.val($q,'reaccion_widal_h_valor') : '' }}
                                                </td>
                                                <td class="center">{{ rangoTexto('Reacción de Widal H',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Reacción de Widal H',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('reaccion_widal_a'))
                                            <tr>
                                                <td>Widal A</td>
                                                <td class="center">
                                                    {{ val($q,'reaccion_widal_a') }}{{ hasVal($q,'reaccion_widal_a_valor') ? ' / '.val($q,'reaccion_widal_a_valor') : '' }}
                                                </td>
                                                <td class="center">{{ rangoTexto('Reacción de Widal A',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Reacción de Widal A',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('reaccion_widal_b'))
                                            <tr>
                                                <td>Widal B</td>
                                                <td class="center">
                                                    {{ val($q,'reaccion_widal_b') }}{{ hasVal($q,'reaccion_widal_b_valor') ? ' / '.val($q,'reaccion_widal_b_valor') : '' }}
                                                </td>
                                                <td class="center">{{ rangoTexto('Reacción de Widal B',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Reacción de Widal B',$rangosMap) }}</td>
                                            </tr>
                                        @endif

                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- ===================== GASOMETRÍA ===================== --}}
                        @if($showGasometria)
                            <div class="block">
                                <div class="title">Gasometría</div>
                                <div class="body">
                                    <table class="tbl">
                                        @if(hasVal($q,'gasometria_tipo'))
                                        <tr>
                                            <td class="bold" style="width:30%">Tipo</td>
                                            <td>{{ val($q,'gasometria_tipo') }}</td>
                                        </tr>
                                        @endif
                                        @if(hasVal($q,'gasometria_muestra_estado'))
                                        <tr>
                                            <td class="bold" style="width:30%">Estado de la muestra</td>
                                            <td>{{ val($q,'gasometria_muestra_estado') }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>
                        @endif

                        {{-- ===================== OBSERVACIONES ===================== --}}
                        @if(false && $showObs)
                            <div class="block">
                                <div class="title">Observaciones / Método / Equipo</div>
                                <div class="body">
                                    <table class="tbl">
                                        <tr>
                                            <td style="width:16%" class="bold">Observaciones</td>
                                            <td style="width:86%">{{ val($q,'observaciones') }}</td>
                                        </tr>
{{--                                        <tr>--}}
{{--                                            <td class="bold">Método</td>--}}
{{--                                            <td>{{ val($q,'metodo') }}</td>--}}
{{--                                        </tr>--}}
{{--                                        <tr>--}}
{{--                                            <td class="bold">Equipo</td>--}}
{{--                                            <td>{{ val($q,'equipo') }}</td>--}}
{{--                                        </tr>--}}
                                    </table>
                                </div>
                            </div>
                        @endif
                        @if($showCito || $hasCitoData)
                            <div class="block">
                                <div class="title">Citoquímico</div>
                                <div class="body">
                                    @if($canVariable('tipo_de_muestra') && hasVal($q,'tipo_de_muestra'))
                                        <div style="padding:0 0 4px 2px;"><span class="bold">Muestra:</span> {{ val($q,'tipo_de_muestra') }}</div>
                                    @endif
                                    <table class="tbl">
                                        <thead>
                                        <tr>
                                            <th style="width:44%;">Parámetro</th>
                                            <th style="width:16%;">Res</th>
                                            <th style="width:26%;">Rango</th>
                                            <th style="width:14%;">Unid</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                        @if($canVariable('citoquimico_cantidad') && hasVal($q,'citoquimico_cantidad'))
                                            <tr>
                                                <td>Cantidad (ml)</td>
                                                <td class="center">{{ round((float)val($q,'citoquimico_cantidad'))}}</td>
                                                <td class="center">{{ rangoTexto('Cantidad (ml)',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Cantidad (ml)',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('citoquimico_color') && hasVal($q,'citoquimico_color'))
                                            <tr>
                                                <td>Color</td>
                                                <td class="center">{{ val($q,'citoquimico_color') }}</td>
                                                <td class="center">{{ rangoTexto('Color',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Color',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('citoquimico_aspecto') && hasVal($q,'citoquimico_aspecto'))
                                            <tr>
                                                <td>Aspecto</td>
                                                <td class="center">{{ val($q,'citoquimico_aspecto') }}</td>
                                                <td class="center">{{ rangoTexto('Aspecto',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Aspecto',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('citoquimico_glucosa') && hasVal($q,'citoquimico_glucosa'))
                                            <tr>
                                                <td>Glucosa</td>
                                                <td class="center">{{ round((float)val($q,'citoquimico_glucosa')) }}</td>
                                                <td class="center">{{ rangoTexto('Glucosa',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Glucosa',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('citoquimico_ph') && hasVal($q,'citoquimico_ph'))
                                            <tr>
                                                <td>pH</td>
                                                <td class="center {{ outOfRangeQ('pH',val($q,'citoquimico_ph'),$rangosMap)?'out-range':'' }}">{{ val($q,'citoquimico_ph') }}</td>
                                                <td class="center">{{ rangoTexto('pH',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('pH',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('citoquimico_proteinas_totales') && hasVal($q,'citoquimico_proteinas_totales'))
                                            <tr>
                                                <td>Proteínas totales</td>
                                                <td class="center">{{ number_format(val($q,'citoquimico_proteinas_totales'), 1) }}</td>
                                                <td class="center">{{ rangoTexto('Proteínas totales',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Proteínas totales',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('citoquimico_densidad') && hasVal($q,'citoquimico_densidad'))
                                            <tr>
                                                <td>Densidad</td>
                                                <td class="center {{ outOfRangeQ('Densidad',val($q,'citoquimico_densidad'),$rangosMap)?'out-range':'' }}">{{ val($q,'citoquimico_densidad') }}</td>
                                                <td class="center">{{ rangoTexto('Densidad',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Densidad',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('citoquimico_ldh') && hasVal($q,'citoquimico_ldh'))
                                            <tr>
                                                <td>LDH</td>
                                                <td class="center">{{ round((float)val($q,'citoquimico_ldh')) }}</td>
                                                <td class="center">{{ rangoTexto('LDH',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('LDH',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('citoquimico_globulos_blancos') && hasVal($q,'citoquimico_globulos_blancos'))
                                            <tr>
                                                <td>Glóbulos blancos</td>
                                                <td class="center">{{ number_format(val($q,'citoquimico_globulos_blancos'), 1) }}</td>
                                                <td class="center">{{ rangoTexto('Glóbulos blancos',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Glóbulos blancos',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('citoquimico_polimorfonucleares') && hasVal($q,'citoquimico_polimorfonucleares'))
                                            <tr>
                                                <td>Polimorfonucleares (%)</td>
                                                <td class="center">{{ round((float)val($q,'citoquimico_polimorfonucleares')) }}</td>
                                                <td class="center">{{ rangoTexto('Polimorfonucleares (%)',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Polimorfonucleares (%)',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('citoquimico_mononucleares') && hasVal($q,'citoquimico_mononucleares'))
                                            <tr>
                                                <td>Mononucleares (%)</td>
                                                <td class="center">{{ round((float)val($q,'citoquimico_mononucleares')) }}</td>
                                                <td class="center">{{ rangoTexto('Mononucleares (%)',$rangosMap) }}</td>
                                                <td class="center">{{ rangoUnidad('Mononucleares (%)',$rangosMap) }}</td>
                                            </tr>
                                        @endif
                                        @if($canVariable('citoquimico_mononucleares') && hasVal($q,'citoquimico_mononucleares'))
                                            <tr>
                                                <td>Total (%)</td>
                                                <td class="center">
                                                    {{ round((float)val($q,'citoquimico_mononucleares')) +round((float)val($q,'citoquimico_polimorfonucleares')) }}
                                                </td>
                                                <td class="center"></td>
                                                <td class="center">%</td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                    @if($canVariable('citoquimico_observaciones') && hasVal($q,'citoquimico_observaciones'))
                                        <div style="font-size:7px; margin-top:2px;"><span style="font-weight:700;">Observaciones:</span> {{ val($q,'citoquimico_observaciones') }}</div>
                                    @endif
                                </div>
                            </div>
                        @endif


                        {{-- ===================== OBSERVACIONES ===================== --}}
{{--                        @if($showObs)--}}
                            <div class="block">
                                <div class="title">Observaciones </div>
                                <div class="body">
                                    <table class="tbl">
                                        <tr>
                                            <td style="width:16%" class="bold">Observaciones</td>
                                            <td style="width:86%">{{ val($q,'observaciones') }}</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
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
