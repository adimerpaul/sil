<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Solicitude;
use App\Models\Paciente;
use App\Models\Doctor;
use Illuminate\Http\Request;

class SolicitudeController extends Controller
{
    public function index(Request $request)
    {
        $query = Solicitude::with(['paciente', 'doctor', 'servicios']);

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
        return Solicitude::with(['paciente', 'doctor', 'servicios'])->findOrFail($id);
    }

    public function store(Request $request)
    {
        $data = $request->all();

        // usuario que crea
        if ($request->user()) {
            $data['user_id'] = $request->user()->id;
        }

        // upsert de paciente por CI
        $ci = $request->paciente_ci;
        $paciente = $this->pacienteUpsert($ci, $data);
        if ($paciente) {
            $data['paciente_id'] = $paciente->id;
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

        // Crear solicitud
        $solicitud = Solicitude::create($data);

        // Guardar servicios en tabla pivote
        $this->syncServicios($solicitud, $request->input('servicios', []));

        return response()->json($solicitud->load(['paciente', 'doctor', 'servicios']), 201);
    }

    protected function pacienteUpsert($ci, &$data)
    {
        if (empty($ci)) {
            return null;
        }

        $p = Paciente::where('ci', $ci)->first();
        if ($p) {
            // actualizar paciente existente
            $p->nombre_completo = $data['paciente_nombre'] ?? $p->nombre_completo;
            $p->telefono        = $data['paciente_telefono'] ?? $p->telefono;
            $p->direccion       = $data['paciente_direccion'] ?? $p->direccion;
            $p->fecha_nac       = $data['paciente_fecha_nac'] ?? $p->fecha_nac;
            $p->genero          = $data['paciente_genero'] ?? $p->genero;
            $p->edad            = $data['paciente_edad'] ?? $p->edad;
            $p->save();
        } else {
            // crear nuevo paciente
            $p = Paciente::create([
                'nombre_completo' => $data['paciente_nombre'],
                'ci'              => $ci,
                'telefono'        => $data['paciente_telefono'] ?? null,
                'direccion'       => $data['paciente_direccion'] ?? null,
                'fecha_nac'       => $data['paciente_fecha_nac'] ?? null,
                'genero'          => $data['paciente_genero'] ?? null,
                'edad'            => $data['paciente_edad'] ?? null,
            ]);
        }

        $data['paciente_id'] = $p->id;
        return $p;
    }

    public function update(Request $request, $id)
    {
        $solicitud = Solicitude::findOrFail($id);
        $data = $request->all();

        // si cambian datos de paciente por CI, actualizamos también
        $ci = $request->paciente_ci;
        if (!empty($ci)) {
            $paciente = $this->pacienteUpsert($ci, $data);
            if ($paciente) {
                $data['paciente_id'] = $paciente->id;
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

        $solicitud->update($data);

        // actualizar servicios de la solicitud
        $this->syncServicios($solicitud, $request->input('servicios', []));

        return response()->json($solicitud->load(['paciente', 'doctor', 'servicios']));
    }

    protected function syncServicios(Solicitude $solicitud, array $servicios)
    {
        // $servicios viene del front: [{id, nombre, precio}, ...]
        $pivotData = [];

        foreach ($servicios as $serv) {
            if (!isset($serv['id'])) {
                continue;
            }
            $pivotData[$serv['id']] = [
                'precio' => $serv['precio'] ?? null,
            ];
        }

        $solicitud->servicios()->sync($pivotData);
    }

    public function destroy($id)
    {
        $solicitud = Solicitude::findOrFail($id);
        $solicitud->delete();

        return response()->json(['message' => 'Solicitud eliminada correctamente']);
    }
}
