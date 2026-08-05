<?php

namespace App\Http\Controllers;

use App\Models\EntregaResultado;
use App\Models\Solicitude;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class EntregaResultadoController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->get('from', Carbon::now()->startOfMonth()->toDateString());
        $to = $request->get('to', Carbon::now()->endOfMonth()->toDateString());
        $search = trim((string) $request->get('search', ''));
        $estado = $request->get('estado', '');

        // Solo se seleccionan las columnas que realmente usa la vista.
        // El modelo Solicitude tiene ~70 columnas (copias de paciente/doctor);
        // devolverlas todas hacía que la respuesta pesara >1MB sin necesidad.
        $rows = Solicitude::query()
            ->select('solicitudes.id', 'solicitudes.codigo')
            ->with([
                // solo el área de cada servicio
                'servicios' => fn ($q) => $q->select('servicios.id', 'servicios.area_id')
                    ->with('area:id,name'),
                // solo lo que se muestra de la entrega
                'entregaResultados' => fn ($q) => $q->select('id', 'solicitude_id', 'area', 'hora_entrega', 'user_id')
                    ->with('user:id,name'),
            ])
            ->whereDate('fecha_solicitud', '>=', $from)
            ->whereDate('fecha_solicitud', '<=', $to)
            ->whereHas('servicioSolicitudes', fn ($q) => $q->where('realizado', 'REALIZADO'))
            ->when($search !== '', function ($q) use ($search) {
                $isNumeric = ctype_digit($search);
                $q->where(function ($w) use ($search, $isNumeric) {
                    if ($isNumeric) {
                        $w->where('codigo', 'like', "%{$search}%");
                    } else {
                        $w->where('paciente_nombre', 'like', "%{$search}%")
                            ->orWhere('paciente_ci', 'like', "%{$search}%");
                    }
                });
            })
            ->when($estado === 'entregado', fn ($q) => $q->whereHas('entregaResultados'))
        // pendiente = tiene al menos un área cuyos resultados aún no fueron entregados
        // (la entrega se registra por área, no por solicitud completa)
            ->when($estado === 'pendiente', fn ($q) => $q->whereRaw('EXISTS (
            SELECT 1 FROM servicio_solicitudes ss
            JOIN servicios sv ON sv.id = ss.servicio_id
            JOIN areas a ON a.id = sv.area_id
            WHERE ss.solicitude_id = solicitudes.id
              AND NOT EXISTS (
                SELECT 1 FROM entrega_resultados er
                WHERE er.solicitude_id = solicitudes.id
                  AND er.area = a.name
              )
        )'))
            ->orderByDesc('fecha_solicitud')
            ->orderByDesc('id')
            ->get()
            // Se arma un DTO mínimo: la vista solo necesita el código, las áreas
            // distintas de la solicitud y, por entrega, el área/hora/usuario.
            ->map(fn (Solicitude $s) => [
                'id' => $s->id,
                'codigo' => $s->codigo,
                'areas' => $s->servicios
                    ->map(fn ($sv) => $sv->area?->name ?? 'Sin área')
                    ->unique()
                    ->values(),
                'entrega_resultados' => $s->entregaResultados->map(fn ($e) => [
                    'area' => $e->area,
                    'hora_entrega' => $e->hora_entrega,
                    'user' => ['name' => $e->user?->name],
                ]),
            ]);

        // Sin paginación: el filtro por rango de fechas ya acota los datos.
        return response()->json([
            'rows' => $rows,
            'pagination' => [
                'total' => $rows->count(),
            ],
        ]);
    }

    public function registrar(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.solicitude_id' => 'required|integer|exists:solicitudes,id',
            'items.*.area' => 'required|string|max:100',
        ]);

        $user = auth()->user();
        $now = Carbon::now();
        $fecha = $now->toDateString();
        $hora = $now->format('H:i:s');

        foreach ($request->items as $item) {
            EntregaResultado::updateOrCreate(
                [
                    'solicitude_id' => $item['solicitude_id'],
                    'area' => $item['area'],
                ],
                [
                    'user_id' => $user->id,
                    'fecha_entrega' => $fecha,
                    'hora_entrega' => $hora,
                ]
            );
        }

        return response()->json([
            'message' => 'Entrega registrada correctamente',
            'count' => count($request->items),
        ]);
    }
}
