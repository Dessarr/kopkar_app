<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\SimpananController;
use App\Http\Controllers\PinjamanController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\MobilController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\PengajuanController;

Route::middleware('web')->group(function () {
Route::get('/', function () {
    return view('auth.login');
    });

    
Route::get('/', function () {
    return view('auth/login');
});

Route::get('/admin/dashboard',function (){
    return view('admin/dashboard');
});

Route::get('/member/dashboard',function (){
    return view('member/dashboard');
});
});

// API routes should not be in the web group if they are stateless
Route::prefix('api')->group(function () {
    // Anggota Routes
    Route::resource('anggota', AnggotaController::class);
    Route::get('anggota/{anggota}/simpanan', [AnggotaController::class, 'getByAnggota']);
    
    // Simpanan Routes
    Route::resource('simpanan', SimpananController::class);
    Route::get('simpanan/anggota/{anggotaId}', [SimpananController::class, 'getByAnggota']);
    
    // Pinjaman Routes
    Route::resource('pinjaman', PinjamanController::class);
    Route::get('pinjaman/anggota/{anggotaId}', [PinjamanController::class, 'getByAnggota']);
    Route::post('pinjaman/{id}/approve', [PinjamanController::class, 'approve']);
    Route::post('pinjaman/{id}/reject', [PinjamanController::class, 'reject']);
    
    // Barang (Toserda) Routes
    Route::resource('barang', BarangController::class);
    
    // Mobil (Angkutan) Routes
    Route::resource('mobil', MobilController::class);
    
    // Kas Routes
    Route::resource('kas', KasController::class);
    
    // Pengajuan Routes
    Route::resource('pengajuan', PengajuanController::class);
});

// Route untuk modul kas
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/kas', [KasController::class, 'index'])->name('kas.index');
    Route::get('/kas/create', [KasController::class, 'create'])->name('kas.create');
    Route::post('/kas', [KasController::class, 'store'])->name('kas.store');
    Route::get('/kas/{id}', [KasController::class, 'show'])->name('kas.show');
    Route::get('/kas/report', [KasController::class, 'report'])->name('kas.report');
});
