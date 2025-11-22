<?php

namespace App\Http\Controllers;

use App\Models\Area;
use Illuminate\Http\Request;

class AreaController extends Controller
{
    public function index()
    {
        // si quieres incluir servicios: ->with('servicios')
        return Area::orderBy('id', 'asc')->get();
    }

    public function show($id)
    {
        return Area::with('servicios')->findOrFail($id);
    }

    public function store(Request $request)
    {
        // sin validaciones fuertes, toma todo el request
        $area = Area::create($request->all());
        return response()->json($area, 201);
    }

    public function update(Request $request, $id)
    {
        $area = Area::findOrFail($id);
        $area->update($request->all());
        return response()->json($area);
    }

    public function destroy($id)
    {
        $area = Area::findOrFail($id);
        $area->delete();

        return response()->json(['message' => 'Área eliminada correctamente']);
    }
}
