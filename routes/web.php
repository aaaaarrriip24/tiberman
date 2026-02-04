<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SuratJalanController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\ProofController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {

    // 
    Route::get('/surat-jalan/{id}/detail', [SuratJalanController::class, 'detail'])->name('sj.detail');

    // Creator + Superuser
    Route::middleware('role:creator,superuser')->group(function () {
        Route::get('/surat-jalan', [SuratJalanController::class, 'index'])->name('sj.index');
        Route::get('/surat-jalan/datatable', [SuratJalanController::class, 'datatable'])->name('sj.datatable');
        Route::post('/surat-jalan', [SuratJalanController::class, 'store'])->name('sj.store');
        // Route::get('/surat-jalan/{id}/detail', [SuratJalanController::class, 'detail'])->name('sj.detail');
        Route::put('/surat-jalan/{id}', [SuratJalanController::class, 'update'])->name('sj.update');
    });

    // Admin + Superuser (scan & upload bukti)
    Route::middleware('role:admin,superuser')->group(function () {
        Route::get('/scan', [ScanController::class, 'index'])->name('scan.index');
        Route::post('/scan', [ScanController::class, 'store'])->name('scan.store');

        Route::post('/surat-jalan/{id}/proof', [ProofController::class, 'store'])->name('proof.store');
    });

    // View all roles
    Route::get('/surat-jalan/{id}', [SuratJalanController::class, 'show'])->name('sj.show');
});