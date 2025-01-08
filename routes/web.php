<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\IndexController;

Route::get('/import', [ImportController::class, 'index']);
Route::post('/import', [ImportController::class, 'import'])->name('import');

Route::get('/', [IndexController::class, 'index']);
Route::post('/update-winner', [IndexController::class, 'updateWinner']);
Route::post('/check-total-winner', [IndexController::class, 'checkTotalWinner']);
Route::post('/remove-winner', [IndexController::class, 'removeWinner']);

