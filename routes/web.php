<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\JnsAkunController;
use App\Http\Controllers\KasController;
use App\Http\Controllers\JnsSimpanController;
use App\Http\Controllers\DtaPenggunaController;
use App\Http\Controllers\DtaBarangController;
use App\Http\Controllers\DtaMobilController;
use App\Http\Controllers\JnsAngusuranController;
use App\Http\Controllers\DtaAnggotaController;
use App\Http\Controllers\DtaKasController;
use App\Http\Controllers\DtaPengajuanController;

use App\Http\Controllers\BillingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TransaksiKasController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SukuBungaController;

Route::get('/', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.post');
Route::get('/admin/dashboard', [AdminController::class, 'adminDashboard'])->name('admin.dashboard');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');


// Route untuk modul kas

    Route::get('/transaksi_kas/pemasukan', [TransaksiKasController::class, 'pemasukan'])->name('kas.pemasukan');
    Route::get('/transaksi_kas/pengeluaran', [TransaksiKasController::class, 'pengeluaran'])->name('kas.pengeluaran');
    Route::get('/transaksi_kas/transfer', [TransaksiKasController::class, 'transfer'])->name('kas.transfer');
//Route billing
    Route::get('/billing',[BillingController::class, 'index'])->name('billing.index');


    //Route untuk Pinjaman
    Route::get('/pinjaman/data_pengajuan', [DtaPengajuanController::class, 'index'])->name('pinjaman.data_pengajuan');

//Route untuk Master Data

    Route::get('/master-data/jns_akun',[JnsAkunController::class,'index'])->name('master-data.jns_akun');
    Route::get('/master-data/jns_simpan',[JnsSimpanController::class,'index'])->name('master-data.jns_simpan');
    Route::get('/master-data/data_pengguna',[DtaPenggunaController::class,'index'])->name('master-data.data_pengguna');
    Route::get('/master-data/data_barang',[DtaBarangController::class,'index'])->name('master-data.data_barang');
    Route::get('/master-data/data_mobil',[DtaMobilController::class,'index'])->name('master-data.data_mobil');
    Route::get('/master-data/jenis_angsuran',[JnsAngusuranController::class,'index'])->name('master-data.jenis_angsuran');
    Route::get('/master-data/data_anggota',[DtaAnggotaController::class,'index'])->name('master-data.data_anggota');
    Route::get('/master-data/data_Kas',[DtaKasController::class,'index'])->name('master-data.data_kas');

    
//Route untuk Setting

    Route::get('/settings/identitas_koperasi',[SettingController::class,'index'])->name('settings.identitas_koperasi');
    Route::post('/settings/identitas_koperasi/update', [SettingController::class, 'update'])->name('settings.identitas_koperasi.update');
    Route::get('/settings/suku_bunga',[SukuBungaController::class,'index'])->name('settings.suku_bunga');
    Route::post('/settings/suku_bunga/update', [SukuBungaController::class, 'update'])->name('settings.suku_bunga.update');















    
    
    // // API routes should not be in the web group if they are stateless
    // Route::prefix('api')->group(function () {
    //     // Anggota Routes
    //     Route::resource('anggota', AnggotaController::class);
    //     Route::get('anggota/{anggota}/simpanan', [AnggotaController::class, 'getByAnggota']);
    
    //     // Simpanan Routes
    //     Route::resource('simpanan', SimpananController::class);
    //     Route::get('simpanan/anggota/{anggotaId}', [SimpananController::class, 'getByAnggota']);
    
    //     // Pinjaman Routes
    //     Route::resource('pinjaman', PinjamanController::class);
    //     Route::get('pinjaman/anggota/{anggotaId}', [PinjamanController::class, 'getByAnggota']);
    //     Route::post('pinjaman/{id}/approve', [PinjamanController::class, 'approve']);
    //     Route::post('pinjaman/{id}/reject', [PinjamanController::class, 'reject']);
    
    //     // Barang (Toserda) Routes
    //     Route::resource('barang', BarangController::class);
    
    //     // Mobil (Angkutan) Routes
    //     Route::resource('mobil', MobilController::class);
    
    //     // Kas Routes
    //     Route::resource('kas', KasController::class);
    
    //     // Pengajuan Routes
    //     Route::resource('pengajuan', PengajuanController::class);
    // });