<?php

namespace App\Http\Controllers;

use App\Models\Solicitude;
use App\Models\Paciente;
use App\Models\Doctor;
use Illuminate\Http\Request;

class SolicitudeController extends Controller
{
    public function index(Request $request)
    {
        $query = Solicitude::with(['paciente', 'doctor']);

        // filtros por fecha de solicitud
        if ($request->filled('from')) {
            $query->whereDate('fecha_solicitud', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('fecha_solicitud', '<=', $request->to);
        }

        // filtro por estado
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        // filtro por tipo atención
        if ($request->filled('tipo_atencion')) {
            $query->where('tipo_atencion', $request->tipo_atencion);
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function show($id)
    {
        return Solicitude::with(['paciente', 'doctor'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        // usuario que crea
        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        // Copia de datos del paciente (si se seleccionó)
        if ($request->filled('paciente_id')) {
            $p = Paciente::find($request->paciente_id);
            if ($p) {
                $data['paciente_nombre']     = $data['paciente_nombre']     ?? $p->nombre_completo;
                $data['paciente_ci']         = $data['paciente_ci']         ?? $p->ci;
                $data['paciente_telefono']   = $data['paciente_telefono']   ?? $p->telefono;
                $data['paciente_direccion']  = $data['paciente_direccion']  ?? $p->direccion;
                $data['paciente_fecha_nac']  = $data['paciente_fecha_nac']  ?? $p->fecha_nac;
                $data['paciente_genero']     = $data['paciente_genero']     ?? $p->genero;
                $data['paciente_edad']       = $data['paciente_edad']       ?? $p->edad;
            }
        }

        // Copia de datos del doctor (si se seleccionó)
        if ($request->filled('doctor_id')) {
            $d = Doctor::find($request->doctor_id);
            if ($d) {
                $data['doctor_nombre']       = $data['doctor_nombre']       ?? $d->nombre;
                $data['doctor_especialidad'] = $data['doctor_especialidad'] ?? $d->especialidad;
                $data['doctor_ci']           = $data['doctor_ci']           ?? $d->ci;
                $data['doctor_telefono']     = $data['doctor_telefono']     ?? $d->telefono;
                $data['doctor_email']        = $data['doctor_email']        ?? $d->email;
                $data['doctor_registro']     = $data['doctor_registro']     ?? $d->registro;
            }
        }

        $solicitud = Solicitude::create($data);

        return response()->json($solicitud->load(['paciente', 'doctor']), 201);
    }

    public function update(Request $request, $id)
    {
        $solicitud = Solicitude::findOrFail($id);
        $data = $request->all();

        // Si cambian paciente/doctor, volvemos a copiar
        if ($request->filled('paciente_id')) {
            $p = Paciente::find($request->paciente_id);
            if ($p) {
                $data['paciente_nombre']     = $data['paciente_nombre']     ?? $p->nombre_completo;
                $data['paciente_ci']         = $data['paciente_ci']         ?? $p->ci;
                $data['paciente_telefono']   = $data['paciente_telefono']   ?? $p->telefono;
                $data['paciente_direccion']  = $data['paciente_direccion']  ?? $p->direccion;
                $data['paciente_fecha_nac']  = $data['paciente_fecha_nac']  ?? $p->fecha_nac;
                $data['paciente_genero']     = $data['paciente_genero']     ?? $p->genero;
                $data['paciente_edad']       = $data['paciente_edad']       ?? $p->edad;
            }
        }

        if ($request->filled('doctor_id')) {
            $d = Doctor::find($request->doctor_id);
            if ($d) {
                $data['doctor_nombre']       = $data['doctor_nombre']       ?? $d->nombre;
                $data['doctor_especialidad'] = $data['doctor_especialidad'] ?? $d->especialidad;
                $data['doctor_ci']           = $data['doctor_ci']           ?? $d->ci;
                $data['doctor_telefono']     = $data['doctor_telefono']     ?? $d->telefono;
                $data['doctor_email']        = $data['doctor_email']        ?? $d->email;
                $data['doctor_registro']     = $data['doctor_registro']     ?? $d->registro;
            }
        }

        $solicitud->update($data);

        return response()->json($solicitud->load(['paciente', 'doctor']));
    }

    public function destroy($id)
    {
        $solicitud = Solicitude::findOrFail($id);
        $solicitud->delete();

        return response()->json(['message' => 'Solicitud eliminada correctamente']);
    }
}
