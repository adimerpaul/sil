<?php

use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PacienteController;
use App\Http\Controllers\ConsentimientoController;
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
});
Route::get('consentimientos/{id}/print', [ConsentimientoController::class, 'print']);
