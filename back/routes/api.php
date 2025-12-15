<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\AreaRangoController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\FormulariosController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ConsentimientoController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\SolicitudeController;
use App\Http\Controllers\AreaTipoMuestraController; // <-- NUEVO
use App\Http\Controllers\SolicitudePropiedadController;
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

//    Route::get('solicitudes-area-analitica', [SolicitudeController::class, 'solicitudesAreaAnalitica']);
//    Route::post('solicitudes/{id}/analitica', [SolicitudeController::class, 'guardarAnalitica']);

    Route::apiResource('area-rangos', AreaRangoController::class);

    // NUEVO: CRUD de tipos de muestra por área
    Route::apiResource('area-tipo-muestras', AreaTipoMuestraController::class);

    Route::get('solicitudes-area-analitica', [SolicitudeController::class, 'solicitudesAreaAnalitica']);
    Route::get('solicitudes-area-analitica/{id}', [SolicitudeController::class, 'showAnalitica']); // NUEVA
    Route::post('solicitudes/{id}/analitica', [SolicitudeController::class, 'guardarAnalitica']);

    Route::apiResource('solicitude-propiedades', SolicitudePropiedadController::class);

//    analitica/solicitudes
    Route::get('solicitudesAnalitica', [SolicitudeController::class, 'solicitudesAnalitica']);

    Route::get   ('formularios',        [FormulariosController::class, 'index']);
    Route::post  ('formularios',        [FormulariosController::class, 'store']);
    Route::get   ('formularios/{id}',   [FormulariosController::class, 'show']);
    Route::put   ('formularios/{id}',   [FormulariosController::class, 'update']);
    Route::delete('formularios/{id}',   [FormulariosController::class, 'destroy']);

    Route::get('reportes/solicitudes-dashboard', [SolicitudeController::class, 'dashboard']);

    Route::get('/hematologia/solicitud/{id}', [\App\Http\Controllers\HematologiaController::class, 'showBySolicitude']);
    Route::post('/hematologia/solicitud/{id}', [\App\Http\Controllers\HematologiaController::class, 'upsert']);
    Route::delete('/hematologia/solicitud/{id}', [\App\Http\Controllers\HematologiaController::class, 'destroyBySolicitude']);

    Route::get('quimica-sanguinea/solicitud/{id}', [\App\Http\Controllers\QuimicaSanguineaController::class, 'showBySolicitude']);
    Route::post('quimica-sanguinea/solicitud/{id}', [\App\Http\Controllers\QuimicaSanguineaController::class, 'upsert']);
    Route::delete('quimica-sanguinea/solicitud/{id}', [\App\Http\Controllers\QuimicaSanguineaController::class, 'destroyBySolicitude']);

    Route::get('uroanalisis/solicitud/{id}', [\App\Http\Controllers\UroanalisisController::class, 'showBySolicitude']);
    Route::post('uroanalisis/solicitud/{id}', [\App\Http\Controllers\UroanalisisController::class, 'upsert']);
    Route::delete('uroanalisis/solicitud/{id}', [\App\Http\Controllers\UroanalisisController::class, 'destroyBySolicitude']);

    Route::get('parasitologia/solicitud/{id}', [\App\Http\Controllers\ParasitologiaController::class, 'showBySolicitude']);
    Route::post('parasitologia/solicitud/{id}', [\App\Http\Controllers\ParasitologiaController::class, 'upsert']);
    Route::delete('parasitologia/solicitud/{id}', [\App\Http\Controllers\ParasitologiaController::class, 'destroyBySolicitude']);

    Route::get('diagnosticos', [\App\Http\Controllers\DiagnosticoController::class, 'index']);
    Route::get('papiloma-humano/solicitud/{id}', [\App\Http\Controllers\PapilomaHumanoController::class, 'showBySolicitude']);
    Route::post('papiloma-humano/solicitud/{id}', [\App\Http\Controllers\PapilomaHumanoController::class, 'upsert']);
    Route::delete('papiloma-humano/solicitud/{id}', [\App\Http\Controllers\PapilomaHumanoController::class, 'destroyBySolicitude']);

    Route::get('/panel-respiratorio/solicitud/{id}', [\App\Http\Controllers\PanelRespiratorioController::class, 'showBySolicitude']);
    Route::post('/panel-respiratorio/solicitud/{id}', [\App\Http\Controllers\PanelRespiratorioController::class, 'upsert']);
    Route::delete('/panel-respiratorio/solicitud/{id}', [\App\Http\Controllers\PanelRespiratorioController::class, 'destroyBySolicitude']);

    Route::get('/panel-sexual/solicitud/{id}', [\App\Http\Controllers\PanelSexualController::class, 'showBySolicitude']);
    Route::post('/panel-sexual/solicitud/{id}', [\App\Http\Controllers\PanelSexualController::class, 'upsert']);
    Route::delete('/panel-sexual/solicitud/{id}', [\App\Http\Controllers\PanelSexualController::class, 'destroyBySolicitude']);

    Route::get('/cultivo-antibiograma/solicitud/{id}', [\App\Http\Controllers\CultivoAntibiogramaController::class, 'showBySolicitude']);
    Route::post('/cultivo-antibiograma/solicitud/{id}', [\App\Http\Controllers\CultivoAntibiogramaController::class, 'upsert']);
    Route::delete('/cultivo-antibiograma/solicitud/{id}', [\App\Http\Controllers\CultivoAntibiogramaController::class, 'destroyBySolicitude']);

    Route::get('/inmunologia/solicitud/{id}', [\App\Http\Controllers\InmunologiaController::class, 'dashboard']);
    Route::post('/inmunologia/solicitud/{id}/add', [\App\Http\Controllers\InmunologiaController::class, 'add']);

    Route::put('/inmunologia/solicitude-formulario/{id}', [\App\Http\Controllers\InmunologiaController::class, 'update']);
    Route::delete('/inmunologia/solicitude-formulario/{id}', [\App\Http\Controllers\InmunologiaController::class, 'remove']);

});
Route::get('solicitudes/{id}/analitica-pdf', [SolicitudeController::class, 'imprimirAnalitica']);
Route::get('public/reportes/{codigo}', [SolicitudeController::class, 'imprimirAnaliticaPublica'])
    ->name('solicitudes.analitica.publica');

Route::get('consentimientos/{id}/print', [ConsentimientoController::class, 'print']);

//pdfBySolicitude
Route::get('/hematologia/solicitud/{id}/pdf', [\App\Http\Controllers\HematologiaController::class, 'pdfBySolicitude']);
Route::get('parasitologia/solicitud/{id}/pdf', [\App\Http\Controllers\ParasitologiaController::class, 'pdfBySolicitude']);
Route::get('uroanalisis/solicitud/{id}/pdf', [\App\Http\Controllers\UroanalisisController::class, 'pdfBySolicitude']);
Route::get('/quimica-sanguinea/solicitud/{id}/pdf', [\App\Http\Controllers\QuimicaSanguineaController::class, 'pdfBySolicitude']);
Route::get('papiloma-humano/solicitud/{id}/pdf', [\App\Http\Controllers\PapilomaHumanoController::class, 'pdfBySolicitude']);
Route::get('/panel-respiratorio/solicitud/{id}/pdf', [\App\Http\Controllers\PanelRespiratorioController::class, 'pdfBySolicitude']);
Route::get('/panel-sexual/solicitud/{id}/pdf', [\App\Http\Controllers\PanelSexualController::class, 'pdfBySolicitude']);
Route::get('/cultivo-antibiograma/solicitud/{id}/pdf', [\App\Http\Controllers\CultivoAntibiogramaController::class, 'pdfBySolicitude']);

Route::get('/inmunologia/solicitude-formulario/{id}/pdf', [\App\Http\Controllers\InmunologiaController::class, 'pdfOne']);
Route::get('/inmunologia/solicitud/{id}/pdf-all', [\App\Http\Controllers\InmunologiaController::class, 'pdfAll']);






