<?php

use App\Http\Controllers\ArrivalController;
use App\Http\Controllers\ScannerController;
use App\Http\Controllers\SupplierController;
use Illuminate\Support\Facades\Route;

Route::get('/', [ScannerController::class, 'index'])->name('scanner.index');

Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index');
Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store');
Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update');
Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy');

Route::get('/arrivals', [ArrivalController::class, 'index'])->name('arrivals.index');
Route::post('/arrivals', [ArrivalController::class, 'store'])->name('arrivals.store');
Route::get('/arrivals/{arrival}', [ArrivalController::class, 'show'])->name('arrivals.show');
Route::post('/arrivals/{arrival}/details', [ArrivalController::class, 'storeDetail'])->name('arrivals.details.store');
Route::delete('/arrivals/{arrival}/details', [ArrivalController::class, 'clearDetails'])->name('arrivals.details.clear');
Route::delete('/arrivals/{arrival}', [ArrivalController::class, 'destroy'])->name('arrivals.destroy');
