<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PesertaController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::guard('web')->check()
        ? redirect()->route('dashboard')
        : view('auth.login');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])
        ->middleware('throttle:5,1')
        ->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::post('/peserta/import', [PesertaController::class, 'import'])->name('participants.import');
    Route::resource('/peserta', PesertaController::class)->names([
        'index' => 'participants.index',
        'create' => 'participants.create',
        'store' => 'participants.store',
        'show' => 'participants.show',
        'edit' => 'participants.edit',
        'update' => 'participants.update',
        'destroy' => 'participants.destroy',
    ]);
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
