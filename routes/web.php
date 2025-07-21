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

use App\Http\Controllers\DtaPengajuanPenarikanController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TransaksiKasController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SukuBungaController;
use App\Http\Controllers\SimpananController;
use App\Http\Controllers\ToserdaController;
use App\Http\Controllers\AngkutanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AnggotaController;

// Admin Routes
Route::get('/', [AdminController::class, 'showLoginForm'])->name('admin.login');
Route::post('/admin/login', [AdminController::class, 'login'])->name('admin.login.post');
Route::get('/admin/dashboard', [AdminController::class, 'adminDashboard'])->name('admin.dashboard')->middleware('auth:admin');
Route::post('/admin/logout', [AdminController::class, 'logout'])->name('admin.logout');

// Member Routes
Route::get('/member/login', [MemberController::class, 'showLoginForm'])->name('member.login');
Route::post('/member/login', [MemberController::class, 'login'])->name('member.login.post');
Route::get('/member/dashboard', [MemberController::class, 'memberDashboard'])->name('member.dashboard')->middleware('auth:member');
Route::post('/member/logout', [MemberController::class, 'logout'])->name('member.logout');
Route::get('/member/toserda', [MemberController::class, 'toserdaPayment'])->name('member.toserda.payment')->middleware('auth:member');
Route::post('/member/toserda/process/{billing_code}', [MemberController::class, 'processToserda'])->name('member.toserda.process')->middleware('auth:member');

// Anggota Routes
Route::middleware(['auth:member'])->group(function () {
    Route::get('/anggota/bayar-toserda', [AnggotaController::class, 'bayarToserda'])->name('anggota.bayar.toserda');
    Route::post('/anggota/bayar-toserda/process/{billing_code}', [AnggotaController::class, 'processPayment'])->name('anggota.bayar.toserda.process');
    Route::get('/anggota/get-transaksi-period', [AnggotaController::class, 'getTransaksiByPeriod'])->name('anggota.get.transaksi.period');
});

// Protected Routes (Admin Only)
Route::middleware(['auth:admin'])->group(function () {
    // Route untuk modul kas
    Route::get('/transaksi_kas/pemasukan', [TransaksiKasController::class, 'pemasukan'])->name('kas.pemasukan');
    Route::get('/transaksi_kas/pengeluaran', [TransaksiKasController::class, 'pengeluaran'])->name('kas.pengeluaran');
    Route::get('/transaksi_kas/transfer', [TransaksiKasController::class, 'transfer'])->name('kas.transfer');

    //Route billing
    Route::prefix('billing')->middleware(['auth:admin'])->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/process/{billing_code}', [BillingController::class, 'processPayment'])->name('billing.process');
        Route::get('/export/excel', [BillingController::class, 'exportExcel'])->name('billing.export.excel');
        Route::get('/export/pdf', [BillingController::class, 'exportPdf'])->name('billing.export.pdf');
        Route::get('/generate/{bulan}/{tahun}', [BillingController::class, 'generateBillingForPeriod'])->name('billing.generate');
    });

    //Route untuk Pinjaman
    Route::get('/pinjaman/data_pengajuan', [DtaPengajuanController::class, 'index'])->name('pinjaman.data_pengajuan');

    //Route untuk Master Data
    Route::get('/master-data/jns_akun',[JnsAkunController::class,'index'])->name('master-data.jns_akun');
    Route::get('/master-data/jns_simpan',[JnsSimpanController::class,'index'])->name('master-data.jns_simpan');
    Route::get('/master-data/data_pengguna',[DtaPenggunaController::class,'index'])->name('master-data.data_pengguna');
    Route::get('/master-data/data_barang',[DtaBarangController::class,'index'])->name('master-data.data_barang');
    Route::get('/master-data/data_mobil',[DtaMobilController::class,'index'])->name('master-data.data_mobil');
    Route::get('/master-data/jenis_angsuran',[JnsAngusuranController::class,'index'])->name('master-data.jenis_angsuran');
    
    // Route untuk Data Anggota
    Route::get('/master-data/data_anggota',[DtaAnggotaController::class,'index'])->name('master-data.data_anggota');
    Route::get('/master-data/data_anggota/nonaktif', [DtaAnggotaController::class,  'nonaktif'])->name('master-data.data_anggota.nonaktif');
    Route::get('/master-data/data_anggota/export',[DtaAnggotaController::class,'export'])->name('master-data.data_anggota.export');
    Route::get('/master-data/data_anggota/create', [DtaAnggotaController::class, 'create'])->name('master-data.data_anggota.create');
    Route::get('/master-data/data_anggota/{id}', [DtaAnggotaController::class, 'show'])->name('master-data.data_anggota.show');
    Route::get('/master-data/data_anggota/{id}/edit', [DtaAnggotaController::class, 'edit'])->name('master-data.data_anggota.edit');
    Route::put('/master-data/data_anggota/{id}', [DtaAnggotaController::class, 'update'])->name('master-data.data_anggota.update');
    Route::delete('/master-data/data_anggota/{id}', [DtaAnggotaController::class, 'destroy'])->name('master-data.data_anggota.destroy');
    Route::post('/master-data/data_anggota', [DtaAnggotaController::class, 'store'])->name('master-data.data_anggota.store');
    
    Route::get('/master-data/data_Kas',[DtaKasController::class,'index'])->name('master-data.data_kas');
    //Route CRUD untuk master Data
    // Route::get('/master-data/data_anggota/add', function(){
    //     return view('layouts.form.add_data_anggota');
    // });
    
//Route untuk Setting

    Route::get('/settings/identitas_koperasi',[SettingController::class,'index'])->name('settings.identitas_koperasi');
    Route::post('/settings/identitas_koperasi/update', [SettingController::class, 'update'])->name('settings.identitas_koperasi.update');
    Route::get('/settings/suku_bunga',[SukuBungaController::class,'index'])->name('settings.suku_bunga');
    Route::post('/settings/suku_bunga/update', [SukuBungaController::class, 'update'])->name('settings.suku_bunga.update');

    // Toserda Routes
    Route::prefix('toserda')->group(function () {
        Route::get('/penjualan', [ToserdaController::class, 'penjualan'])->name('toserda.penjualan');
        Route::get('/pembelian', [ToserdaController::class, 'pembelian'])->name('toserda.pembelian');
        Route::get('/biaya-usaha', [ToserdaController::class, 'biayaUsaha'])->name('toserda.biaya-usaha');
        Route::get('/lain-lain', [ToserdaController::class, 'lainLain'])->name('toserda.lain-lain');
        Route::post('/penjualan', [ToserdaController::class, 'storePenjualan'])->name('toserda.store.penjualan');
        Route::post('/pembelian', [ToserdaController::class, 'storePembelian'])->name('toserda.store.pembelian');
        Route::post('/biaya-usaha', [ToserdaController::class, 'storeBiayaUsaha'])->name('toserda.store.biaya-usaha');
        Route::post('/upload', [ToserdaController::class, 'storeUploadToserda'])->name('toserda.upload.store');
        Route::post('/billing/process', [ToserdaController::class, 'processMonthlyBilling'])->name('toserda.billing.process');
        Route::get('/template/download', [ToserdaController::class, 'downloadTemplate'])->name('toserda.template.download');
    });

    // Angkutan Routes
    Route::prefix('angkutan')->group(function () {
        Route::get('/pemasukan', [AngkutanController::class, 'pemasukan'])->name('angkutan.pemasukan');
        Route::get('/pengeluaran', [AngkutanController::class, 'pengeluaran'])->name('angkutan.pengeluaran');
        Route::post('/pemasukan', [AngkutanController::class, 'storePemasukan'])->name('angkutan.store.pemasukan');
        Route::post('/pengeluaran', [AngkutanController::class, 'storePengeluaran'])->name('angkutan.store.pengeluaran');
        Route::get('/transaksi', [AngkutanController::class, 'getTransaksi'])->name('angkutan.transaksi');
    });
    Route::get('/simpanan/setoran-tunai', [SimpananController::class, 'setoranTunai'])->name('simpanan.setoran');
    Route::post('/simpanan/setoran-tunai/store', [SimpananController::class, 'storeSetoran'])->name('simpanan.setoran.store');
    Route::get('/simpanan/penarikan-tunai', [SimpananController::class, 'penarikanTunai'])->name('simpanan.penarikan');
    Route::post('/simpanan/penarikan-tunai/store', [SimpananController::class, 'storePenarikan'])->name('simpanan.penarikan.store');
    Route::get('/simpanan/get-anggota/{noKtp}', [SimpananController::class, 'getAnggotaByKtp'])->name('simpanan.get-anggota');
    Route::get('/simpanan/pengajuan-penarikan', [DtaPengajuanPenarikanController::class, 'index'])->name('simpanan.pengajuan_penarikan');


    // Simpanan Routes
    Route::prefix('simpanan')->group(function () {
        Route::get('/setoran', [SimpananController::class, 'setoranTunai'])->name('simpanan.setoran');
        Route::post('/setoran', [SimpananController::class, 'storeSetoran'])->name('simpanan.store.setoran');
        Route::get('/penarikan', [SimpananController::class, 'penarikanTunai'])->name('simpanan.penarikan');
        Route::post('/penarikan', [SimpananController::class, 'storePenarikan'])->name('simpanan.store.penarikan');
        Route::get('/pengajuan-penarikan', [SimpananController::class, 'pengajuanPenarikan'])->name('simpanan.pengajuan_penarikan');
        Route::get('/setoran-upload', [SimpananController::class, 'setoranUpload'])->name('simpanan.upload');
        Route::post('/setoran-upload', [SimpananController::class, 'uploadSetoran'])->name('simpanan.upload.store');
        Route::post('/setoran-upload/process', [SimpananController::class, 'prosesSetoran'])->name('simpanan.upload.process');
        Route::get('/tagihan', [SimpananController::class, 'tagihan'])->name('simpanan.tagihan');
        Route::post('/tagihan', [SimpananController::class, 'storeTagihan'])->name('simpanan.tagihan.store');
        Route::get('/anggota/{noKtp}', [SimpananController::class, 'getAnggotaByKtp']);
    });
});