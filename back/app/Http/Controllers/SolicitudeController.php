<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\Solicitude;
use App\Models\Paciente;
use App\Models\Doctor;
use Illuminate\Http\Request;

class SolicitudeController extends Controller
{
    public function generarCodigo(Request $request, $id)
    {
        $solicitud = Solicitude::findOrFail($id);

        // 1) Actualizar tipo_atencion desde el front SI/NO (si viene)
        if ($request->filled('tipo_atencion')) {
            $solicitud->tipo_atencion = $request->input('tipo_atencion');
        } elseif (empty($solicitud->tipo_atencion)) {
            // si no tiene nada, por defecto SI
            $solicitud->tipo_atencion = 'SI';
        }

        // 2) Asegurar que fecha_solicitud tenga algo (para el filtro por mes/año)
        if (empty($solicitud->fecha_solicitud)) {
            $solicitud->fecha_solicitud = now()->toDateString();
        }

        // 3) Paciente y nro_registro
        $paciente = Paciente::find($solicitud->paciente_id);
        $nombreCompleto = $paciente ? $paciente->nombre_completo : 'Desconocido';
        $nro_registro   = $this->nroRegistro($nombreCompleto, $paciente->fecha_nac ?? null);

        // 4) Generar correlativo por tipo_atencion + mes
        $solicitud->codigo      = $this->generarCodigoPorTipoYMes($solicitud);
        $solicitud->nro_registro = $nro_registro;
        $solicitud->estado      = 'ATENDIENDO';
        $solicitud->fecha_pre_analitica      = now();
        $solicitud->user_preanalitica_id = $request->user() ? $request->user()->id : null;

        $solicitud->save();

        return response()->json($solicitud->fresh(['paciente', 'doctor', 'servicios']));
    }
    protected function generarCodigoPorTipoYMes(Solicitude $solicitud): int
    {
        $tipo = $solicitud->tipo_atencion ?? 'SI';

        $fechaBase = $solicitud->fecha_solicitud ?: now()->toDateString();
        $timestamp = strtotime($fechaBase);

        $anio = date('Y', $timestamp);
        $mes  = date('m', $timestamp);

        $ultimoCodigo = Solicitude::where('tipo_atencion', $tipo)
            ->whereYear('fecha_solicitud', $anio)
            ->whereMonth('fecha_solicitud', $mes)
            ->whereNotNull('codigo')
            ->max('codigo');
        error_log("Último código para tipo $tipo en $anio-$mes: " . var_export($ultimoCodigo, true));

        return $ultimoCodigo ? ((int)$ultimoCodigo + 1) : 1;
    }
    public function guardarPreAnalitica(Request $request, $id)
    {
        $solicitud = Solicitude::findOrFail($id);
        $area_tipo_muestras = $request->input('area_tipo_muestras', []);
        foreach ($area_tipo_muestras as $area) {
            if (isset($area['area_tipo_muestras']) && is_array($area['area_tipo_muestras'])) {
                foreach ($area['area_tipo_muestras'] as $tipoMuestra) {
                    $id = $tipoMuestra['id'];
                    $existing = \App\Models\SolitudePreAnalitica::where('solicitude_id', $solicitud->id)
                        ->where('area_tipo_muestra_id', $id)
                        ->first();
                    if (!$existing) {
                        $findArea = \App\Models\AreaTipoMuestra::find($id);
                        $SolitudePreAnalitica = new \App\Models\SolitudePreAnalitica();
                        $SolitudePreAnalitica->solicitude_id = $solicitud->id;
                        $SolitudePreAnalitica->area_tipo_muestra_id = $id;
                        $SolitudePreAnalitica->estado = 'Pendiente';
                        $SolitudePreAnalitica->nombre = $findArea ? $findArea->tipo_muestra : '';
                        $SolitudePreAnalitica->selected = !empty($tipoMuestra['selected']) ? true : false;
                        $SolitudePreAnalitica->save();
                    }
                }
            }
        }
        $solicitud->fecha_envio_analitica = now();
        $solicitud->estado = 'ENVIADO_ANALITICA';
        $solicitud->save();

        return response()->json([
            'message' => 'Muestras preanalíticas actualizadas',
            'area_tipo_muestras' => $solicitud,
        ]);
    }

    function solicitudesAreaPreanalitica(Request $request){

        $filter = $request->input('filter', '');

        $query = Solicitude::with([
            'paciente',
            'doctor',
            'servicios.area.areaTipoMuestras',
            'userPreanalitica',
            'user'
        ])
            ->whereIn('estado', ['CREADO', 'ATENDIENDO']);

        if (!empty($filter)) {
            $query->whereHas('paciente', function ($q) use ($filter) {
                $q->where('nombre_completo', 'like', "%$filter%")
                    ->orWhere('ci', 'like', "%$filter%");
            });
        }
        $perPage = $request->input('per_page', 10);
        $solicitudes = $query->orderBy('id', 'desc')->paginate($perPage);

        return response()->json($solicitudes);
    }
    function nroRegistro($nombreCompleto, $fechaNac)
    {
        $nombreCompleto = trim((string) $nombreCompleto);

        // Si falta nombre o fecha, devolvemos null (o lo que prefieras)
        if ($nombreCompleto === '' || empty($fechaNac)) {
            return null;
        }

        // Separar en partes (nombre y apellidos)
        $partes = preg_split('/\s+/', mb_strtoupper($nombreCompleto, 'UTF-8'));

        // Tomar nombre y apellidos según la cantidad de palabras
        if (count($partes) >= 3) {
            // Ej: ADIMER PAUL CHAMBI AJATA -> ADIMER | CHAMBI | AJATA
            $nombre   = $partes[0];
            $apPat    = $partes[count($partes) - 2];
            $apMat    = $partes[count($partes) - 1];
        } elseif (count($partes) === 2) {
            // Ej: "MARIA PEREZ" -> MARIA | PEREZ | PEREZ
            $nombre   = $partes[0];
            $apPat    = $partes[1];
            $apMat    = $partes[1]; // repetimos el mismo apellido
        } else {
            // Solo una palabra: "MARIA" -> MARIA | MARIA | MARIA
            $nombre   = $partes[0];
            $apPat    = $partes[0];
            $apMat    = $partes[0];
        }

        // Iniciales (3 letras)
        $iniciales =
            mb_substr($nombre, 0, 1, 'UTF-8') .
            mb_substr($apPat, 0, 1, 'UTF-8') .
            mb_substr($apMat, 0, 1, 'UTF-8');

        // Fecha en formato ddmmyy
        $timestamp = strtotime($fechaNac);
        if ($timestamp === false) {
            // Si la fecha no es válida, devolvemos solo las iniciales
            return $iniciales;
        }

        $fechaFormateada = date('dmy', $timestamp); // 02 04 89 -> "020489"

        return $iniciales . $fechaFormateada;
    }
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
//        $fecha_creacion
            $data['fecha_creacion'] = now();

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
