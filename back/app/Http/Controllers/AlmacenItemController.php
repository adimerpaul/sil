<?php

namespace App\Http\Controllers;

use App\Models\AlmacenItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AlmacenItemController extends Controller
{
    public function index(Request $request)
    {
        $query = AlmacenItem::with('subpartida.partida.grupo')
            ->select('almacen_items.*')
            ->selectSub($this->cantidadSubquery(), 'cantidad');

        $this->applyFilters($query, $request);

        if ($request->boolean('existente')) {
            $query->having('cantidad', '>', 0);
        }

        $sortBy = $request->input('sortBy', 'nombre');
        $descending = filter_var($request->input('descending', false), FILTER_VALIDATE_BOOLEAN);
        $rowsPerPage = (int) $request->input('rowsPerPage', 15);

        if (! in_array($sortBy, ['nombre', 'unidad_medida', 'precio_unitario', 'cantidad', 'id'], true)) {
            $sortBy = 'nombre';
        }

        $query->orderBy($sortBy, $descending ? 'desc' : 'asc');
        $summary = $this->summary($request);

        if ($rowsPerPage <= 0) {
            return response()->json([
                'data' => $query->get(),
                'summary' => $summary,
            ]);
        }

        $paginated = $query->paginate($rowsPerPage);
        $response = $paginated->toArray();
        $response['summary'] = $summary;

        return response()->json($response);
    }

    public function show($id)
    {
        return AlmacenItem::with('subpartida.partida.grupo')->findOrFail($id);
    }

    public function reportPdf(Request $request)
    {
        return $this->buildReportPdf($request);
    }

    private function buildReportPdf(Request $request)
    {
        $existente = $request->boolean('existente');
        @set_time_limit(240);
        @ini_set('memory_limit', '768M');

        $query = $this->reportQuery($request);


        if ($existente) {
            $query->havingRaw('cantidad > 0');
        }

        $items = $query
            ->orderBy('almacen_items.nombre')
            ->get();

        $summary = $this->summary($request);
        $filters = $this->filterLabels($request);
        $title = $existente ? 'Inventario existente' : 'Inventario completo';

        $pdf = Pdf::loadView('reportes.almacen_inventario', [
            'items' => $items,
            'summary' => $summary,
            'filters' => $filters,
            'title' => $title,
            'existente' => $existente,
        ])->setPaper('letter', 'landscape');

        return $pdf->stream(($existente ? 'inventario_existente' : 'inventario_completo').'_'.now()->format('Ymd_His').'.pdf');
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $item = AlmacenItem::create($data);

        return response()->json($item->load('subpartida.partida.grupo'), 201);
    }

    public function update(Request $request, $id)
    {
        $item = AlmacenItem::findOrFail($id);
        $data = $request->validate($this->rules(true));
        $item->update($data);

        return response()->json($item->load('subpartida.partida.grupo'));
    }

    public function destroy($id)
    {
        $item = AlmacenItem::findOrFail($id);
        $item->delete();

        return response()->json(['message' => 'Item eliminado correctamente']);
    }

    private function rules(bool $updating = false): array
    {
        $required = $updating ? 'sometimes|required' : 'required';

        return [
            'subpartida_id' => "{$required}|exists:subpartidas,id",
            'nombre' => "{$required}|string|max:255",
            'unidad_medida' => 'nullable|string|max:100',
            'precio_unitario' => 'nullable|numeric|min:0',
        ];
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('grupo_id')) {
            $query->whereHas('subpartida.partida', function ($query) use ($request) {
                $query->where('grupo_id', $request->grupo_id);
            });
        }

        if ($request->filled('partida_id')) {
            $query->whereHas('subpartida', function ($query) use ($request) {
                $query->where('partida_id', $request->partida_id);
            });
        }

        if ($request->filled('subpartida_id')) {
            $query->where('subpartida_id', $request->subpartida_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%")
                    ->orWhere('unidad_medida', 'like', "%{$q}%")
                    ->orWhereHas('subpartida', function ($query) use ($q) {
                        $query->where('codigo', 'like', "%{$q}%")
                            ->orWhere('nombre', 'like', "%{$q}%");
                    })
                    ->orWhereHas('subpartida.partida', function ($query) use ($q) {
                        $query->where('codigo', 'like', "%{$q}%")
                            ->orWhere('nombre', 'like', "%{$q}%");
                    })
                    ->orWhereHas('subpartida.partida.grupo', function ($query) use ($q) {
                        $query->where('codigo', 'like', "%{$q}%")
                            ->orWhere('nombre', 'like', "%{$q}%");
                    });
            });
        }
    }

    private function cantidadSubquery()
    {
        return DB::table('compra_detalles')
            ->selectRaw('COALESCE(SUM(COALESCE(cantidad, 0) - COALESCE(cantidad_venta, 0)), 0)')
            ->whereColumn('compra_detalles.producto_id', 'almacen_items.id')
            ->whereNull('compra_detalles.deleted_at')
            ->whereRaw("UPPER(COALESCE(compra_detalles.estado, '')) = 'ACTIVO'");
    }

    private function summary(Request $request): array
    {
        $query = DB::table('almacen_items')
            ->leftJoin('compra_detalles', function ($join) {
                $join->on('compra_detalles.producto_id', '=', 'almacen_items.id')
                    ->whereNull('compra_detalles.deleted_at')
                    ->whereRaw("UPPER(COALESCE(compra_detalles.estado, '')) = 'ACTIVO'");
            })
            ->join('subpartidas', 'subpartidas.id', '=', 'almacen_items.subpartida_id')
            ->join('partidas', 'partidas.id', '=', 'subpartidas.partida_id')
            ->join('grupos', 'grupos.id', '=', 'partidas.grupo_id')
            ->whereNull('almacen_items.deleted_at');

        if ($request->filled('grupo_id')) {
            $query->where('partidas.grupo_id', $request->grupo_id);
        }

        if ($request->filled('partida_id')) {
            $query->where('subpartidas.partida_id', $request->partida_id);
        }

        if ($request->filled('subpartida_id')) {
            $query->where('almacen_items.subpartida_id', $request->subpartida_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('almacen_items.nombre', 'like', "%{$q}%")
                    ->orWhere('almacen_items.unidad_medida', 'like', "%{$q}%")
                    ->orWhere('subpartidas.codigo', 'like', "%{$q}%")
                    ->orWhere('subpartidas.nombre', 'like', "%{$q}%")
                    ->orWhere('partidas.codigo', 'like', "%{$q}%")
                    ->orWhere('partidas.nombre', 'like', "%{$q}%")
                    ->orWhere('grupos.codigo', 'like', "%{$q}%")
                    ->orWhere('grupos.nombre', 'like', "%{$q}%");
            });
        }

        if ($request->boolean('existente')) {
            $query->havingRaw('SUM(COALESCE(compra_detalles.cantidad, 0) - COALESCE(compra_detalles.cantidad_venta, 0)) > 0');
        }

        $row = $query
            ->selectRaw('COUNT(DISTINCT almacen_items.id) as items')
            ->selectRaw('COALESCE(SUM(COALESCE(compra_detalles.cantidad, 0) - COALESCE(compra_detalles.cantidad_venta, 0)), 0) as cantidad')
            ->first();

        return [
            'items' => (int) ($row->items ?? 0),
            'cantidad' => (float) ($row->cantidad ?? 0),
        ];
    }

    private function reportQuery(Request $request)
    {
        $cantidad = "COALESCE(SUM(COALESCE(compra_detalles.cantidad, 0) - COALESCE(compra_detalles.cantidad_venta, 0)), 0)";

        $query = DB::table('almacen_items')
            ->leftJoin('compra_detalles', function ($join) {
                $join->on('compra_detalles.producto_id', '=', 'almacen_items.id')
                    ->whereNull('compra_detalles.deleted_at')
                    ->whereRaw("UPPER(COALESCE(compra_detalles.estado, '')) = 'ACTIVO'");
            })
            ->join('subpartidas', 'subpartidas.id', '=', 'almacen_items.subpartida_id')
            ->join('partidas', 'partidas.id', '=', 'subpartidas.partida_id')
            ->join('grupos', 'grupos.id', '=', 'partidas.grupo_id')
            ->whereNull('almacen_items.deleted_at');

        if ($request->filled('grupo_id')) {
            $query->where('partidas.grupo_id', $request->grupo_id);
        }

        if ($request->filled('partida_id')) {
            $query->where('subpartidas.partida_id', $request->partida_id);
        }

        if ($request->filled('subpartida_id')) {
            $query->where('almacen_items.subpartida_id', $request->subpartida_id);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('almacen_items.nombre', 'like', "%{$q}%")
                    ->orWhere('almacen_items.unidad_medida', 'like', "%{$q}%")
                    ->orWhere('subpartidas.codigo', 'like', "%{$q}%")
                    ->orWhere('subpartidas.nombre', 'like', "%{$q}%")
                    ->orWhere('partidas.codigo', 'like', "%{$q}%")
                    ->orWhere('partidas.nombre', 'like', "%{$q}%")
                    ->orWhere('grupos.codigo', 'like', "%{$q}%")
                    ->orWhere('grupos.nombre', 'like', "%{$q}%");
            });
        }

        return $query
            ->select([
                'almacen_items.id',
                'almacen_items.nombre',
                'almacen_items.unidad_medida',
                'almacen_items.precio_unitario',
                'subpartidas.codigo as subpartida_codigo',
                'subpartidas.nombre as subpartida_nombre',
                'partidas.codigo as partida_codigo',
                'partidas.nombre as partida_nombre',
                'grupos.codigo as grupo_codigo',
                'grupos.nombre as grupo_nombre',
            ])
            ->selectRaw("{$cantidad} as cantidad")
            ->groupBy(
                'almacen_items.id',
                'almacen_items.nombre',
                'almacen_items.unidad_medida',
                'almacen_items.precio_unitario',
                'subpartidas.codigo',
                'subpartidas.nombre',
                'partidas.codigo',
                'partidas.nombre',
                'grupos.codigo',
                'grupos.nombre',
            );
    }

    private function filterLabels(Request $request): array
    {
        $filters = [
            'grupo' => 'Todos',
            'partida' => 'Todas',
            'subpartida' => 'Todas',
            'busqueda' => $request->input('q') ?: 'Sin busqueda',
        ];

        if ($request->filled('grupo_id')) {
            $row = DB::table('grupos')->where('id', $request->grupo_id)->first();
            if ($row) {
                $filters['grupo'] = "{$row->codigo} - {$row->nombre}";
            }
        }

        if ($request->filled('partida_id')) {
            $row = DB::table('partidas')->where('id', $request->partida_id)->first();
            if ($row) {
                $filters['partida'] = "{$row->codigo} - {$row->nombre}";
            }
        }

        if ($request->filled('subpartida_id')) {
            $row = DB::table('subpartidas')->where('id', $request->subpartida_id)->first();
            if ($row) {
                $filters['subpartida'] = "{$row->codigo} - {$row->nombre}";
            }
        }

        return $filters;
    }
}
