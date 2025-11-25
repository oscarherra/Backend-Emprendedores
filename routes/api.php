<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmprendedorController;
use App\Http\Controllers\Api\StatisticsController;
use App\Http\Controllers\CaptchaController;


Route::post('/login', [AuthController::class, 'login']);

Route::post('/captcha/verify', [CaptchaController::class, 'verify']);
Route::post('/emprendedores', [EmprendedorController::class, 'store']);

Route::middleware(['auth:sanctum', 'is_admin'])->group(function () {

        // --- Rutas para la gestión de Emprendedores ---
    Route::get('/emprendedores', [EmprendedorController::class, 'index']);
    Route::get('/emprendedores/{emprendedor}', [EmprendedorController::class, 'show']);
    Route::put('/emprendedores/{emprendedor}', [EmprendedorController::class, 'update']);
    Route::delete('/emprendedores/{emprendedor}', [EmprendedorController::class, 'destroy']);
    
    // --- Ruta para el Dashboard de Estadísticas ---
    Route::get('/statistics', [StatisticsController::class, 'index']);
    
    // --- Ruta para obtener los datos del usuario logueado ---
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

});