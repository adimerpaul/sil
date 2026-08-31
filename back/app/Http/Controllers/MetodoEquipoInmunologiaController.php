<?php

namespace App\Http\Controllers;

use App\Models\MetodoEquipoInmunologia;
use Illuminate\Http\Request;

class MetodoEquipoInmunologiaController extends Controller
{
    public function index(Request $request)
    {
        $query = MetodoEquipoInmunologia::query();

        if ($request->filled('tipo')) {
            $query->where('tipo', strtoupper($request->get('tipo')));
        }
        if ($request->filled('q')) {
            $query->where('nombre', 'like', '%' . $request->get('q') . '%');
        }

        return response()->json(
            $query->orderBy('tipo')->orderBy('nombre')->get()
        );
    }

    public function store(Request $request)
    {
        $registro = MetodoEquipoInmunologia::create($this->validar($request));

        return response()->json($registro, 201);
    }

    public function show(string $id)
    {
        return response()->json(MetodoEquipoInmunologia::findOrFail($id));
    }

    public function update(Request $request, string $id)
    {
        $registro = MetodoEquipoInmunologia::findOrFail($id);
        $registro->update($this->validar($request));

        return response()->json($registro->fresh());
    }

    public function destroy(string $id)
    {
        MetodoEquipoInmunologia::findOrFail($id)->delete();

        return response()->json(['message' => 'Registro eliminado']);
    }

    private function validar(Request $request): array
    {
        return $request->validate([
            'tipo' => 'required|in:METODO,EQUIPO',
            'nombre' => 'required|string|max:150',
        ]);
    }
}
