<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DeclaracionJuradaController;

Route::middleware(['auth'])->group(function () {
    Route::get('/imprimir/hr/{id}', [DeclaracionJuradaController::class, 'imprimirHr'])
        ->name('imprimir.hr');
    Route::get('/imprimir/pu/{id}', [DeclaracionJuradaController::class, 'imprimirPu'])
        ->name('imprimir.pu');
});

Route::get('/', function () {
    return view('welcome');
});
