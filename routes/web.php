<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\ArsipSertifikatController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\JenisSertifikatController;
use App\Http\Controllers\PesertaController;
use App\Http\Controllers\SertifikatController;
use App\Http\Controllers\TemplateSertifikatController;
use App\Http\Controllers\VerifikasiSertifikatController;
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

Route::get('/verifikasi/{token}/pdf', [VerifikasiSertifikatController::class, 'preview'])->name('certificates.verify.pdf');
Route::get('/verifikasi/{token}/unduh', [VerifikasiSertifikatController::class, 'download'])->name('certificates.verify.download');
Route::get('/verifikasi/{token}', [VerifikasiSertifikatController::class, 'show'])->name('certificates.verify');

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
    Route::get('/jenis-sertifikat', [JenisSertifikatController::class, 'index'])->name('certificate-types.index');
    Route::post('/jenis-sertifikat', [JenisSertifikatController::class, 'store'])->name('certificate-types.store');
    Route::put('/jenis-sertifikat/{jenis}', [JenisSertifikatController::class, 'update'])->name('certificate-types.update');
    Route::patch('/jenis-sertifikat/{jenis}/status', [JenisSertifikatController::class, 'toggle'])->name('certificate-types.toggle');
    Route::delete('/jenis-sertifikat/{jenis}', [JenisSertifikatController::class, 'destroy'])->name('certificate-types.destroy');
    Route::get('/template-sertifikat', [TemplateSertifikatController::class, 'index'])->name('certificate-templates.index');
    Route::get('/template-sertifikat/create', [TemplateSertifikatController::class, 'create'])->name('certificate-templates.create');
    Route::post('/template-sertifikat', [TemplateSertifikatController::class, 'store'])->name('certificate-templates.store');
    Route::get('/template-sertifikat/{template}/edit', [TemplateSertifikatController::class, 'edit'])->name('certificate-templates.edit');
    Route::get('/template-sertifikat/{template}/preview', [TemplateSertifikatController::class, 'preview'])->name('certificate-templates.preview');
    Route::put('/template-sertifikat/{template}', [TemplateSertifikatController::class, 'update'])->name('certificate-templates.update');
    Route::patch('/template-sertifikat/{template}/status', [TemplateSertifikatController::class, 'toggle'])->name('certificate-templates.toggle');
    Route::delete('/template-sertifikat/{template}', [TemplateSertifikatController::class, 'destroy'])->name('certificate-templates.destroy');
    Route::get('/sertifikat/arsip', [ArsipSertifikatController::class, 'index'])->name('certificate-archive.index');
    Route::get('/sertifikat/arsip/unduh', [ArsipSertifikatController::class, 'downloadAll'])->name('certificate-archive.download');
    Route::get('/sertifikat/arsip/{sertifikat}', [ArsipSertifikatController::class, 'show'])->name('certificate-archive.show');
    Route::post('/sertifikat/arsip/{sertifikat}/batalkan', [ArsipSertifikatController::class, 'cancel'])->name('certificate-archive.cancel');
    Route::get('/sertifikat/terbitkan', [SertifikatController::class, 'create'])->name('certificate-issues.create');
    Route::post('/sertifikat/terbitkan', [SertifikatController::class, 'store'])->name('certificate-issues.store');
    Route::get('/sertifikat/{sertifikat}/pratinjau', [SertifikatController::class, 'preview'])->name('certificate-issues.preview');
    Route::get('/sertifikat/{sertifikat}/unduh', [SertifikatController::class, 'download'])->name('certificate-issues.download');
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});
