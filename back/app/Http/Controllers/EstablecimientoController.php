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
        $query = Establecimiento::query();

        // Filtro por tipo: PUBLICO / PRIVADO
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
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

        return $query->orderBy('id', 'desc')->get();
    }

    /**
     * Mostrar un establecimiento.
     */
    public function show($id)
    {
        return Establecimiento::findOrFail($id);
    }

    /**
     * Crear un nuevo establecimiento.
     * (sin validaciones, igual que tu ejemplo)
     */
    public function store(Request $request)
    {
        $establecimiento = Establecimiento::create($request->all());

        return response()->json($establecimiento, 201);
    }

    /**
     * Actualizar un establecimiento.
     */
    public function update(Request $request, $id)
    {
        $establecimiento = Establecimiento::findOrFail($id);
        $establecimiento->update($request->all());

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
