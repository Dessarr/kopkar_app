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
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SukuBungaController;

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

// Route untuk modul kas

    Route::get('/kas/pemasukan', [KasController::class, 'pemasukanView'])->name('kas.pemasukan');
    Route::get('/kas/pengeluaran', [KasController::class, 'pengeluaranView'])->name('kas.pengeluaran');
    Route::get('/kas/transfer', [KasController::class, 'transferView'])->name('kas.transfer');


    //working but not used
    Route::get('/kas/create', [KasController::class, 'create'])->name('kas.create');

    //idk what they for
    Route::post('/kas', [KasController::class, 'store'])->name('kas.store');
    Route::get('/kas/{id}', [KasController::class, 'show'])->name('kas.show');
    Route::get('/kas/report', [KasController::class, 'report'])->name('kas.report');



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


    