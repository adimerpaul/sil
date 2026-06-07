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
        $from   = $request->get('from', Carbon::now()->startOfMonth()->toDateString());
        $to     = $request->get('to', Carbon::now()->endOfMonth()->toDateString());
        $search = trim((string) $request->get('search', ''));
        $estado = $request->get('estado', ''); // 'entregado' | 'pendiente' | ''
        $perPage = min((int) $request->get('per_page', 20), 300);
        $page    = max((int) $request->get('page', 1), 1);

        $query = Solicitude::with([
            'servicios' => fn($q) => $q->with('area'),
            'entregaResultado.user',
        ])
        ->whereDate('fecha_solicitud', '>=', $from)
        ->whereDate('fecha_solicitud', '<=', $to)
        ->whereHas('servicioSolicitudes', fn($q) => $q->where('realizado', 'REALIZADO'))
        ->when($search !== '', function ($q) use ($search) {
            $isNumeric = ctype_digit($search);
            $q->where(function ($w) use ($search, $isNumeric) {
                if ($isNumeric) {
                    $w->where('codigo', 'like', "%{$search}%");
                } else {
                    $w->where('paciente_nombre', 'like', "%{$search}%")
                      ->orWhere('paciente_ci', 'like', "%{$search}%")
                      ->orWhere('doctor_nombre', 'like', "%{$search}%");
                }
            });
        })
        ->when($estado === 'entregado', fn($q) => $q->whereHas('entregaResultado'))
        ->when($estado === 'pendiente', fn($q) => $q->whereDoesntHave('entregaResultado'))
        ->orderByDesc('fecha_solicitud')
        ->orderByDesc('id');

        $total = $query->count();
        $rows  = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        return response()->json([
            'rows' => $rows,
            'pagination' => [
                'total'    => $total,
                'page'     => $page,
                'per_page' => $perPage,
            ],
        ]);
    }

    public function registrar(Request $request)
    {
        $request->validate([
            'solicitude_ids'   => 'required|array|min:1',
            'solicitude_ids.*' => 'integer|exists:solicitudes,id',
            'observaciones'    => 'nullable|string|max:255',
        ]);

        $user  = auth()->user();
        $now   = Carbon::now();
        $fecha = $now->toDateString();
        $hora  = $now->format('H:i:s');

        foreach ($request->solicitude_ids as $id) {
            EntregaResultado::updateOrCreate(
                ['solicitude_id' => $id],
                [
                    'user_id'        => $user->id,
                    'fecha_entrega'  => $fecha,
                    'hora_entrega'   => $hora,
                    'observaciones'  => $request->observaciones,
                ]
            );
        }

        return response()->json(['message' => 'Entrega registrada correctamente', 'count' => count($request->solicitude_ids)]);
    }
}
