<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    public function index() {
        return Paciente::orderBy('id', 'desc')->get();
    }

    public function show($id) {
        return Paciente::findOrFail($id);
    }

    public function store(Request $request) {
        $validated = $request->validate([
            'nombre_completo' => 'required|string|max:255',
        ]);
        $paciente = Paciente::create($request->all());
        return response()->json($paciente, 201);
    }

    public function update(Request $request, $id) {
        $paciente = Paciente::findOrFail($id);
        $paciente->update($request->all());
        return response()->json($paciente);
    }

    public function destroy($id) {
        $paciente = Paciente::findOrFail($id);
        $paciente->delete();
        return response()->json(['message' => 'Paciente eliminado correctamente']);
    }
}
