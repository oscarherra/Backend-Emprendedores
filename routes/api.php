<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\EmprendedorController;
use App\Http\Controllers\Api\StatisticsController;

/*
|--------------------------------------------------------------------------
| Rutas de la API
|--------------------------------------------------------------------------
|
| Aquí se definen los endpoints que tu aplicación de Vue llamará.
|
*/

// ============================================
//   RUTAS PÚBLICAS (No requieren autenticación)
// ============================================

// Ruta para que el ADMIN inicie sesión.
Route::post('/login', [AuthController::class, 'login']);

// Ruta PÚBLICA para que cualquier persona envíe los datos del formulario de emprendedor.
// Esta es la corrección clave: la ruta de creación está fuera del grupo protegido.
Route::post('/emprendedores', [EmprendedorController::class, 'store']);


// ===============================================================
//   RUTAS PROTEGIDAS (Solo para el ADMIN que ha iniciado sesión)
// ===============================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- Rutas para la GESTIÓN de Emprendedores (ver, actualizar, borrar) ---
    // Estas son las acciones que el administrador puede realizar desde el dashboard.
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