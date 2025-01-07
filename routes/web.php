<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ImportController;
use App\Http\Controllers\IndexController;

Route::get('/', [IndexController::class, 'index']);
Route::get('/import', [ImportController::class, 'index']);
Route::post('/import', [ImportController::class, 'import'])->name('import');

