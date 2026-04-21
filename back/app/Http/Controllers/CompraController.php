<?php

namespace App\Http\Controllers;

use App\Models\AlmacenItem;
use App\Models\Compra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class CompraController extends Controller
{
    public function index(Request $request)
    {
        $query = Compra::with(['proveedor:id,nombre,carnet', 'user:id,name'])
            ->withCount('detalles');

        $this->applyFilters($query, $request);

        $rowsPerPage = (int) $request->input('rowsPerPage', 15);
        $rowsPerPage = $rowsPerPage > 0 ? $rowsPerPage : 15;

        $summaryQuery = Compra::query();
        $this->applyFilters($summaryQuery, $request);
        $summary = [
            'total_compras' => (float) (clone $summaryQuery)->where('estado', 'ACTIVO')->sum('total'),
            'total_anuladas' => (float) (clone $summaryQuery)->where('estado', 'ANULADO')->sum('total'),
            'cantidad' => (int) (clone $summaryQuery)->count(),
        ];

        $paginated = $query
            ->orderBy('fecha_hora', 'desc')
            ->orderBy('id', 'desc')
            ->paginate($rowsPerPage);

        $response = $paginated->toArray();
        $response['summary'] = $summary;

        return response()->json($response);
    }

    public function show($id)
    {
        return Compra::with(['proveedor', 'user:id,name', 'detalles.producto'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'proveedor_id' => 'nullable|exists:proveedores,id',
            'fecha_hora' => 'nullable|date',
            'tipo_registro' => ['required', Rule::in(['ENTRADA', 'SALIDA'])],
            'motivo_registro' => 'required|string|max:50',
            'carnet' => 'nullable|string|max:100',
            'nombre' => 'nullable|string|max:255',
            'tipo_pago' => 'nullable|string|max:50',
            'nro_factura' => 'nullable|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:almacen_items,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio' => 'nullable|numeric|min:0',
            'items.*.factor' => 'nullable|numeric|min:0',
            'items.*.precio_venta' => 'nullable|numeric|min:0',
            'items.*.lote' => 'nullable|string|max:255',
            'items.*.fecha_vencimiento' => 'nullable|date',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $productos = AlmacenItem::whereIn('id', collect($data['items'])->pluck('producto_id'))->get()->keyBy('id');
            $total = collect($data['items'])->sum(fn ($item) => (float) ($item['precio'] ?? 0) * (int) $item['cantidad']);

            $compra = Compra::create([
                'user_id' => $request->user()->id,
                'proveedor_id' => $data['proveedor_id'] ?? null,
                'fecha_hora' => $data['fecha_hora'] ?? now(),
                'tipo_registro' => $data['tipo_registro'],
                'motivo_registro' => strtoupper($data['motivo_registro']),
                'carnet' => $data['carnet'] ?? null,
                'nombre' => $data['nombre'] ?? null,
                'estado' => 'ACTIVO',
                'total' => $total,
                'tipo_pago' => $data['tipo_pago'] ?? 'EFECTIVO',
                'nro_factura' => $data['nro_factura'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $producto = $productos[$item['producto_id']];
                $precio = (float) ($item['precio'] ?? $producto->precio_unitario ?? 0);
                $cantidad = (int) $item['cantidad'];
                $factor = (float) ($item['factor'] ?? 1.25);
                $precio13 = round($precio * $factor, 2);

                $compra->detalles()->create([
                    'user_id' => $request->user()->id,
                    'proveedor_id' => $data['proveedor_id'] ?? null,
                    'producto_id' => $producto->id,
                    'nombre' => $producto->nombre,
                    'precio' => $precio,
                    'cantidad' => $cantidad,
                    'cantidad_venta' => 0,
                    'total' => round($precio * $cantidad, 2),
                    'factor' => $factor,
                    'precio13' => $precio13,
                    'total13' => round($precio13 * $cantidad, 2),
                    'precio_venta' => $item['precio_venta'] ?? $precio13,
                    'estado' => 'ACTIVO',
                    'lote' => $item['lote'] ?? null,
                    'fecha_vencimiento' => $item['fecha_vencimiento'] ?? null,
                    'nro_factura' => $data['nro_factura'] ?? null,
                ]);
            }

            return response()->json($compra->load(['proveedor', 'user:id,name', 'detalles.producto']), 201);
        });
    }

    public function destroy($id)
    {
        $compra = Compra::with('detalles')->findOrFail($id);
        $compra->update(['estado' => 'ANULADO']);
        $compra->detalles()->update(['estado' => 'ANULADO']);

        return response()->json(['message' => 'Compra anulada correctamente']);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('date_from')) {
            $query->whereDate('fecha_hora', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('fecha_hora', '<=', $request->date_to);
        }

        if ($request->filled('tipo_registro')) {
            $query->where('tipo_registro', $request->tipo_registro);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('nombre', 'like', "%{$q}%")
                    ->orWhere('nro_factura', 'like', "%{$q}%")
                    ->orWhereHas('proveedor', fn ($query) => $query->where('nombre', 'like', "%{$q}%"));
            });
        }
    }
}
