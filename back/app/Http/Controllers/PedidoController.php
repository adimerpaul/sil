<?php

namespace App\Http\Controllers;

use App\Models\AlmacenItem;
use App\Models\Pedido;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class PedidoController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function index(Request $request)
    {
        $this->authorize('Ver Pedidos');
        $query = Pedido::with(['user:id,name'])
            ->withCount('detalles');

        $isAdmin = auth()->user()->hasAnyRole(['admin', 'jefe-almacen']);
        if (!$isAdmin) {
            $query->deUsuario();
        }

        $this->applyFilters($query, $request);

        $rowsPerPage = (int) $request->input('rowsPerPage', 15);
        $rowsPerPage = $rowsPerPage > 0 ? $rowsPerPage : 15;

        $summaryQuery = Pedido::query();
        if (!$isAdmin) {
            $summaryQuery->deUsuario();
        }
        $this->applyFilters($summaryQuery, $request);
        $summary = [
            'total_pendientes' => (float) (clone $summaryQuery)->where('estado', 'PENDIENTE')->sum('total'),
            'total_aceptados' => (float) (clone $summaryQuery)->where('estado', 'ACEPTADO')->sum('total'),
            'total_rechazados' => (float) (clone $summaryQuery)->where('estado', 'RECHAZADO')->sum('total'),
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
        $this->authorize('Ver Pedidos');
        
        $pedido = Pedido::with(['user:id,name', 'detalles.producto'])->findOrFail($id);

        $isAdmin = auth()->user()->hasAnyRole(['admin', 'jefe-almacen']);
        if (!$isAdmin && $pedido->user_id !== auth()->id()) {
            abort(403, 'No autorizado para ver este pedido');
        }

        return $pedido;
    }

    public function store(Request $request)
    {
        $this->authorize('Crear Pedidos');
        
        $data = $request->validate([
            'nombre_usuario' => 'required|string|max:255',
            'items' => 'required|array|min:1',
            'items.*.producto_id' => 'required|exists:almacen_items,id',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'nullable|numeric|min:0',
        ]);

        return DB::transaction(function () use ($data, $request) {
            $productos = AlmacenItem::whereIn('id', collect($data['items'])->pluck('producto_id'))->get()->keyBy('id');
            $total = collect($data['items'])->sum(function ($item) use ($productos) {
                $precio = (float) ($item['precio_unitario'] ?? $productos[$item['producto_id']]->precio_unitario ?? 0);
                return $precio * (int) $item['cantidad'];
            });

            $pedido = Pedido::create([
                'user_id' => $request->user()->id,
                'fecha_hora' => now(),
                'nombre_usuario' => $data['nombre_usuario'],
                'estado' => 'PENDIENTE',
                'total' => $total,
                'modificado' => false,
            ]);

            foreach ($data['items'] as $item) {
                $producto = $productos[$item['producto_id']];
                $precio = (float) ($item['precio_unitario'] ?? $producto->precio_unitario ?? 0);
                $cantidad = (int) $item['cantidad'];
                $subtotal = round($precio * $cantidad, 2);

                $pedido->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $cantidad,
                    'precio_unitario' => $precio,
                    'subtotal' => $subtotal,
                ]);
            }

            return response()->json($pedido->load(['user:id,name', 'detalles.producto']), 201);
        });
    }

    public function update(Request $request, $id)
    {
        $this->authorize('Editar Pedidos');
        
        $pedido = Pedido::findOrFail($id);

        $isAdmin = auth()->user()->hasAnyRole(['admin', 'jefe-almacen']);
        if (!$isAdmin) {
            abort(403, 'No autorizado para actualizar este pedido');
        }

        $data = $request->validate([
            'estado' => ['required', Rule::in(['PENDIENTE', 'ACEPTADO', 'RECHAZADO'])],
        ]);

        $pedido->update([
            'estado' => $data['estado'],
            'modificado' => true,
        ]);

        return response()->json($pedido->load(['user:id,name', 'detalles.producto']));
    }

    public function destroy($id)
    {
        $this->authorize('Anular Pedidos');
        
        $pedido = Pedido::with('detalles')->findOrFail($id);

        $isAdmin = auth()->user()->hasAnyRole(['admin', 'jefe-almacen']);
        if (!$isAdmin && $pedido->user_id !== auth()->id()) {
            abort(403, 'No autorizado para anular este pedido');
        }

        $pedido->delete();

        return response()->json(['message' => 'Pedido anulado correctamente']);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('date_from')) {
            $query->whereDate('fecha_hora', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('fecha_hora', '<=', $request->date_to);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($query) use ($q) {
                $query->where('nombre_usuario', 'like', "%{$q}%")
                    ->orWhereHas('user', fn ($query) => $query->where('name', 'like', "%{$q}%"));
            });
        }
    }
}
