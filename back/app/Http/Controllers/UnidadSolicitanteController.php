<?php

namespace App\Http\Controllers;

use App\Models\UnidadSolicitante;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UnidadSolicitanteController extends Controller
{
    public function index(Request $request)
    {
        $query = UnidadSolicitante::query();

        if ($request->filled('q')) {
            $q = trim((string) $request->q);
            $query->where('nombre', 'like', "%{$q}%");
        }

        return $query->orderBy('nombre')->get();
    }

    public function show($id)
    {
        return UnidadSolicitante::findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nombre' => ['required', 'string', 'max:255', 'unique:unidad_solicitantes,nombre'],
        ]);

        $data['nombre'] = trim($data['nombre']);

        $unidadSolicitante = UnidadSolicitante::create($data);

        return response()->json($unidadSolicitante, 201);
    }

    public function update(Request $request, $id)
    {
        $unidadSolicitante = UnidadSolicitante::findOrFail($id);

        $data = $request->validate([
            'nombre' => [
                'required',
                'string',
                'max:255',
                Rule::unique('unidad_solicitantes', 'nombre')->ignore($unidadSolicitante->id),
            ],
        ]);

        $data['nombre'] = trim($data['nombre']);

        $unidadSolicitante->update($data);

        // Mantener el snapshot de texto en solicitudes para no romper reportes existentes.
        $unidadSolicitante->solicitudes()->update(['sala' => $unidadSolicitante->nombre]);

        return response()->json($unidadSolicitante);
    }

    public function destroy($id)
    {
        $unidadSolicitante = UnidadSolicitante::findOrFail($id);
        $unidadSolicitante->solicitudes()->update([
            'unidad_solicitante_id' => null,
        ]);

        $unidadSolicitante->delete();

        return response()->json(['message' => 'Unidad solicitante eliminada correctamente']);
    }
}
