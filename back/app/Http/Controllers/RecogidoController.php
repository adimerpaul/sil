<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Recogido;
use App\Models\Solicitude;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RecogidoController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $search = trim((string) $request->get('search', ''));
        $from = $request->get('from');
        $to = $request->get('to');
        $perPage = (int) $request->get('per_page', 10);
        $perPage = $perPage > 0 ? min($perPage, 100) : 10;

        $areaId = $request->filled('area_id') ? (int) $request->get('area_id') : null;
        if (($user->role ?? null) !== 'Administrador') {
            $areaId = (int) ($user->area_id ?? 0);
            if ($areaId <= 0) {
                return response()->json([
                    'rows' => [],
                    'pagination' => [
                        'page' => 1,
                        'per_page' => $perPage,
                        'rows_number' => 0,
                        'last_page' => 1,
                    ],
                    'area' => null,
                ]);
            }
        }

        $query = Solicitude::query()
            ->with([
                'doctor',
                'servicioSolicitudes' => function ($q) use ($areaId) {
                    $q->with(['servicio.area', 'area'])
                        ->when($areaId, fn($x) => $x->where('area_id', $areaId))
                        ->orderBy('id', 'asc');
                },
            ])
            ->whereHas('servicioSolicitudes', function ($q) use ($areaId) {
                $q->when($areaId, fn($x) => $x->where('area_id', $areaId));
            })
            ->when($from, fn($q) => $q->whereDate('fecha_solicitud', '>=', $from))
            ->when($to, fn($q) => $q->whereDate('fecha_solicitud', '<=', $to))
            ->when($search !== '', function ($q) use ($search) {
                $q->where(function ($w) use ($search) {
                    $w->where('paciente_nombre', 'like', "%{$search}%")
                        ->orWhere('paciente_ci', 'like', "%{$search}%")
                        ->orWhere('doctor_nombre', 'like', "%{$search}%")
                        ->orWhere('nro_registro', 'like', "%{$search}%")
                        ->orWhere('codigo', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('id');

        $rows = $query->paginate($perPage);

        return response()->json([
            'rows' => $rows->items(),
            'pagination' => [
                'page' => $rows->currentPage(),
                'per_page' => $rows->perPage(),
                'rows_number' => $rows->total(),
                'last_page' => $rows->lastPage(),
            ],
            'area' => $areaId ? Area::find($areaId) : null,
        ]);
    }

    public function update(Request $request, $id)
    {
        $row = Recogido::with(['servicio.area', 'solicitud'])->findOrFail($id);

        $user = $request->user();
        if (($user->role ?? null) !== 'Administrador' && (int) $row->area_id !== (int) $user->area_id) {
            return response()->json(['message' => 'No autorizado para actualizar este servicio'], 403);
        }

        $data = $request->validate([
            'fue_recogido' => 'required|boolean',
            'recogido_por_personal' => 'nullable|string|max:255',
            'grado_parentesco' => 'nullable|string|max:120',
            'telefono_recogido' => 'nullable|string|max:40',
            'ci_recogido' => 'nullable|string|max:40',
            'recogido_en_dia' => 'nullable|date',
        ]);
        $data['recogido_en_dia'] = $data['recogido_en_dia'] ? date('Y-m-d H:i:s', strtotime($data['recogido_en_dia'])) : null;
        if ($data['fue_recogido']) {
            $request->validate([
                'recogido_por_personal' => 'required|string|max:255',
                'grado_parentesco' => 'required|string|max:120',
                'telefono_recogido' => 'required|string|max:40',
                'ci_recogido' => 'required|string|max:40',
                'recogido_en_dia' => 'required|date',
            ]);
        } else {
            $data['recogido_por_personal'] = null;
            $data['grado_parentesco'] = null;
            $data['telefono_recogido'] = null;
            $data['ci_recogido'] = null;
            $data['recogido_en_dia'] = null;
        }

        $row->update($data);

        return response()->json($row->fresh(['servicio.area', 'area', 'solicitud']));
    }

    public function recogerArea(Request $request)
    {
        $data = $request->validate([
            'solicitude_id' => 'required|integer|exists:solicitudes,id',
            'area_id' => 'required|integer|exists:areas,id',
            'fue_recogido' => 'required|boolean',
            'recogido_por_personal' => 'nullable|string|max:255',
            'grado_parentesco' => 'nullable|string|max:120',
            'telefono_recogido' => 'nullable|string',
            'ci_recogido' => 'nullable|string|max:40',
            'recogido_en_dia' => 'nullable|date',
        ]);

        if ($data['fue_recogido']) {
            $request->validate([
                'recogido_por_personal' => 'required|string|max:255',
                'grado_parentesco' => 'nullable|string|max:120',
                'telefono_recogido' => 'nullable|string|max:40',
                'ci_recogido' => 'nullable|string|max:40',
                'recogido_en_dia' => 'nullable|date',
            ]);
        } else {
            $data['recogido_por_personal'] = null;
            $data['grado_parentesco'] = null;
            $data['telefono_recogido'] = null;
            $data['ci_recogido'] = null;
            $data['recogido_en_dia'] = null;
        }

        $user = $request->user();
        if (($user->role ?? null) !== 'Administrador' && (int) $data['area_id'] !== (int) $user->area_id) {
            return response()->json(['message' => 'No autorizado para actualizar esta area'], 403);
        }

        $query = Recogido::query()
            ->where('solicitude_id', $data['solicitude_id'])
            ->where('area_id', $data['area_id']);

        $count = (clone $query)->count();
        if ($count === 0) {
            return response()->json(['message' => 'No existen servicios de esta area para la solicitud'], 404);
        }

        DB::transaction(function () use ($query, $data) {
            $query->update([
                'fue_recogido' => $data['fue_recogido'],
                'recogido_por_personal' => $data['recogido_por_personal'],
                'grado_parentesco' => $data['grado_parentesco'],
                'telefono_recogido' => $data['telefono_recogido'],
                'ci_recogido' => $data['ci_recogido'],
                'recogido_en_dia' => $data['recogido_en_dia'],
            ]);
        });

        $rows = Recogido::with(['servicio.area', 'area'])
            ->where('solicitude_id', $data['solicitude_id'])
            ->where('area_id', $data['area_id'])
            ->orderBy('id', 'asc')
            ->get();

        return response()->json([
            'message' => 'Recogido actualizado por area',
            'solicitude_id' => (int) $data['solicitude_id'],
            'area_id' => (int) $data['area_id'],
            'updated_count' => $count,
            'rows' => $rows,
        ]);
    }
}
