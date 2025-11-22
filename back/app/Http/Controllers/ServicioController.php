<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    public function index(Request $request)
    {
        $query = Servicio::with('area')->orderBy('codigo', 'asc');

        if ($request->filled('area_id')) {
            $query->where('area_id', $request->area_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        return $query->get();
    }

    public function show($id)
    {
        return Servicio::with('area')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $servicio = Servicio::create($request->all());
        return response()->json($servicio, 201);
    }

    public function update(Request $request, $id)
    {
        $servicio = Servicio::findOrFail($id);
        $servicio->update($request->all());

        return response()->json($servicio);
    }

    public function destroy($id)
    {
        $servicio = Servicio::findOrFail($id);
        $servicio->delete();

        return response()->json(['message' => 'Servicio eliminado correctamente']);
    }
}
