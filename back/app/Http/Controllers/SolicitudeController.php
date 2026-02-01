<?php

namespace App\Http\Controllers;

use App\Models\PerfilImpresion;
use App\Models\ServicioSolicitude;
use App\Models\SolicitudeFormulario;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\ResultadoLaboratorio;
use App\Models\Servicio;
use App\Models\Solicitude;
use App\Models\Paciente;
use App\Models\Doctor;
use App\Models\SolitudePreAnalitica;
use App\Models\SolicitudePropiedad;

// <-- NUEVO
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class SolicitudeController extends Controller
{
    public function pdfPreanalitica(Request $request)
    {
        $fecha  = $request->query('fecha');          // YYYY-MM-DD
        $filter = $request->query('filter', '');

        // si no mandan fecha, usa hoy
        if (!$fecha) {
            $fecha = now()->toDateString();
        }

        $query = Solicitude::with([
            'paciente',
            'doctor',
            'servicios',
            'userPreanalitica',
        ])
            ->whereDate('fecha_creacion', $fecha)
            ->whereIn('estado', ['ATENDIENDO','MUESTRA RECHAZADA','ENVIADO_ANALITICA','ANALIZADO','MUESTRA NO TOMADA'])
            ->orderByRaw("FIELD(estado, 'ATENDIENDO', 'ENVIADO_ANALITICA', 'ANALIZADO', 'MUESTRA RECHAZADA', 'MUESTRA NO TOMADA') ASC")
            ->orderBy('id', 'desc');

        if (!empty($filter)) {
            $query->where(function ($q) use ($filter) {
                $q->where('paciente_nombre', 'like', "%$filter%")
                    ->orWhereHas('paciente', function ($q2) use ($filter) {
                        $q2->where('nombre_completo', 'like', "%$filter%")
                            ->orWhere('ci', 'like', "%$filter%");
                    })
                    ->orWhere('establecimiento_salud', 'like', "%$filter%")
                    ->orWhere('doctor_nombre', 'like', "%$filter%");
            });
        }

        $rows = $query->get();

        $pdf = Pdf::loadView('reportes.solicitudes_preanalitica', [
            'fecha' => $fecha,
            'filter' => $filter,
            'rows' => $rows,
            'generado' => now(),
        ])->setPaper('letter', 'landscape'); // igual que tu jsPDF landscape letter

        return $pdf->stream("solicitudes_preanalitica_{$fecha}.pdf");
    }
    function marcarMuestraNoTomada(Request $request, $id)
    {
        $solicitud = Solicitude::findOrFail($id);
//        $solicitud->muestra_rechazada = 'Si';
        $solicitud->motivo_rechazo = $request->input('motivo_rechazo', 'No especificado');
//        $solicitud->fecha_rechazo_muestra = now();
        $solicitud->user_preanalitica_id = $request->user() ? $request->user()->id : null;
        $solicitud->estado = 'MUESTRA NO TOMADA';
        $solicitud->fecha_envio_analitica = now();
        $solicitud->save();

        return response()->json($solicitud->fresh());
    }
    public function reporteSolicitudesServicios(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo   = $request->get('date_to');
        $userId   = $request->get('user_id');
        $group    = $request->get('group', 'day');

        // servicio_id puede venir como: servicio_id=3 o servicio_id[]=3&servicio_id[]=5
        $servicioIds = $request->get('servicio_id', null);
        if ($servicioIds !== null && !is_array($servicioIds)) {
            $servicioIds = [$servicioIds];
        }
        $servicioIds = array_values(array_filter((array)$servicioIds, fn($x) => $x !== null && $x !== ''));
        $servicioIds = !empty($servicioIds) ? array_map('intval', $servicioIds) : [];

        // ---- query base: solicitudes (soft delete)
        $solQuery = DB::table('solicitudes as s')
            ->whereNull('s.deleted_at');

        if ($dateFrom) $solQuery->whereDate('s.fecha_creacion', '>=', $dateFrom);
        if ($dateTo)   $solQuery->whereDate('s.fecha_creacion', '<=', $dateTo);
        if ($userId)   $solQuery->where('s.user_id', $userId);

        // ---- base pivot servicio_solicitudes (soft delete pivot + solicitudes + servicios)
        $ssQuery = DB::table('servicio_solicitudes as ss')
            ->join('solicitudes as s', 's.id', '=', 'ss.solicitude_id')
            ->join('servicios as se', 'se.id', '=', 'ss.servicio_id')
            ->whereNull('s.deleted_at')
            ->whereNull('ss.deleted_at')
            ->whereNull('se.deleted_at');

        if ($dateFrom) $ssQuery->whereDate('s.fecha_creacion', '>=', $dateFrom);
        if ($dateTo)   $ssQuery->whereDate('s.fecha_creacion', '<=', $dateTo);
        if ($userId)   $ssQuery->where('s.user_id', $userId);
        if (!empty($servicioIds)) $ssQuery->whereIn('ss.servicio_id', $servicioIds);

        // ---- KPIs
        $totalSolicitudes  = (clone $solQuery)->count();
        $totalPrestaciones = (clone $ssQuery)->count();

        $promedioPrestaciones = $totalSolicitudes > 0
            ? round($totalPrestaciones / $totalSolicitudes, 1)
            : 0;

        // ---- expresiones de agrupación
        if ($group === 'month') {
            $keyExpr   = "DATE_FORMAT(s.fecha_creacion, '%Y-%m')";
            $labelExpr = "DATE_FORMAT(s.fecha_creacion, '%Y-%m')";
        } elseif ($group === 'week') {
            $keyExpr   = "YEARWEEK(s.fecha_creacion, 1)";
            $labelExpr = "CONCAT(YEAR(s.fecha_creacion), '-W', LPAD(WEEK(s.fecha_creacion, 1), 2, '0'))";
        } else { // day
            $keyExpr   = "DATE(s.fecha_creacion)";
            $labelExpr = "DATE(s.fecha_creacion)";
        }

        // ---- SERIE: solicitudes vs prestaciones (FIX ONLY_FULL_GROUP_BY)
        $serie = DB::table('solicitudes as s')
            ->leftJoin('servicio_solicitudes as ss', function ($join) {
                $join->on('ss.solicitude_id', '=', 's.id')
                    ->whereNull('ss.deleted_at');
            })
            ->whereNull('s.deleted_at')
            ->when($dateFrom, fn($q) => $q->whereDate('s.fecha_creacion', '>=', $dateFrom))
            ->when($dateTo,   fn($q) => $q->whereDate('s.fecha_creacion', '<=', $dateTo))
            ->when($userId,   fn($q) => $q->where('s.user_id', $userId))
            ->when(!empty($servicioIds), fn($q) => $q->whereIn('ss.servicio_id', $servicioIds))
            ->selectRaw("$keyExpr as group_key")
            ->selectRaw("$labelExpr as label")
            ->selectRaw("COUNT(DISTINCT s.id) as solicitudes")
            ->selectRaw("COUNT(ss.id) as prestaciones")
            ->groupBy('group_key', 'label')
            ->orderBy('group_key', 'asc')
            ->get();

        // ---- TABLA POR USUARIO (solicitudes + prestaciones)
        $porUsuario = DB::table('users as u')
            ->join('solicitudes as s', 's.user_id', '=', 'u.id')
            ->leftJoin('servicio_solicitudes as ss', function ($join) {
                $join->on('ss.solicitude_id', '=', 's.id')
                    ->whereNull('ss.deleted_at');
            })
            ->whereNull('u.deleted_at')
            ->whereNull('s.deleted_at')
            ->when($dateFrom, fn($q) => $q->whereDate('s.fecha_creacion', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('s.fecha_creacion', '<=', $dateTo))
            ->when($userId, fn($q) => $q->where('u.id', $userId))
            ->when(!empty($servicioIds), fn($q) => $q->whereIn('ss.servicio_id', $servicioIds))
            ->groupBy('u.id', 'u.name', 'u.username')
            ->select(
                'u.id as user_id',
                'u.name as user_name',
                'u.username',
                DB::raw('COUNT(DISTINCT s.id) as solicitudes'),
                DB::raw('COUNT(ss.id) as prestaciones')
            )
            ->orderByDesc('prestaciones')
            ->get();

        // ---- TOP PRESTACIONES (servicios) (histograma)
        $topPrestaciones = DB::table('servicio_solicitudes as ss')
            ->join('solicitudes as s', 's.id', '=', 'ss.solicitude_id')
            ->join('servicios as se', 'se.id', '=', 'ss.servicio_id')
            ->whereNull('s.deleted_at')
            ->whereNull('ss.deleted_at')
            ->whereNull('se.deleted_at')
            ->when($dateFrom, fn($q) => $q->whereDate('s.fecha_creacion', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('s.fecha_creacion', '<=', $dateTo))
            ->when($userId, fn($q) => $q->where('s.user_id', $userId))
            ->when(!empty($servicioIds), fn($q) => $q->whereIn('ss.servicio_id', $servicioIds))
            ->groupBy('se.id', 'se.nombre')
            ->select(
                'se.id as prestacion_id',
                'se.nombre as prestacion_nombre',
                DB::raw('COUNT(*) as total'),
                DB::raw('COUNT(DISTINCT s.id) as solicitudes')
            )
            ->orderByDesc('total')
            ->limit(12)
            ->get();

        // ---- ÚLTIMAS SOLICITUDES con conteo prestaciones
        $ultimas = DB::table('solicitudes as s')
            ->leftJoin('users as u', 'u.id', '=', 's.user_id')
            ->whereNull('s.deleted_at')
            ->when($dateFrom, fn($q) => $q->whereDate('s.fecha_creacion', '>=', $dateFrom))
            ->when($dateTo, fn($q) => $q->whereDate('s.fecha_creacion', '<=', $dateTo))
            ->when($userId, fn($q) => $q->where('s.user_id', $userId))
            ->when(!empty($servicioIds), function ($q) use ($servicioIds) {
                $q->whereExists(function ($sub) use ($servicioIds) {
                    $sub->select(DB::raw(1))
                        ->from('servicio_solicitudes as ssf')
                        ->whereColumn('ssf.solicitude_id', 's.id')
                        ->whereNull('ssf.deleted_at')
                        ->whereIn('ssf.servicio_id', $servicioIds);
                });
            })
            ->select(
                's.id',
                's.nro_registro',
                's.codigo_solicitud',
                's.paciente_nombre',
                's.doctor_nombre',
                's.estado',
                's.fecha_creacion',
                's.hora_solicitud',
                'u.name as user_name',
                DB::raw('(
                SELECT COUNT(*)
                FROM servicio_solicitudes ss2
                WHERE ss2.solicitude_id = s.id
                  AND ss2.deleted_at IS NULL
            ) as cant_prestaciones')
            )
            ->orderByDesc('s.fecha_creacion')
            ->orderByDesc('s.id')
            ->limit(20)
            ->get();

        return response()->json([
            'resumen' => [
                'total_solicitudes' => $totalSolicitudes,
                'total_prestaciones' => $totalPrestaciones,
                'promedio_prestaciones' => $promedioPrestaciones,
            ],
            'por_usuario' => $porUsuario,
            'top_prestaciones' => $topPrestaciones,
            'serie' => $serie,
            'ultimas' => $ultimas,
        ]);
    }

    function muestrasRechazadas(){
        $solicitudes = Solicitude::with([
            'paciente',
            'doctor',
            'servicios.area.areaTipoMuestras',
            'userPreanalitica',
            'user'
        ])->where('muestra_rechazada', 'Si')
            ->orderBy('id', 'desc')
            ->get();

        return response()->json($solicitudes);
    }
    public function dashboard(Request $request)
    {
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        // FILTRO BASE SOBRE SOLICITUDES
        $query = Solicitude::query()
            ->whereNull('deleted_at');

        if ($dateFrom) {
            $query->whereDate('fecha_creacion', '>=', $dateFrom);
        }
        if ($dateTo) {
            $query->whereDate('fecha_creacion', '<=', $dateTo);
        }

        // 1) KPIs de SOLICITUDES
        $totalSolicitudes = (clone $query)->count();

        $totalPacientes = (clone $query)
            ->whereNotNull('paciente_id')
            ->distinct('paciente_id')
            ->count('paciente_id');

        $totalDoctores = (clone $query)
            ->whereNotNull('doctor_id')
            ->distinct('doctor_id')
            ->count('doctor_id');

        $finalizadas = (clone $query)
            ->where('estado', 'FINALIZADO')
            ->count();

        // 2) KPIs de SERVICIOS (TABLA servicio_solicitudes)
        $serviciosQuery = DB::table('servicio_solicitudes as ss')
            ->join('solicitudes as s', 's.id', '=', 'ss.solicitude_id')
            ->whereNull('s.deleted_at');

        if ($dateFrom) {
            $serviciosQuery->whereDate('s.fecha_creacion', '>=', $dateFrom);
        }
        if ($dateTo) {
            $serviciosQuery->whereDate('s.fecha_creacion', '<=', $dateTo);
        }

        $totalServicios = (clone $serviciosQuery)->count(); // filas en servicio_solicitudes

        $promedioServicios = $totalSolicitudes > 0
            ? round($totalServicios / $totalSolicitudes, 1)
            : 0;

        // 3) KPIs de PREANALÍTICA (TABLA solitude_pre_analiticas)
        $preQuery = DB::table('solitude_pre_analiticas as spa')
            ->join('solicitudes as s', 's.id', '=', 'spa.solicitude_id')
            ->whereNull('s.deleted_at');

        if ($dateFrom) {
            $preQuery->whereDate('s.fecha_creacion', '>=', $dateFrom);
        }
        if ($dateTo) {
            $preQuery->whereDate('s.fecha_creacion', '<=', $dateTo);
        }

        $totalMuestrasPre = (clone $preQuery)->count();

        // 4) SOLICITUDES Y SERVICIOS POR ÁREA
        $porArea = DB::table('servicio_solicitudes as ss')
            ->join('areas as a', 'a.id', '=', 'ss.area_id')
            ->join('solicitudes as s', 's.id', '=', 'ss.solicitude_id')
            ->whereNull('s.deleted_at')
            ->when($dateFrom, function ($q) use ($dateFrom) {
                $q->whereDate('s.fecha_creacion', '>=', $dateFrom);
            })
            ->when($dateTo, function ($q) use ($dateTo) {
                $q->whereDate('s.fecha_creacion', '<=', $dateTo);
            })
            ->groupBy('ss.area_id', 'a.name')
            ->select(
                'ss.area_id',
                'a.name as area_nombre',
                DB::raw('COUNT(DISTINCT s.id) as solicitudes'),
                DB::raw('COUNT(ss.id) as servicios')
            )
            ->orderByDesc('solicitudes')
            ->get();

        // 5) TOP SERVICIOS MÁS SOLICITADOS
        $topServicios = DB::table('servicio_solicitudes as ss')
            ->join('servicios as se', 'se.id', '=', 'ss.servicio_id')
            ->join('solicitudes as s', 's.id', '=', 'ss.solicitude_id')
            ->whereNull('s.deleted_at')
            ->when($dateFrom, function ($q) use ($dateFrom) {
                $q->whereDate('s.fecha_creacion', '>=', $dateFrom);
            })
            ->when($dateTo, function ($q) use ($dateTo) {
                $q->whereDate('s.fecha_creacion', '<=', $dateTo);
            })
            ->groupBy('ss.servicio_id', 'se.nombre')
            ->select(
                'ss.servicio_id',
                'se.nombre as servicio_nombre',
                DB::raw('COUNT(*) as total')
            )
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 6) TOP TIPOS DE MUESTRA PREANALÍTICA
        $porTipoMuestra = DB::table('solitude_pre_analiticas as spa')
            ->join('area_tipo_muestras as atm', 'atm.id', '=', 'spa.area_tipo_muestra_id')
            ->join('areas as a', 'a.id', '=', 'atm.area_id')
            ->join('solicitudes as s', 's.id', '=', 'spa.solicitude_id')
            ->whereNull('s.deleted_at')
            ->when($dateFrom, function ($q) use ($dateFrom) {
                $q->whereDate('s.fecha_creacion', '>=', $dateFrom);
            })
            ->when($dateTo, function ($q) use ($dateTo) {
                $q->whereDate('s.fecha_creacion', '<=', $dateTo);
            })
            ->groupBy('atm.id', 'atm.tipo_muestra', 'a.name')
            ->select(
                'atm.id',
                'atm.tipo_muestra',
                'a.name as area_nombre',
                DB::raw('COUNT(*) as total')
            )
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        // 7) SERIE POR FECHA (SOLICITUDES/DÍA)
        $serieFechas = (clone $query)
            ->select(DB::raw('DATE(fecha_creacion) as fecha'), DB::raw('COUNT(*) as total'))
            ->groupBy(DB::raw('DATE(fecha_creacion)'))
            ->orderBy('fecha')
            ->get();

        // 8) ÚLTIMAS SOLICITUDES, CON ÁREAS Y Nº SERVICIOS
        $ultimasSolicitudes = (clone $query)
            ->select(
                'solicitudes.id',
                'solicitudes.nro_registro',
                'solicitudes.codigo_solicitud',
                'solicitudes.paciente_nombre',
                'solicitudes.doctor_nombre',
                'solicitudes.tipo_atencion',
                'solicitudes.estado',
                'solicitudes.fecha_creacion',
                'solicitudes.hora_solicitud',
                DB::raw('(
                SELECT COUNT(*)
                FROM servicio_solicitudes ss
                WHERE ss.solicitude_id = solicitudes.id
            ) as cant_servicios'),
                DB::raw('(
                SELECT GROUP_CONCAT(DISTINCT a.name SEPARATOR ", ")
                FROM servicio_solicitudes ss
                JOIN areas a ON a.id = ss.area_id
                WHERE ss.solicitude_id = solicitudes.id
            ) as areas')
            )
            ->orderByDesc('solicitudes.fecha_creacion')
            ->orderByDesc('solicitudes.id')
            ->limit(20)
            ->get();

        return response()->json([
            'resumen' => [
                'total_solicitudes' => $totalSolicitudes,
                'total_servicios' => $totalServicios,
                'promedio_servicios' => $promedioServicios,
                'total_muestras_preanaliticas' => $totalMuestrasPre,
                // extras por si luego quieres usarlos
                'total_pacientes' => $totalPacientes,
                'total_doctores' => $totalDoctores,
                'finalizadas' => $finalizadas,
            ],
            'por_area' => $porArea,
            'top_servicios' => $topServicios,
            'por_tipo_muestra' => $porTipoMuestra,
            'serie_fechas' => $serieFechas,
            'ultimas' => $ultimasSolicitudes,
        ]);
    }

    function solicitudesAnalitica(Request $request)
    {
        $filter = $request->input('filter', '');
        $fecha = $request->input('fecha', '');
        $query = Solicitude::with([
            'paciente', 'doctor', 'servicios.area', 'resultados',
            'hematologia',
            'quimicaSanguinea',
            'uroanalisis',
            'parasitologia',
            'papilomaHumano',
            'panelRespiratorio',
            'panelSexual',
            'cultivoAntibiograma',
            ])
            ->whereIn('estado', ['ENVIADO_ANALITICA', 'ANALITICA_ATENDIENDO', 'FINALIZADO','ANALIZADO','MUESTRA RECHAZADA']);

        $user = $request->user();

        // Si NO es administrador, filtrar por el área del usuario
        if ($user && $user->role !== 'Administrador' && $user->area_id) {
            $areaId = $user->area_id;

            $query->whereHas('servicios', function ($q) use ($areaId) {
                $q->where('servicio_solicitudes.area_id', $areaId);
            });
        }

        if (!empty($filter)) {
            $query->where(function ($q) use ($filter) {
                $q->where('paciente_nombre', 'like', "%$filter%")
                    ->orWhereHas('paciente', function ($q2) use ($filter) {
                        $q2->where('nombre_completo', 'like', "%$filter%")
                            ->orWhere('ci', 'like', "%$filter%");
                    })
                    ->orWhere('establecimiento_salud', 'like', "%$filter%");
            });
        }

        if (!empty($fecha)) {
            $query->whereDate('fecha_creacion', $fecha);
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function imprimirAnaliticaPublica($codigo)
    {
        $solicitud = Solicitude::with([
            'paciente',
            'doctor',
            'resultados',
            'servicios.area',
        ])->where('nro_registro', $codigo)->firstOrFail();

        $pdf = $this->buildPdfFromSolicitud($solicitud);

        return $pdf->stream('LAB_' . $solicitud->nro_registro . '.pdf');
    }

    public function imprimirAnalitica($id)
    {
        $solicitud = Solicitude::with([
            'paciente',
            'doctor',
            'resultados',
            'servicios.area',
            'userAnalitica'
        ])->findOrFail($id);

        $pdf = $this->buildPdfFromSolicitud($solicitud);

        return $pdf->stream('LAB_' . $solicitud->nro_registro . '.pdf');
    }

    protected function buildPdfFromSolicitud(Solicitude $solicitud)
    {
        $perfilHemograma = PerfilImpresion::where('codigo', 'HEMOGRAMA')
            ->with(['items.areaRango'])
            ->first();

        $perfilQuimica = PerfilImpresion::where('codigo', 'QUIMICA')
            ->with(['items.areaRango'])
            ->first();

        $hemoItems = $perfilHemograma
            ? $perfilHemograma->items->sortBy(['columna', 'seccion', 'orden'])
            : collect();

        $quimicaItems = $perfilQuimica
            ? $perfilQuimica->items->sortBy(['columna', 'seccion', 'orden'])
            : collect();

        $resultados = $solicitud->resultados;

        $pdf = Pdf::loadView('reportes.solicitud_media_carta', [
            'solicitud' => $solicitud,
            'hemoItems' => $hemoItems,
            'quimicaItems' => $quimicaItems,
            'perfilHemograma' => $perfilHemograma,
            'perfilQuimica' => $perfilQuimica,
            'resultados' => $resultados,
        ])->setPaper('letter', 'portrait');

        return $pdf;
    }

    public function generarCodigo(Request $request, $id)
    {
        $solicitud = Solicitude::findOrFail($id);

        if ($request->filled('tipo_atencion')) {
            $solicitud->tipo_atencion = $request->input('tipo_atencion');
        } elseif (empty($solicitud->tipo_atencion)) {
            $solicitud->tipo_atencion = 'SI';
        }

        if (empty($solicitud->fecha_creacion)) {
            $solicitud->fecha_creacion = now()->toDateString();
        }

        $paciente = Paciente::find($solicitud->paciente_id);
//        $nombreCompleto = $paciente ? $paciente->nombre_completo : 'Desconocido';
//        $nombreCompleto = $solicitud->paciente_nombre ?? ($paciente ? $paciente->nombre_completo : 'Desconocido');
//        $nro_registro = $this->nroRegistro($nombreCompleto, $paciente->fecha_nac ?? null);
//
//        $solicitud->codigo = $this->generarCodigoPorTipoYMes($solicitud);
//        $solicitud->nro_registro = $nro_registro;
        $solicitud->estado = 'ATENDIENDO';
        $solicitud->fecha_pre_analitica = now();
        $solicitud->user_preanalitica_id = $request->user() ? $request->user()->id : null;
        $solicitud->iniciales = $solicitud->user && $solicitud->user->establecimiento ? $solicitud->user->establecimiento->inicial : '';

        $solicitud->save();

        return response()->json($solicitud->fresh(['paciente', 'doctor', 'servicios']));
    }

    protected function generarCodigoPorTipoYMes(Solicitude $solicitud): int
    {
        $tipo = $solicitud->tipo_atencion ?? 'SI';

        $fechaBase = $solicitud->fecha_creacion ?: now()->toDateString();
        $timestamp = strtotime($fechaBase);

        $anio = date('Y', $timestamp);
        $mes = date('m', $timestamp);
        $establecimiento = $solicitud->user->establecimiento;

        if ($tipo === 'SI') {
            $ultimoCodigo = Solicitude::where('tipo_atencion', $tipo)
                ->whereYear('fecha_creacion', $anio)
                ->whereMonth('fecha_creacion', $mes)
                ->whereNotNull('codigo')
                ->where('iniciales',$establecimiento->inicial)
                ->max('codigo');
        }else{
            $ultimoCodigo = Solicitude::where('tipo_atencion', $tipo)
                ->whereYear('fecha_creacion', $anio)
                ->whereMonth('fecha_creacion', $mes)
                ->whereDate('fecha_creacion', date('Y-m-d', $timestamp))
                ->whereNotNull('codigo')
                ->where('iniciales',$establecimiento->inicial)
                ->max('codigo');
        }


        error_log("Último código para tipo $tipo en $anio-$mes: " . var_export($ultimoCodigo, true));

        return $ultimoCodigo ? ((int)$ultimoCodigo + 1) : 1;
    }

    public function guardarPreAnalitica(Request $request, $id)
    {
        $solicitud = Solicitude::findOrFail($id);
        $area_tipo_muestras = $request->input('area_tipo_muestras', []);

        $urlSocket = env('URL_SOCKET_IO', null);
        //return response()->json(['message' => 'URL_SOCKET_IO no está configurada', 'url' => $urlSocket], 500);
        $response = Http::get($urlSocket . '/silSolicitud');


        foreach ($area_tipo_muestras as $area) {
            if (isset($area['area_tipo_muestras']) && is_array($area['area_tipo_muestras'])) {
                foreach ($area['area_tipo_muestras'] as $tipoMuestra) {
                    $idTipo = $tipoMuestra['id'];
                    $existing = SolitudePreAnalitica::where('solicitude_id', $solicitud->id)
                        ->where('area_tipo_muestra_id', $idTipo)
                        ->first();

                    if (!$existing) {
                        $findArea = \App\Models\AreaTipoMuestra::find($idTipo);
                        $SolitudePreAnalitica = new SolitudePreAnalitica();
                        $SolitudePreAnalitica->solicitude_id = $solicitud->id;
                        $SolitudePreAnalitica->area_tipo_muestra_id = $idTipo;
                        $SolitudePreAnalitica->estado = 'Pendiente';
                        $SolitudePreAnalitica->nombre = $findArea ? $findArea->tipo_muestra : '';
                        $SolitudePreAnalitica->selected = !empty($tipoMuestra['selected']) ? true : false;
                        $SolitudePreAnalitica->save();
                    }
                }
            }
        }

        $solicitud->fecha_envio_analitica = now();
        $solicitud->estado = 'ENVIADO_ANALITICA';
//        'muestra_rechazada',
//        'muestra_observacion',
        $solicitud->muestra_rechazada = 'No';
        $solicitud->muestra_observacion = null;
        $solicitud->user_preanalitica_id = $request->user() ? $request->user()->id : null;
        $solicitud->save();

        return response()->json([
            'message' => 'Muestras preanalíticas actualizadas',
            'area_tipo_muestras' => $solicitud,
        ]);
    }
    function solicitudesAreaPreanaliticaEstado(Request $request){
        $filter = $request->input('filter', '');
        $fecha = $request->input('fecha', '');

        $query = Solicitude::with([
            'paciente',
            'doctor',
            'servicios.area.rangos',
            'preAnaliticaMuestras.areaTipoMuestra.area',
            'userPreanalitica',
            'userAnalitica',
            'user',
        ])
            ->whereDate('fecha_creacion', $fecha)
            ->whereIn('estado', ['ATENDIENDO','MUESTRA RECHAZADA','ENVIADO_ANALITICA','ANALIZADO','MUESTRA NO TOMADA']);


        $query->orderByRaw("FIELD(estado, 'ATENDIENDO', 'ENVIADO_ANALITICA', 'ANALIZADO', 'MUESTRA RECHAZADA', 'MUESTRA NO TOMADA') ASC");

        if (!empty($filter)) {
            $query->whereHas('paciente', function ($q) use ($filter) {
                $q->where('nombre_completo', 'like', "%$filter%")
                    ->orWhere('ci', 'like', "%$filter%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $solicitudes = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json($solicitudes);
    }

    public function solicitudesAreaPreanalitica(Request $request)
    {
        $filter = $request->input('filter', '');
        $fecha = $request->input('fecha', '');

        $query = Solicitude::with([
            'paciente',
            'doctor',
            'servicios.area.areaTipoMuestras',
            'userPreanalitica',
            'user',
            'solicitudRechazadas.user',
            'solicitudRechazadas.area',
        ])
            ->whereDate('fecha_creacion', $fecha)
            ->whereIn('estado', ['CREADO', 'ATENDIENDO','MUESTRA RECHAZADA']);

        if (!empty($filter)) {
            $query->whereHas('paciente', function ($q) use ($filter) {
                $q->where('nombre_completo', 'like', "%$filter%")
                    ->orWhere('ci', 'like', "%$filter%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $solicitudes = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json($solicitudes);
    }

    function nroRegistro($nombreCompleto, $fechaNac)
    {
        $nombreCompleto = trim((string)$nombreCompleto);

        if ($nombreCompleto === '' || empty($fechaNac)) {
            return null;
        }

        $partes = preg_split('/\s+/', mb_strtoupper($nombreCompleto, 'UTF-8'));

        if (count($partes) >= 3) {
            $nombre = $partes[0];
            $apPat = $partes[count($partes) - 2];
            $apMat = $partes[count($partes) - 1];
        } elseif (count($partes) === 2) {
            $nombre = $partes[0];
            $apPat = $partes[1];
            $apMat = $partes[1];
        } else {
            $nombre = $partes[0];
            $apPat = $partes[0];
            $apMat = $partes[0];
        }

        $iniciales =
            mb_substr($nombre, 0, 1, 'UTF-8') .
            mb_substr($apPat, 0, 1, 'UTF-8') .
            mb_substr($apMat, 0, 1, 'UTF-8');

        $timestamp = strtotime($fechaNac);
        if ($timestamp === false) {
            return $iniciales;
        }

        $fechaFormateada = date('dmy', $timestamp);

        return $iniciales . $fechaFormateada;
    }

    public function index(Request $request)
    {
        $query = Solicitude::with(['paciente', 'doctor', 'servicios']);

        if ($request->filled('from')) {
            $query->whereDate('fecha_creacion', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('fecha_creacion', '<=', $request->to);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo_atencion')) {
            $query->where('tipo_atencion', $request->tipo_atencion);
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function show($id)
    {
        return Solicitude::with(['paciente', 'doctor', 'servicios'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $servicios = $request->input('servicios', []);
        if (empty($servicios) || !is_array($servicios)) {
            return response()->json(['message' => 'Debe seleccionar al menos un servicio'], 422);
        }

        $data = $request->all();

        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

//        error_log('establecimiento_salud: ' . $request->establecimiento_salud);
        $establecimientoSalud = \App\Models\Establecimiento::where('nombre', $request->establecimiento_salud)->first();
//        error_log('EstablecimientoSalud: ' . json_encode($establecimientoSalud));
        if ($establecimientoSalud) {
//            error_log('entro');
            $data['establecimiento_id'] = $establecimientoSalud->id;
        }else{
            error_log('no entro');
        }

        $ci = $request->paciente_ci;
        $paciente = $this->pacienteUpsert($ci, $data);
        if ($paciente) {
            $data['paciente_id'] = $paciente->id;
        }

        $data['fecha_creacion'] = now();
        $data['establecimiento_origen_id'] = $request->user() && $request->user()->establecimiento ? $request->user()->establecimiento->id : null;

        if ($request->filled('doctor_id')) {
            $d = Doctor::find($request->doctor_id);
            if ($d) {
                $data['doctor_nombre'] = $data['doctor_nombre'] ?? $d->nombre;
                $data['doctor_especialidad'] = $data['doctor_especialidad'] ?? $d->especialidad;
                $data['doctor_ci'] = $data['doctor_ci'] ?? $d->ci;
                $data['doctor_telefono'] = $data['doctor_telefono'] ?? $d->telefono;
                $data['doctor_email'] = $data['doctor_email'] ?? $d->email;
                $data['doctor_registro'] = $data['doctor_registro'] ?? $d->registro;
            }
        }


        $solicitud = Solicitude::create($data);
        $nombreCompleto = $solicitud->paciente_nombre ?? ($paciente ? $paciente->nombre_completo : 'Desconocido');
        $nro_registro = $this->nroRegistro($nombreCompleto, $paciente->fecha_nac ?? null);

        $solicitud->codigo = $this->generarCodigoPorTipoYMes($solicitud);
        $solicitud->nro_registro = $nro_registro;
        $solicitud->save();

//        $this->syncServicios($solicitud, $request->input('servicios', []));
        $servicios = $request->input('servicios', []);
        foreach ($servicios as $servicio) {
//            $servicioSolicitud = Servicio::find($servicio['id']);
            $newServicioSolicitud = new ServicioSolicitude();
            $newServicioSolicitud->solicitude_id = $solicitud->id;
            $newServicioSolicitud->servicio_id = $servicio['id'];
            $newServicioSolicitud->area_id = $servicio['area_id'];
            $newServicioSolicitud->precio = $servicio['precio'] ?? null;
            $newServicioSolicitud->nombre = $servicio['nombre'] ?? '';
            $newServicioSolicitud->save();
        }

        return response()->json($solicitud->load(['paciente', 'doctor', 'servicios']), 201);
    }

    protected function pacienteUpsert($ci, &$data)
    {
        if (empty($ci) || $ci === '') {
            $p = Paciente::create([
                'nombre_completo' => $data['paciente_nombre'],
                'ci' => $ci,
                'telefono' => $data['paciente_telefono'] ?? null,
                'direccion' => $data['paciente_direccion'] ?? null,
                'fecha_nac' => $data['paciente_fecha_nac'] ?? null,
                'genero' => $data['paciente_genero'] ?? null,
                'edad' => $data['paciente_edad'] ?? null,
            ]);
            $data['paciente_id'] = $p->id;
            return $p;
        }

        $p = Paciente::where('ci', $ci)->first();
        if ($p) {
            $p->nombre_completo = $data['paciente_nombre'] ?? $p->nombre_completo;
            $p->telefono = $data['paciente_telefono'] ?? $p->telefono;
            $p->direccion = $data['paciente_direccion'] ?? $p->direccion;
            $p->fecha_nac = $data['paciente_fecha_nac'] ?? $p->fecha_nac;
            $p->genero = $data['paciente_genero'] ?? $p->genero;
            $p->edad = $data['paciente_edad'] ?? $p->edad;
            $p->save();
        } else {
            $p = Paciente::create([
                'nombre_completo' => $data['paciente_nombre'],
                'ci' => $ci,
                'telefono' => $data['paciente_telefono'] ?? null,
                'direccion' => $data['paciente_direccion'] ?? null,
                'fecha_nac' => $data['paciente_fecha_nac'] ?? null,
                'genero' => $data['paciente_genero'] ?? null,
                'edad' => $data['paciente_edad'] ?? null,
            ]);
        }

        $data['paciente_id'] = $p->id;
        return $p;
    }

    public function update(Request $request, $id)
    {
        $solicitud = Solicitude::findOrFail($id);
//        if (!empty($solicitud->codigo)) {
//            return response()->json(['message' => 'No se puede modificar una solicitud que ya tiene código asignado'], 422);
//        }
        $data = $request->all();

        $ci = $request->paciente_ci;
        if (!empty($ci)) {
            $paciente = $this->pacienteUpsert($ci, $data);
            if ($paciente) {
                $data['paciente_id'] = $paciente->id;
            }
        }

        if ($request->filled('doctor_id')) {
            $d = Doctor::find($request->doctor_id);
            if ($d) {
                $data['doctor_nombre'] = $data['doctor_nombre'] ?? $d->nombre;
                $data['doctor_especialidad'] = $data['doctor_especialidad'] ?? $d->especialidad;
                $data['doctor_ci'] = $data['doctor_ci'] ?? $d->ci;
                $data['doctor_telefono'] = $data['doctor_telefono'] ?? $d->telefono;
                $data['doctor_email'] = $data['doctor_email'] ?? $d->email;
                $data['doctor_registro'] = $data['doctor_registro'] ?? $d->registro;
            }
        }

        $servicios = $request->input('servicios', []);
//        delted servicos antguos
        ServicioSolicitude::where('solicitude_id', $solicitud->id)->delete();
        foreach ($servicios as $servicio) {
//            $servicioSolicitud = Servicio::find($servicio['id']);
            $newServicioSolicitud = new ServicioSolicitude();
            $newServicioSolicitud->solicitude_id = $solicitud->id;
            $newServicioSolicitud->servicio_id = $servicio['id'];
            $newServicioSolicitud->area_id = $servicio['area_id'];
            $newServicioSolicitud->precio = $servicio['precio'] ?? null;
            $newServicioSolicitud->nombre = $servicio['nombre'] ?? '';
            $newServicioSolicitud->save();
        }

        $this->syncServicios($solicitud, $request->input('servicios', []));
//        solitud uupte
        $solicitud->update($data);

        return response()->json($solicitud->load(['paciente', 'doctor', 'servicios']));
    }

    protected function syncServicios(Solicitude $solicitud, array $servicios)
    {
        $pivotData = [];

        foreach ($servicios as $serv) {
            if (!isset($serv['id'])) {
                continue;
            }
            $pivotData[$serv['id']] = [
                'precio' => $serv['precio'] ?? null,
            ];
        }

        $solicitud->servicios()->sync($pivotData);
    }

    public function destroy($id)
    {
        $solicitud = Solicitude::findOrFail($id);
        $solicitud->delete();

        return response()->json(['message' => 'Solicitud eliminada correctamente']);
    }

    public function solicitudesAreaAnalitica(Request $request)
    {
        $filter = $request->input('filter', '');

        $query = Solicitude::with([
            'paciente',
            'doctor',
            'servicios.area.rangos',
            'preAnaliticaMuestras.areaTipoMuestra.area',
            'userPreanalitica',
            'userAnalitica',
            'user',
        ])
            ->whereIn('estado', ['ENVIADO_ANALITICA', 'ANALITICA_ATENDIENDO', 'FINALIZADO']);

        if (!empty($filter)) {
            $query->where(function ($q) use ($filter) {
                $q->where('paciente_nombre', 'like', "%$filter%")
                    ->orWhereHas('paciente', function ($q2) use ($filter) {
                        $q2->where('nombre_completo', 'like', "%$filter%")
                            ->orWhere('ci', 'like', "%$filter%");
                    })
                    ->orWhere('establecimiento_salud', 'like', "%$filter%");
            });
        }

        $perPage = $request->input('per_page', 10);
        $solicitudes = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json($solicitudes);
    }

    public function showAnalitica($id)
    {
        $solicitud = Solicitude::with([
            'paciente',
            'doctor',
            'servicios.area.rangos',
            'resultados',
            'preAnaliticaMuestras.areaTipoMuestra.area',
            'propiedades',
            'userPreanalitica',
            'solicitudeFormularios'
        ])->findOrFail($id);

        return response()->json($solicitud);
    }

    protected function parseValorNullable($value): ?float
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $value = str_replace(',', '.', trim($value));
            if ($value === '') {
                return null;
            }
        }

        if (!is_numeric($value)) {
            return null;
        }

        return (float)$value;
    }

    public function guardarAnalitica(Request $request, $id)
    {
        $solicitud = Solicitude::findOrFail($id);
        $resultados = $request->input('resultados', []);
        $propiedadesArea = $request->input('propiedades_area', []); // <-- NUEVO
//        formularios: this.solicitud.solicitude_formularios || []
        $fomularios = $request->input('formularios', []);

        DB::transaction(function () use ($solicitud, $resultados, $propiedadesArea, $fomularios) {
//            SolicitudeFormulario deletes
            SolicitudeFormulario::where('solicitude_id', $solicitud->id)->delete();
            foreach ($fomularios as $formularioData) {
                $solicitudeFormulario = SolicitudeFormulario::updateOrCreate(
                    [
                        'solicitude_id' => $solicitud->id,
                        'formulario_id' => $formularioData['formulario_id'],
                        'area_id' => $formularioData['area_id'] ?? null,
                    ],
                    [
                        'nombre' => $formularioData['nombre'] ?? null,
                        'html' => $formularioData['html'] ?? null,
                    ]
                );
            }

            foreach ($resultados as $areaId => $rangos) {
                if (!is_array($rangos)) {
                    continue;
                }

                $areaId = (int)$areaId;

                foreach ($rangos as $rangoId => $payload) {
                    if (!is_array($payload)) {
                        continue;
                    }

                    $rangoId = (int)$rangoId;

                    // Área 1: Hematología (auto / manual)
                    if ($areaId === 1) {
                        $valorAuto = $this->parseValorNullable($payload['valor_automatizado'] ?? null);
                        $valorManual = $this->parseValorNullable($payload['valor_manual'] ?? null);

                        if ($valorAuto === null && $valorManual === null) {
                            ResultadoLaboratorio::where('solicitude_id', $solicitud->id)
                                ->where('area_rango_id', $rangoId)
                                ->delete();
                            continue;
                        }

                        $valorFinal = null;
                        $preferido = null;
                        $metodoFinal = null;

                        if ($valorManual !== null) {
                            $valorFinal = $valorManual;
                            $preferido = 'MAN';
                            $metodoFinal = 'MANUAL';
                        } elseif ($valorAuto !== null) {
                            $valorFinal = $valorAuto;
                            $preferido = 'AUTO';
                            $metodoFinal = 'AUTOMATIZADO';
                        }

                        ResultadoLaboratorio::updateOrCreate(
                            [
                                'solicitude_id' => $solicitud->id,
                                'area_rango_id' => $rangoId,
                            ],
                            [
                                'area_id' => $areaId,
                                'valor_automatizado' => $valorAuto,
                                'valor_manual' => $valorManual,
                                'valor_final' => $valorFinal,
                                'preferido' => $preferido,
                                'metodo_final' => $metodoFinal,
                            ]
                        );
                    } else {
                        // Otras áreas: solo valor_final
                        $valor = $this->parseValorNullable($payload['valor'] ?? null);

                        if ($valor === null) {
                            ResultadoLaboratorio::where('solicitude_id', $solicitud->id)
                                ->where('area_rango_id', $rangoId)
                                ->delete();
                            continue;
                        }

                        ResultadoLaboratorio::updateOrCreate(
                            [
                                'solicitude_id' => $solicitud->id,
                                'area_rango_id' => $rangoId,
                            ],
                            [
                                'area_id' => $areaId,
                                'valor_final' => $valor,
                                'metodo_final' => null,
                                'valor_automatizado' => null,
                                'valor_manual' => null,
                                'preferido' => null,
                            ]
                        );
                    }
                }
            }

            // Guardar propiedades extra por área
            $this->guardarPropiedadesArea($solicitud, $propiedadesArea);

            $solicitud->estado = 'FINALIZADO';
            $solicitud->user_analitica_id = auth()->id();
            $solicitud->fecha_envio_analitica = now();
            if (empty($solicitud->fecha_finalizacion)) {
                $solicitud->fecha_finalizacion = now();
            }
            $solicitud->save();
        });

        return response()->json([
            'message' => 'Resultados de analítica guardados correctamente.',
        ]);
    }

    /**
     * Guarda las propiedades extra por área (sangre, suero, sala/cama, etc.)
     *
     * Estructura esperada:
     *  propiedades_area: {
     *    "1": { "aceptada": "ACEPTADA", "coagulo": "NO", "equipo": "Mindray C3510", ... },
     *    "2": { "aceptada": "ACEPTADA", "hemolizada": "NO", ... },
     *    "3": { "sala": "3A", "cama": "12", "paciente_ambulatorio": "SI", ... }
     *  }
     */
    protected function guardarPropiedadesArea(Solicitude $solicitud, array $propiedadesArea): void
    {
        foreach ($propiedadesArea as $areaId => $campos) {
            if (!is_array($campos)) {
                continue;
            }

            $areaId = (int)$areaId;

            foreach ($campos as $campo => $valor) {
                $campo = trim((string)$campo);
                if ($campo === '') {
                    continue;
                }

                if ($valor === null || $valor === '') {
                    SolicitudePropiedad::where('solicitude_id', $solicitud->id)
                        ->where('area_id', $areaId)
                        ->where('campo', $campo)
                        ->delete();
                    continue;
                }

                SolicitudePropiedad::updateOrCreate(
                    [
                        'solicitude_id' => $solicitud->id,
                        'area_id' => $areaId,
                        'campo' => $campo,
                    ],
                    [
                        'valor' => (string)$valor,
                    ]
                );
            }
        }
    }
}
