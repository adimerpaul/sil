<?php

use App\Http\Controllers\AreaController;
use App\Http\Controllers\AreaRangoController;
use App\Http\Controllers\AlmacenItemController;
use App\Http\Controllers\CompraController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\EstablecimientoController;
use App\Http\Controllers\FormulariosController;
use App\Http\Controllers\GrupoController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\PartidaController;
use App\Http\Controllers\ConsentimientoController;
use App\Http\Controllers\PedidoController;
use App\Http\Controllers\ProductoPorVencerController;
use App\Http\Controllers\ProductoVencidoController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\SolicitudeController;
use App\Http\Controllers\SubpartidaController;
use App\Http\Controllers\UnidadSolicitanteController;
use App\Http\Controllers\AreaTipoMuestraController; // <-- NUEVO
use App\Http\Controllers\SolicitudePropiedadController;
use App\Http\Controllers\SolicitudCatalogoController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReporteServiciosController;
use App\Http\Controllers\RecogidoController;

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
    Route::get('pacientes/nn-rn/{tipo}', [PacienteController::class, 'buscarPorTipoNN_RN']);

    // Consentimientos
    Route::apiResource('consentimientos', ConsentimientoController::class);
    Route::get('solicitudes/{id}/consentimiento', [ConsentimientoController::class, 'showBySolicitude']);
    Route::post('solicitudes/{id}/consentimiento', [ConsentimientoController::class, 'upsertBySolicitude']);
    Route::apiResource('doctores', DoctorController::class);

    Route::apiResource('solicitudes', SolicitudeController::class);
    Route::get('solicitudes-create-catalogos', [SolicitudCatalogoController::class, 'create']);
    Route::apiResource('doctores', DoctorController::class);
    Route::apiResource('pacientes', PacienteController::class);

    Route::apiResource('establecimientos', EstablecimientoController::class);
    Route::apiResource('unidad-solicitantes', UnidadSolicitanteController::class);
    Route::apiResource('proveedores', ProveedorController::class);

    Route::apiResource('grupos', GrupoController::class);
    Route::apiResource('partidas', PartidaController::class);
    Route::apiResource('subpartidas', SubpartidaController::class);
    Route::get('almacen-items/reporte/pdf', [AlmacenItemController::class, 'reportPdf']);
    Route::apiResource('almacen-items', AlmacenItemController::class);
    Route::get('almacen/productos-por-vencer', [ProductoPorVencerController::class, 'index']);
    Route::get('almacen/productos-vencidos', [ProductoVencidoController::class, 'index']);
    Route::get('compras/{id}/pdf', [CompraController::class, 'printPdf']);
    Route::apiResource('compras', CompraController::class)->only(['index', 'show', 'store', 'update', 'destroy']);
    Route::get('pedidos/{id}/pdf', [PedidoController::class, 'printPdf']);
    Route::get('pedidos/{id}/whatsapp-link', [PedidoController::class, 'whatsappLink']);
    Route::apiResource('pedidos', PedidoController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::apiResource('areas', AreaController::class);
//    areasCreateSolicitud
    Route::get('areasCreateSolicitud', [AreaController::class, 'areasCreateSolicitud']);
    Route::apiResource('servicios', ServicioController::class);
    Route::post('servicios/{id}/tipos-muestra', [ServicioController::class, 'syncTiposMuestra']);

    Route::get('solicitudes-area-preanalitica', [SolicitudeController::class, 'solicitudesAreaPreanalitica']);
    Route::get('solicitudes-area-preanalitica-estado', [SolicitudeController::class, 'solicitudesAreaPreanaliticaEstado']);
    Route::post('solicitudes/{id}/pre-analitica', [SolicitudeController::class, 'guardarPreAnalitica']);
    Route::post('solicitudes/{id}/pre-analitica-comentarios', [SolicitudeController::class, 'storePreAnaliticaComentario']);
    Route::delete('solicitudes/{id}/pre-analitica-comentarios/{comentarioId}', [SolicitudeController::class, 'destroyPreAnaliticaComentario']);
    Route::get('areas-tipo-muestras', [AreaController::class, 'tipoMuestras']);

    Route::post('solicitudes/{id}/generar-codigo', [SolicitudeController::class, 'generarCodigo']);
    Route::get('solicitudesMuestrasRechazadas', [SolicitudeController::class, 'muestrasRechazadas']);
    Route::post('solicitudes/{id}/actualizar-codigo', [SolicitudeController::class, 'actualizarCodigo']);
    Route::get('solicitudes/{id}/test-embarazo', [SolicitudeController::class, 'showTestEmbarazo']);
    Route::post('solicitudes/{id}/test-embarazo', [SolicitudeController::class, 'saveTestEmbarazo']);
    Route::get('solicitudes/{id}/test-embarazo/pdf', [SolicitudeController::class, 'printTestEmbarazo']);

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

    Route::get('reportes/servicios-resumen', [ReporteServiciosController::class, 'index']);
    Route::get('reportes/servicios-resumen/excel', [ReporteServiciosController::class, 'exportExcel']);
    Route::get('reportes/servicios-resumen/pdf', [ReporteServiciosController::class, 'exportPdf']);
    Route::get('reportes/consentimientos', [ConsentimientoController::class, 'reporte']);
    Route::get('reportes/solicitudes-servicios', [SolicitudeController::class, 'reporteSolicitudesServicios']);

    Route::post('solicitudes/{id}/marcar-muestra-no-tomada', [SolicitudeController::class, 'marcarMuestraNoTomada']);

    Route::get('recogidos', [RecogidoController::class, 'index']);
    Route::put('recogidos/{id}', [RecogidoController::class, 'update']);
    Route::post('recogidos/recoger-area', [RecogidoController::class, 'recogerArea']);
    Route::get('reportes/recogidos/pdf', [RecogidoController::class, 'reportePdf']);

});
Route::get('solicitudes-area-preanalitica/pdf', [SolicitudeController::class, 'pdfPreanalitica']);
Route::get('solicitudes/{id}/analitica-pdf', [SolicitudeController::class, 'imprimirAnalitica']);
Route::get('public/reportes/{codigo}', [SolicitudeController::class, 'imprimirAnaliticaPublica'])
    ->name('solicitudes.analitica.publica');
Route::get('public/pedidos/{id}/pdf', [PedidoController::class, 'printPdfPublic'])
    ->middleware('signed')
    ->name('pedidos.pdf.public');

Route::get('consentimientos/{id}/print', [ConsentimientoController::class, 'print']);
Route::get('solicitudes/{id}/consentimiento/print', [ConsentimientoController::class, 'printBySolicitude']);
//pacientes/nn-rn/
Route::get('pacientesnn-rn/', [PacienteController::class, 'buscarPorNN_RN']);

//pdfBySolicitude
Route::get('/hematologia/solicitud/{id}/pdf', [\App\Http\Controllers\HematologiaController::class, 'pdfBySolicitude']);
Route::get('parasitologia/solicitud/{id}/pdf', [\App\Http\Controllers\ParasitologiaController::class, 'pdfBySolicitude']);
Route::get('uroanalisis/solicitud/{id}/pdf', [\App\Http\Controllers\UroanalisisController::class, 'pdfBySolicitude']);
Route::get('/quimica-sanguinea/solicitud/{id}/pdf', [\App\Http\Controllers\QuimicaSanguineaController::class, 'pdfBySolicitude']);
//const url = `${this.$axios.defaults.baseURL}/quimica-sanguinea/solicitud/${solicitud.quimica_sanguinea?.code}/pdf-tolerancia`
Route::get('/quimica-sanguinea/solicitud/{id}/pdf-tolerancia', [\App\Http\Controllers\QuimicaSanguineaController::class, 'pdfToleranciaBySolicitude']);
//const url = `${this.$axios.defaults.baseURL}/quimica-sanguinea/solicitud/${solicitud.quimica_sanguinea?.code}/pdf-cito-quimico`
Route::get('/quimica-sanguinea/solicitud/{id}/pdf-cito-quimico', [\App\Http\Controllers\QuimicaSanguineaController::class, 'pdfCitoQuimicoBySolicitude']);
Route::get('papiloma-humano/solicitud/{id}/pdf', [\App\Http\Controllers\PapilomaHumanoController::class, 'pdfBySolicitude']);
Route::get('/panel-respiratorio/solicitud/{id}/pdf', [\App\Http\Controllers\PanelRespiratorioController::class, 'pdfBySolicitude']);
Route::get('/panel-sexual/solicitud/{id}/pdf', [\App\Http\Controllers\PanelSexualController::class, 'pdfBySolicitude']);
Route::get('/cultivo-antibiograma/solicitud/{id}/pdf', [\App\Http\Controllers\CultivoAntibiogramaController::class, 'pdfBySolicitude']);

Route::get('/inmunologia/solicitude-formulario/{id}/pdf', [\App\Http\Controllers\InmunologiaController::class, 'pdfOne']);
Route::get('/inmunologia/solicitud/{id}/pdf-all', [\App\Http\Controllers\InmunologiaController::class, 'pdfAll']);
