<?php

namespace App\Http\Controllers;

use App\Models\Establecimiento;
use Illuminate\Http\Request;

class EstablecimientoController extends Controller
{
    /**
     * Listar establecimientos (con filtros opcionales).
     */
    public function index(Request $request)
    {
        $query = Establecimiento::with('servicios'); // 👈 cargamos servicios

        // Filtro por tipo: PUBLICO / PRIVADO
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        // Filtro por estado: ACTIVO / INACTIVO
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // Búsqueda rápida por nombre / dirección / responsable
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qq) use ($q) {
                $qq->where('nombre', 'like', "%{$q}%")
                    ->orWhere('direccion', 'like', "%{$q}%")
                    ->orWhere('responsable_laboratorio', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('id', 'desc')->get();

        // Añadimos un campo servicio_ids para el front
        $items->each(function ($e) {
            $e->servicio_ids = $e->servicios->pluck('id');
        });

        return $items;
    }

    /**
     * Mostrar un establecimiento.
     */
    public function show($id)
    {
        $establecimiento = Establecimiento::with('servicios')->findOrFail($id);
        $establecimiento->servicio_ids = $establecimiento->servicios->pluck('id');

        return $establecimiento;
    }

    /**
     * Crear un nuevo establecimiento.
     */
    public function store(Request $request)
    {
        // si quieres validación, la agregas aquí
        $establecimiento = Establecimiento::create(
            $request->except('servicio_ids')
        );

        // sincronizar servicios
        $servicios = $request->input('servicio_ids', []);
        $establecimiento->servicios()->sync($servicios);

        $establecimiento->load('servicios');
        $establecimiento->servicio_ids = $establecimiento->servicios->pluck('id');

        return response()->json($establecimiento, 201);
    }

    /**
     * Actualizar un establecimiento.
     */
    public function update(Request $request, $id)
    {
        $establecimiento = Establecimiento::findOrFail($id);
        $establecimiento->update($request->except('servicio_ids'));

        $servicios = $request->input('servicio_ids', []);
        $establecimiento->servicios()->sync($servicios);

        $establecimiento->load('servicios');
        $establecimiento->servicio_ids = $establecimiento->servicios->pluck('id');

        return response()->json($establecimiento);
    }

    /**
     * Eliminar (soft delete) un establecimiento.
     */
    public function destroy($id)
    {
        $establecimiento = Establecimiento::findOrFail($id);
        $establecimiento->delete();

        return response()->json(['message' => 'Establecimiento eliminado correctamente']);
    }
}
