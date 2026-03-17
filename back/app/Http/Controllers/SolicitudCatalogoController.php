<?php

namespace App\Http\Controllers;

use App\Models\Area;
use App\Models\Diagnostico;
use App\Models\Doctor;
use App\Models\Establecimiento;
use Illuminate\Http\Request;

class SolicitudCatalogoController extends Controller
{
    public function create(Request $request)
    {
        return response()->json([
            'doctores' => Doctor::with('establecimiento')
                ->orderBy('id', 'desc')
                ->get(),
            'diagnosticos' => Diagnostico::all(),
            'establecimientos' => Establecimiento::with('servicios')
                ->orderBy('id', 'desc')
                ->get()
                ->each(function ($establecimiento) {
                    $establecimiento->servicio_ids = $establecimiento->servicios->pluck('id');
                }),
            'areas' => $this->areasParaCrearSolicitud($request),
        ]);
    }

    private function areasParaCrearSolicitud(Request $request)
    {
        $user = $request->user();
        $query = Area::with('servicios')->orderBy('id', 'asc');

        if ($user->role === 'Administrador') {
            return $query->get();
        }

        $area = $user->area;
        $idBiologiaMolecular = 7;

        if ($area && $area->id == $idBiologiaMolecular) {
            return $query->where('id', $idBiologiaMolecular)->get();
        }

        return $query->where('id', '<>', $idBiologiaMolecular)->get();
    }
}
