<?php

namespace App\Http\Controllers;

use App\Models\HematologiaOpcion;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HematologiaOpcionController extends Controller
{
    /**
     * Lista las opciones de método y equipo.
     * Con ?solo_activos=1 devuelve únicamente las habilitadas (para la captura).
     */
    public function index(Request $request)
    {
        $query = HematologiaOpcion::orderBy('seccion')
            ->orderBy('tipo')
            ->orderBy('orden')
            ->orderBy('nombre');

        if ($request->boolean('solo_activos')) {
            $query->where('activo', true);
        }

        return response()->json([
            'opciones' => $query->get(),
            'secciones' => HematologiaOpcion::SECCIONES,
            'tipos' => HematologiaOpcion::TIPOS,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate($this->rules());

        $opcion = HematologiaOpcion::create([
            'seccion' => $data['seccion'],
            'tipo' => $data['tipo'],
            'nombre' => trim($data['nombre']),
            'activo' => $data['activo'] ?? true,
            'orden' => ((int) HematologiaOpcion::where('seccion', $data['seccion'])
                ->where('tipo', $data['tipo'])
                ->max('orden')) + 10,
        ]);

        return response()->json($opcion, 201);
    }

    public function update(Request $request, $id)
    {
        $opcion = HematologiaOpcion::findOrFail($id);

        $data = $request->validate($this->rules($opcion));

        if (isset($data['nombre'])) {
            $data['nombre'] = trim($data['nombre']);
        }

        $opcion->fill(collect($data)->only(['nombre', 'activo', 'orden'])->all());
        $opcion->save();

        return response()->json($opcion);
    }

    public function destroy($id)
    {
        $opcion = HematologiaOpcion::findOrFail($id);
        $opcion->delete();

        return response()->json(['message' => 'Opción eliminada']);
    }

    private function rules(?HematologiaOpcion $opcion = null): array
    {
        $seccion = $opcion?->seccion ?? request('seccion');
        $tipo = $opcion?->tipo ?? request('tipo');

        $unico = Rule::unique('hematologia_opciones', 'nombre')
            ->where(fn ($q) => $q->where('seccion', $seccion)->where('tipo', $tipo));

        if ($opcion) {
            $unico->ignore($opcion->id);
        }

        return [
            'seccion' => [$opcion ? 'sometimes' : 'required', Rule::in(HematologiaOpcion::SECCIONES)],
            'tipo' => [$opcion ? 'sometimes' : 'required', Rule::in(HematologiaOpcion::TIPOS)],
            'nombre' => [$opcion ? 'sometimes' : 'required', 'string', 'max:255', $unico],
            'activo' => 'sometimes|boolean',
            'orden' => 'sometimes|integer',
        ];
    }
}
