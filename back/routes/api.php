<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\AreaRangoController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ConsentimientoController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\SolicitudeController;
use App\Http\Controllers\AreaTipoMuestraController; // <-- NUEVO
use Illuminate\Support\Facades\Route;

Route::post('/login', [App\Http\Controllers\UserController::class, 'login']);

Route::group(['middleware' => ['auth:sanctum']], function () {

    Route::get('/me', [App\Http\Controllers\UserController::class, 'me']);
    Route::post('/logout', [App\Http\Controllers\UserController::class, 'logout']);

    Route::get('/users', [App\Http\Controllers\UserController::class, 'index']);
    Route::post('/users', [App\Http\Controllers\UserController::class, 'store']);
    Route::put('/users/{user}', [App\Http\Controllers\UserController::class, 'update']);
    Route::delete('/users/{user}', [App\Http\Controllers\UserController::class, 'destroy']);
    Route::put('/updatePassword/{user}', [App\Http\Controllers\UserController::class, 'updatePassword']);
    Route::post('/{user}/avatar', [App\Http\Controllers\UserController::class, 'updateAvatar']);
    Route::get('/permissions', [App\Http\Controllers\UserController::class, 'permissions']);
    Route::get('/users/{user}/permissions', [App\Http\Controllers\UserController::class, 'userPermissions']);
    Route::put('/users/{user}/permissions', [App\Http\Controllers\UserController::class, 'updateUserPermissions']);

    // Pacientes
    Route::apiResource('pacientes', PacienteController::class);
    Route::get('pacientes/buscar-ci/{ci}', [PacienteController::class, 'buscarPorCi']);

    // Consentimientos
    Route::apiResource('consentimientos', ConsentimientoController::class);
    Route::apiResource('doctores', DoctorController::class);

    Route::apiResource('solicitudes', SolicitudeController::class);
    Route::apiResource('doctores', DoctorController::class);
    Route::apiResource('pacientes', PacienteController::class);

    Route::apiResource('establecimientos', EstablecimientoController::class);

    Route::apiResource('areas', AreaController::class);
    Route::apiResource('servicios', ServicioController::class);

    Route::get('solicitudes-area-preanalitica', [SolicitudeController::class, 'solicitudesAreaPreanalitica']);
    Route::post('solicitudes/{id}/pre-analitica', [SolicitudeController::class, 'guardarPreAnalitica']);
    Route::get('areas-tipo-muestras', [AreaController::class, 'tipoMuestras']);

    Route::post('solicitudes/{id}/generar-codigo', [SolicitudeController::class, 'generarCodigo']);

    Route::get('solicitudes-area-analitica', [SolicitudeController::class, 'solicitudesAreaAnalitica']);
    Route::post('solicitudes/{id}/analitica', [SolicitudeController::class, 'guardarAnalitica']);

    Route::apiResource('area-rangos', AreaRangoController::class);

    // NUEVO: CRUD de tipos de muestra por área
    Route::apiResource('area-tipo-muestras', AreaTipoMuestraController::class);

    Route::get('solicitudes-area-analitica', [SolicitudeController::class, 'solicitudesAreaAnalitica']);
    Route::get('solicitudes-area-analitica/{id}', [SolicitudeController::class, 'showAnalitica']); // NUEVA
    Route::post('solicitudes/{id}/analitica', [SolicitudeController::class, 'guardarAnalitica']);
});

Route::get('consentimientos/{id}/print', [ConsentimientoController::class, 'print']);
