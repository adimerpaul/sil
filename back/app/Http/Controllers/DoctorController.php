<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    /**
     * Listar todos los doctores.
     */
    public function index()
    {
        // Si quieres, luego puedes agregar filtro por búsqueda
        return Doctor::orderBy('id', 'desc')->get();
    }

    /**
     * Mostrar un doctor específico.
     */
    public function show($id)
    {
        return Doctor::findOrFail($id);
    }

    /**
     * Crear un nuevo doctor.
     * SIN validaciones, toma todo el request.
     */
    public function store(Request $request)
    {
        $doctor = Doctor::create($request->all());

        return response()->json($doctor, 201);
    }

    /**
     * Actualizar un doctor.
     */
    public function update(Request $request, $id)
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->update($request->all());

        return response()->json($doctor);
    }

    /**
     * Eliminar (soft delete) un doctor.
     */
    public function destroy($id)
    {
        $doctor = Doctor::findOrFail($id);
        $doctor->delete();

        return response()->json(['message' => 'Doctor eliminado correctamente']);
    }
}
