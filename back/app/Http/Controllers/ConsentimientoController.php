<?php
// app/Http/Controllers/ConsentimientoController.php

namespace App\Http\Controllers;

use App\Models\Consentimiento;
use App\Models\Paciente;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ConsentimientoController extends Controller
{
    public function index(Request $request)
    {
        $query = Consentimiento::with('paciente');

        // filtros opcionales por fecha y tipo
        if ($request->filled('from')) {
            $query->whereDate('fecha_consentimiento', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('fecha_consentimiento', '<=', $request->to);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);  // ACEPTA / RECHAZA
        }

        return $query->orderBy('id', 'desc')->get();
    }

    public function show($id)
    {
        return Consentimiento::with('paciente')->findOrFail($id);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tipo'               => 'nullable|in:ACEPTA,RECHAZA',
            'paciente_id'        => 'nullable|exists:pacientes,id',
            'nombre_completo'    => 'required_without:paciente_id|string|max:255',
            'ci'                 => 'nullable|string|max:50',
            'fecha_consentimiento' => 'nullable|date',
        ]);

        // Si viene paciente_id y no se enviaron los campos, los completamos con los datos del paciente
//        if ($request->filled('paciente_id')) {
//            $paciente = Paciente::find($request->paciente_id);
//            if ($paciente && !$request->filled('nombre_completo')) {
//                $request->merge([
//                    'nombre_completo' => $paciente->nombre_completo,
//                    'fecha_nac'       => $paciente->fecha_nac,
//                    'genero'          => $paciente->genero,
//                    'edad'            => $paciente->edad,
//                    'ci'              => $paciente->ci,
//                    'telefono'        => $paciente->telefono,
//                    'direccion'       => $paciente->direccion,
//                    'discapacidad'    => $paciente->discapacidad,
//                    'discapacidad_cual' => $paciente->discapacidad_cual,
//                    'embarazo'        => $paciente->embarazo,
//                    'fum'             => $paciente->fum,
//                    'sem_gest'        => $paciente->sem_gest,
//                ]);
//            }
//        }

        // usuario que registra
        if ($request->user()) {
            $request->merge(['user_id' => $request->user()->id]);
        }

        $consentimiento = Consentimiento::create($request->all());

        return response()->json($consentimiento->load('paciente'), 201);
    }

    public function update(Request $request, $id)
    {
        $consentimiento = Consentimiento::findOrFail($id);

        $request->validate([
            'tipo' => 'nullable',
            'paciente_id' => 'nullable|exists:pacientes,id',
        ]);

        $consentimiento->update($request->all());

        return response()->json($consentimiento->load('paciente'));
    }

    public function destroy($id)
    {
        $consentimiento = Consentimiento::findOrFail($id);
        $consentimiento->delete();

        return response()->json(['message' => 'Consentimiento eliminado correctamente']);
    }

    /**
     * Imprimir con DomPDF
     */
    public function print($id)
    {
        $consentimiento = Consentimiento::with('paciente')->findOrFail($id);
//        return $consentimiento->load('paciente');

        $pdf = Pdf::loadView('pdf.consentimiento', [
            'c' => $consentimiento,
        ])->setPaper('A4', 'portrait');

        return $pdf->stream('consentimiento-'.$consentimiento->id.'.pdf');
    }
}
