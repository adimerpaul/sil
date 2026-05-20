<?php

namespace App\Http\Controllers;

use App\Exports\EstablecimientosExport;
use App\Models\Establecimiento;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class EstablecimientoController extends Controller
{
    public function index(Request $request)
    {
        $query = Establecimiento::with('servicios');

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($qq) use ($q) {
                $qq->where('nombre', 'like', "%{$q}%")
                    ->orWhere('direccion', 'like', "%{$q}%")
                    ->orWhere('responsable_laboratorio', 'like', "%{$q}%");
            });
        }

        $items = $query->orderBy('nombre', 'asc')->get();

        $items->each(function ($e) {
            $e->servicio_ids = $e->servicios->pluck('id');
        });

        return $items;
    }

    public function show($id)
    {
        $establecimiento = Establecimiento::with('servicios')->findOrFail($id);
        $establecimiento->servicio_ids = $establecimiento->servicios->pluck('id');

        return $establecimiento;
    }

    public function store(Request $request)
    {
        $establecimiento = Establecimiento::create($request->except('servicio_ids'));

        $servicios = $request->input('servicio_ids', []);
        $establecimiento->servicios()->sync($servicios);

        $establecimiento->load('servicios');
        $establecimiento->servicio_ids = $establecimiento->servicios->pluck('id');

        return response()->json($establecimiento, 201);
    }

    public function update(Request $request, $id)
    {
        $establecimiento = Establecimiento::findOrFail($id);
        $establecimiento->update($request->except('servicio_ids'));

        $servicios = $request->input('servicio_ids', []);
        $establecimiento->servicios()->sync($servicios);

        $establecimiento->load('servicios');
        $establecimiento->servicio_ids = $establecimiento->servicios->pluck('id');

        return response()->json($establecimiento);
    }

    public function destroy($id)
    {
        $establecimiento = Establecimiento::findOrFail($id);
        $establecimiento->delete();

        return response()->json(['message' => 'Establecimiento eliminado correctamente']);
    }

    public function exportarExcel(Request $request)
    {
        $filters = $request->only(['tipo', 'estado', 'q']);
        $fecha   = now()->format('Y-m-d');

        return Excel::download(new EstablecimientosExport($filters), "establecimientos_{$fecha}.xlsx");
    }

    public function exportarPdf(Request $request)
    {
        $filters = $request->only(['tipo', 'estado', 'q']);

        $query = Establecimiento::orderBy('nombre', 'asc');
        if (!empty($filters['tipo'])) {
            $query->where('tipo', $filters['tipo']);
        }
        if (!empty($filters['estado'])) {
            $query->where('estado', $filters['estado']);
        }
        if (!empty($filters['q'])) {
            $q = $filters['q'];
            $query->where(function ($qq) use ($q) {
                $qq->where('nombre', 'like', "%{$q}%")
                    ->orWhere('direccion', 'like', "%{$q}%")
                    ->orWhere('responsable_laboratorio', 'like', "%{$q}%");
            });
        }
        $establecimientos = $query->get();

        $pdf = Pdf::loadView('pdf.establecimientos', [
            'establecimientos' => $establecimientos,
            'filtros'          => $filters,
        ])->setPaper('a4', 'landscape');

        $fecha = now()->format('Y-m-d');

        return $pdf->download("establecimientos_{$fecha}.pdf");
    }
}
