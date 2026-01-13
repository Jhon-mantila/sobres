<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RegistroImagenController;

Route::get('/sobres-plantilla/{id}/imagenes', [RegistroImagenController::class, 'indexBySobre']);
Route::post('/registro-imagenes', [RegistroImagenController::class, 'store']);
Route::post('/registro-imagenes/update-order', [RegistroImagenController::class, 'updateOrder']);
Route::delete('/registro-imagenes/{id}', [RegistroImagenController::class, 'destroy']);
Route::post('/registro-imagenes/delete-multiple', [RegistroImagenController::class, 'destroyMultiple']);
