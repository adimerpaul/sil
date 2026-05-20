<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\Solicitude;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    public function buscarPorNN_RN(Request $request)
    {
        return $this->generarNombrePorTipo($request->get('tipo'));
    }

    public function buscarPorTipoNN_RN(string $tipo)
    {
        return $this->generarNombrePorTipo($tipo);
    }

    private function generarNombrePorTipo(?string $tipo)
    {
        if (!in_array($tipo, ['NN', 'RN'])) {
            return response()->json(['error' => 'Tipo invÃ¡lido'], 422);
        }

        $count = Paciente::where('nombre_completo', 'LIKE', $tipo . '-%')
            ->count();

        return $tipo . '-' . ($count + 1);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 20);
        $search  = $request->get('search');

        $query = Paciente::orderBy('id', 'desc');

        if ($search) {
            $search = trim($search);
            $query->where(function ($q) use ($search) {
                $q->where('nombre_completo', 'like', "%{$search}%")
                    ->orWhere('ci', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        $pacientes = $query->paginate($perPage);

        return response()->json($pacientes);
    }

    public function show($id)
    {
        return Paciente::findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
        ]);

        $existe = Paciente::where('ci', $request->ci)->first();
        if ($existe && !empty($request->ci)) {
            return response()->json(['message' => 'El paciente con CI ' . $request->ci . ' ya existe'], 409);
        }

        $datos = $request->all();
        $datos['codigo'] = Paciente::generarCodigo($datos['nombre_completo'] ?? null, $datos['fecha_nac'] ?? null);
        $paciente = Paciente::create($datos);
        return response()->json($paciente, 201);
    }

    public function update(Request $request, $id)
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->update($request->all());
        return response()->json($paciente);
    }

    public function destroy($id)
    {
        $paciente = Paciente::findOrFail($id);
        $paciente->delete();
        return response()->json(['message' => 'Paciente eliminado correctamente']);
    }

    public function buscarPorCi($ci)
    {
        $paciente = Paciente::where('ci', $ci)->first();

        if (!$paciente) {
            return response()->json(['message' => 'Paciente no encontrado'], 404);
        }

        return response()->json($paciente);
    }
}
