<?php

namespace App\Http\Controllers;

use App\Models\Solicitude;
use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * "Mis laboratorios": vista del doctor. Muestra las solicitudes de la semana
 * pedidas por los doctores vinculados al usuario, su estado de realización y
 * los PDF de resultados, y registra cuándo el doctor los vio y los aceptó.
 */
class MisLaboratorioController extends Controller
{
    private const PERMISO = 'Mis laboratorios';

    private function autorizar(Request $request)
    {
        $user = $request->user();

        if (! $user || ($user->role !== 'Administrador' && ! $user->can(self::PERMISO))) {
            abort(403, 'No tiene permiso para ver Mis laboratorios');
        }

        return $user;
    }

    /** Solicitud que pertenece a un doctor vinculado al usuario */
    private function solicitudDelDoctor(Request $request, $solicitudId): Solicitude
    {
        $user = $this->autorizar($request);
        $doctorIds = $user->doctores()->pluck('doctors.id');

        return Solicitude::whereIn('doctor_id', $doctorIds)->findOrFail($solicitudId);
    }

    /**
     * GET /mis-laboratorios?desde=&hasta=&filtro=&estado=&page=&per_page=
     * Por defecto el mes en curso. per_page <= 0 trae todo el rango.
     * El buscador y el estado se resuelven en la consulta para que la
     * paginación cuente sobre el total filtrado y no sobre la página cargada.
     */
    public function index(Request $request)
    {
        $user = $this->autorizar($request);

        $request->validate([
            'desde' => 'nullable|date',
            'hasta' => 'nullable|date',
            'filtro' => 'nullable|string|max:100',
            'estado' => 'nullable|in:todos,listos,proceso',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer',
        ]);

        $desde = $request->filled('desde')
            ? Carbon::parse($request->input('desde'))->startOfDay()
            : Carbon::now()->startOfMonth();
        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->input('hasta'))->endOfDay()
            : (clone $desde)->endOfMonth();

        // per_page <= 0 significa "todos": se pagina con el total de filas.
        $perPage = (int) $request->input('per_page', 10);
        $todos = $perPage <= 0;
        if (! $todos) {
            $perPage = min($perPage, 100);
        }

        $doctorIds = $user->doctores()->pluck('doctors.id');

        if ($doctorIds->isEmpty()) {
            return response()->json([
                'desde' => $desde->toDateString(),
                'hasta' => $hasta->toDateString(),
                'doctores' => [],
                'solicitudes' => [],
                'total' => 0,
                'page' => 1,
                'per_page' => $todos ? 0 : $perPage,
                'last_page' => 1,
                'mensaje' => 'Su usuario no tiene doctores vinculados.',
            ]);
        }

        $query = Solicitude::query()
            ->select([
                'id', 'codigo', 'nro_registro', 'estado', 'doctor_id',
                'fecha_creacion', 'fecha_solicitud', 'hora_solicitud', 'fecha_finalizacion',
                'paciente_nombre', 'paciente_ci', 'paciente_edad', 'paciente_genero',
                'doctor_nombre', 'establecimiento_salud', 'sala', 'cama',
                'inmunologia_analitica_codigo', 'muestra_rechazada', 'motivo_rechazo',
                'doctor_visto_at', 'doctor_aceptado_at',
            ])
            ->with([
                'servicios' => fn ($q) => $q
                    ->select('servicios.id', 'servicios.area_id', 'servicios.nombre')
                    ->with('area:id,title'),
                'hematologia:id,solicitude_id,code',
                'quimicaSanguinea:id,solicitude_id,code',
                'uroanalisis:id,solicitude_id,code',
                'parasitologia:id,solicitude_id,code',
                'papilomaHumano:id,solicitude_id,code',
                'panelRespiratorio:id,solicitude_id,code',
                'panelSexual:id,solicitude_id,code',
                'cultivoAntibiograma:id,solicitude_id,code',
            ])
            ->whereIn('doctor_id', $doctorIds)
            ->whereDate('fecha_creacion', '>=', $desde->toDateString())
            ->whereDate('fecha_creacion', '<=', $hasta->toDateString());

        $filtro = trim((string) $request->input('filtro', ''));
        if ($filtro !== '') {
            $query->where(function ($q) use ($filtro) {
                $q->where('paciente_nombre', 'like', "%$filtro%")
                    ->orWhere('paciente_ci', 'like', "%$filtro%")
                    ->orWhere('codigo', 'like', "%$filtro%")
                    ->orWhere('nro_registro', 'like', "%$filtro%")
                    ->orWhere('sala', 'like', "%$filtro%");
            });
        }

        // "listos" = todas las prestaciones realizadas; "proceso" = queda alguna pendiente
        $estado = $request->input('estado', 'todos');
        $pendiente = fn ($q) => $q->where('servicio_solicitudes.realizado', '!=', 'REALIZADO');
        if ($estado === 'listos') {
            $query->has('servicios')->whereDoesntHave('servicios', $pendiente);
        } elseif ($estado === 'proceso') {
            $query->whereHas('servicios', $pendiente);
        }

        $query->orderBy('fecha_creacion', 'desc')->orderBy('id', 'desc');

        if ($todos) {
            $perPage = max(1, (clone $query)->count());
        }

        $pagina = $query->paginate($perPage, ['*'], 'page', (int) $request->input('page', 1));

        return response()->json([
            'desde' => $desde->toDateString(),
            'hasta' => $hasta->toDateString(),
            'doctores' => $user->doctores()->get(['doctors.id', 'doctors.nombre']),
            'solicitudes' => collect($pagina->items())->map(fn ($solicitud) => $this->formatear($solicitud)),
            'total' => $pagina->total(),
            'page' => $pagina->currentPage(),
            'per_page' => $todos ? 0 : $pagina->perPage(),
            'last_page' => $pagina->lastPage(),
        ]);
    }

    /** POST /mis-laboratorios/{solicitud}/visto */
    public function marcarVisto(Request $request, $solicitudId)
    {
        $solicitud = $this->solicitudDelDoctor($request, $solicitudId);

        // Se guarda la primera vez que lo vio; después no se pisa.
        if (! $solicitud->doctor_visto_at) {
            $solicitud->doctor_visto_at = now();
            $solicitud->save();
        }

        return response()->json([
            'doctor_visto_at' => $solicitud->doctor_visto_at,
            'doctor_aceptado_at' => $solicitud->doctor_aceptado_at,
        ]);
    }

    /** POST /mis-laboratorios/{solicitud}/aceptar — el doctor confirma que recibió el resultado */
    public function aceptar(Request $request, $solicitudId)
    {
        $solicitud = $this->solicitudDelDoctor($request, $solicitudId);

        if (! $solicitud->doctor_visto_at) {
            $solicitud->doctor_visto_at = now();
        }
        $solicitud->doctor_aceptado_at = now();
        $solicitud->save();

        return response()->json([
            'doctor_visto_at' => $solicitud->doctor_visto_at,
            'doctor_aceptado_at' => $solicitud->doctor_aceptado_at,
        ]);
    }

    private function formatear(Solicitude $solicitud): array
    {
        $servicios = $solicitud->servicios->map(fn ($servicio) => [
            'id' => $servicio->id,
            'nombre' => $servicio->nombre,
            'area' => $servicio->area->title ?? null,
            'realizado' => $servicio->pivot->realizado,
        ]);

        $realizados = $servicios->where('realizado', 'REALIZADO')->count();

        return [
            'id' => $solicitud->id,
            'codigo' => $solicitud->codigo,
            'nro_registro' => $solicitud->nro_registro,
            'estado' => $solicitud->estado,
            'fecha_creacion' => $solicitud->fecha_creacion,
            'fecha_solicitud' => $solicitud->fecha_solicitud,
            'hora_solicitud' => $solicitud->hora_solicitud,
            'fecha_finalizacion' => $solicitud->fecha_finalizacion,
            'paciente_nombre' => $solicitud->paciente_nombre,
            'paciente_ci' => $solicitud->paciente_ci,
            'paciente_edad' => $solicitud->paciente_edad,
            'paciente_genero' => $solicitud->paciente_genero,
            'doctor_nombre' => $solicitud->doctor_nombre,
            'establecimiento_salud' => $solicitud->establecimiento_salud,
            'sala' => $solicitud->sala,
            'cama' => $solicitud->cama,
            'muestra_rechazada' => $solicitud->muestra_rechazada,
            'motivo_rechazo' => $solicitud->motivo_rechazo,
            'servicios' => $servicios,
            'total_servicios' => $servicios->count(),
            'servicios_realizados' => $realizados,
            'todo_realizado' => $servicios->count() > 0 && $realizados === $servicios->count(),
            'doctor_visto_at' => $solicitud->doctor_visto_at,
            'doctor_aceptado_at' => $solicitud->doctor_aceptado_at,
            'pdfs' => $this->pdfs($solicitud),
        ];
    }

    /** Enlaces públicos a los PDF de resultado que ya existen para la solicitud */
    private function pdfs(Solicitude $solicitud): array
    {
        $modulos = [
            ['Hematología', 'hematologia', '/api/hematologia/solicitud/%s/pdf'],
            ['Química sanguínea', 'quimicaSanguinea', '/api/quimica-sanguinea/solicitud/%s/pdf'],
            ['Uroanálisis', 'uroanalisis', '/api/uroanalisis/solicitud/%s/pdf'],
            ['Parasitología', 'parasitologia', '/api/parasitologia/solicitud/%s/pdf'],
            ['Papiloma humano', 'papilomaHumano', '/api/papiloma-humano/solicitud/%s/pdf'],
            ['Panel respiratorio', 'panelRespiratorio', '/api/panel-respiratorio/solicitud/%s/pdf'],
            ['Panel sexual', 'panelSexual', '/api/panel-sexual/solicitud/%s/pdf'],
            ['Cultivo y antibiograma', 'cultivoAntibiograma', '/api/cultivo-antibiograma/solicitud/%s/pdf'],
        ];

        $pdfs = [];

        foreach ($modulos as [$etiqueta, $relacion, $ruta]) {
            $code = $solicitud->{$relacion}->code ?? null;
            if ($code) {
                $pdfs[] = ['label' => $etiqueta, 'url' => url(sprintf($ruta, $code))];
            }
        }

        if ($solicitud->inmunologia_analitica_codigo) {
            $pdfs[] = [
                'label' => 'Inmunología',
                'url' => url('/api/inmunologia-analitica/resultado/'.$solicitud->inmunologia_analitica_codigo.'/pdf'),
            ];
        }

        return $pdfs;
    }
}
