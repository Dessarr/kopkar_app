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
use App\Http\Controllers\BillingToserdaController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\TransaksiKasController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SukuBungaController;
use App\Http\Controllers\SimpananController;
use App\Http\Controllers\ToserdaController;
use App\Http\Controllers\AngkutanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\AnggotaController;
use App\Http\Controllers\LaporanAngkutanKaryawanController;
use App\Http\Controllers\LaporanDataAnggotaController;
use App\Http\Controllers\LaporanTransaksiKasController;
use App\Http\Controllers\LaporanKasAnggotaController;
use App\Http\Controllers\LaporanJatuhTempoController;
use App\Http\Controllers\LaporanKreditMacetController;

// Admin Routes
Route::get('/', [AdminController::class, 'showLoginForm'])->name('admin.login.form');
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

// Member Routes
Route::middleware(['auth:member'])->group(function () {
    // Dashboard & Home
    Route::get('/member/beranda', [MemberController::class, 'beranda'])->name('member.beranda');
    
    // Loan Application Routes
    Route::prefix('member/pengajuan')->group(function () {
        Route::get('/pinjaman', [MemberController::class, 'pengajuanPinjaman'])->name('member.pengajuan.pinjaman');
        Route::get('/pinjaman/tambah', [MemberController::class, 'tambahPengajuanPinjaman'])->name('member.tambah.pengajuan.pinjaman');
        Route::post('/simulasi-angsuran', [MemberController::class, 'hitungSimulasi'])->name('member.simulasi.angsuran');
        Route::post('/pinjaman/store', [MemberController::class, 'storePengajuanPinjaman'])->name('member.pengajuan.pinjaman.store');
        Route::get('/pinjaman/{id}', [MemberController::class, 'showPengajuan'])->name('member.pengajuan.pinjaman.show');
        Route::post('/pinjaman/{id}/cancel', [MemberController::class, 'cancelPengajuan'])->name('member.pengajuan.pinjaman.cancel');
        Route::get('/pinjaman/{id}/cetak', [MemberController::class, 'cetakPengajuan'])->name('member.pengajuan.pinjaman.cetak');
    });
    

    // Penarikan Routes
    Route::get('/penarikan', [MemberController::class, 'pengajuanPenarikan'])->name('member.pengajuan.penarikan');
    Route::get('/penarikan/form', [MemberController::class, 'formPengajuanPenarikan'])->name('member.pengajuan.penarikan.form');
    Route::post('/penarikan/store', [MemberController::class, 'storePengajuanPenarikan'])->name('member.pengajuan.penarikan.store');
    
    // Report Routes
    Route::prefix('member/laporan')->group(function () {
        Route::get('/', [MemberController::class, 'laporan'])->name('member.laporan');
        Route::get('/simpanan', [MemberController::class, 'laporanSimpanan'])->name('member.laporan.simpanan');
        Route::get('/pinjaman', [MemberController::class, 'laporanPinjaman'])->name('member.laporan.pinjaman');
        Route::get('/transaksi', [MemberController::class, 'laporanTransaksi'])->name('member.laporan.transaksi');
    });
    
    // Profile Routes
    Route::get('/member/profile', [MemberController::class, 'profile'])->name('member.profile');
    Route::put('/member/profile', [MemberController::class, 'updateProfile'])->name('member.profile.update');
});

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
    Route::prefix('billing')->group(function () {
        Route::get('/', [BillingController::class, 'index'])->name('billing.index');
        Route::post('/process/{billing_code}', [BillingController::class, 'processPayment'])->name('billing.process');
        Route::get('/export/excel', [BillingController::class, 'exportExcel'])->name('billing.export.excel');
        Route::get('/export/pdf', [BillingController::class, 'exportPdf'])->name('billing.export.pdf');
        
        // New routes for processed billings
        Route::get('/processed', [BillingController::class, 'processed'])->name('billing.processed');
        Route::post('/cancel/{billing_process_id}', [BillingController::class, 'cancelPayment'])->name('billing.cancel');
        Route::get('/processed/export/excel', [BillingController::class, 'exportProcessedExcel'])->name('billing.processed.export.excel');
        Route::get('/processed/export/pdf', [BillingController::class, 'exportProcessedPdf'])->name('billing.processed.export.pdf');

        // Simpanan -> Proses semua ke Billing Utama (tbl_trans_sp_bayar_temp)
        Route::post('/simpanan/process-all', [BillingController::class, 'processAllToMain'])->name('billing.simpanan.process_all');
    });

    // Route Billing Toserda
    Route::prefix('billing-toserda')->group(function () {
        Route::get('/', [BillingToserdaController::class, 'index'])->name('billing.toserda');
        // Proses semua Toserda bulan/tahun terpilih ke Billing Utama
        Route::post('/process-all', [BillingToserdaController::class, 'processAllToMain'])->name('billing.toserda.process_all');
    });

    // Billing Utama (ambil dari tbl_trans_sp_bayar_temp)
    Route::get('/billing-utama', [\App\Http\Controllers\BillingUtamaController::class, 'index'])->name('billing.utama');

    //Route untuk Pinjaman
    Route::get('/pinjaman/data_pengajuan', [DtaPengajuanController::class, 'index'])->name('pinjaman.data_pengajuan');
    Route::post('/pinjaman/data_pengajuan/{id}/approve', [DtaPengajuanController::class, 'approve'])->name('pinjaman.data_pengajuan.approve');
    Route::post('/pinjaman/data_pengajuan/{id}/reject', [DtaPengajuanController::class, 'reject'])->name('pinjaman.data_pengajuan.reject');
    Route::post('/pinjaman/data_pengajuan/{id}/cancel', [DtaPengajuanController::class, 'cancel'])->name('pinjaman.data_pengajuan.cancel');
    Route::delete('/pinjaman/data_pengajuan/{id}', [DtaPengajuanController::class, 'destroy'])->name('pinjaman.data_pengajuan.destroy');
    Route::get('/pinjaman/data_pengajuan/{id}/cetak', [DtaPengajuanController::class, 'cetak'])->name('pinjaman.data_pengajuan.cetak');

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
        Route::get('/anggota/{noKtp}', [SimpananController::class, 'getAnggotaByKtp'])->name('simpanan.get-anggota');
    }); // End of simpanan routes

    // Laporan Routes
    Route::prefix('laporan')->group(function () {
        Route::get('/angkutan-karyawan', [LaporanAngkutanKaryawanController::class, 'index'])->name('laporan.angkutan.karyawan');
        Route::get('/angkutan-karyawan/export/pdf', [LaporanAngkutanKaryawanController::class, 'exportPdf'])->name('laporan.angkutan.karyawan.export.pdf');
        Route::get('/angkutan-karyawan/export/excel', [LaporanAngkutanKaryawanController::class, 'exportExcel'])->name('laporan.angkutan.karyawan.export.excel');
        
        Route::get('/data-anggota', [LaporanDataAnggotaController::class, 'index'])->name('laporan.data.anggota');
        Route::get('/data-anggota/export/pdf', [LaporanDataAnggotaController::class, 'exportPdf'])->name('laporan.data.anggota.export.pdf');
        Route::get('/data-anggota/export/excel', [LaporanDataAnggotaController::class, 'exportExcel'])->name('laporan.data.anggota.export.excel');
        
        Route::get('/transaksi-kas', [LaporanTransaksiKasController::class, 'index'])->name('laporan.transaksi.kas');
        Route::get('/transaksi-kas/export/pdf', [LaporanTransaksiKasController::class, 'exportPdf'])->name('laporan.transaksi.kas.export.pdf');
        Route::get('/transaksi-kas/export/excel', [LaporanTransaksiKasController::class, 'exportExcel'])->name('laporan.transaksi.kas.export.excel');
        
        Route::get('/kas-anggota', [LaporanKasAnggotaController::class, 'index'])->name('laporan.kas.anggota');
        Route::get('/kas-anggota/export/pdf', [LaporanKasAnggotaController::class, 'exportPdf'])->name('laporan.kas.anggota.export.pdf');
        Route::get('/kas-anggota/export/excel', [LaporanKasAnggotaController::class, 'exportExcel'])->name('laporan.kas.anggota.export.excel');
        
        Route::get('/jatuh-tempo', [LaporanJatuhTempoController::class, 'index'])->name('laporan.jatuh.tempo');
        Route::get('/jatuh-tempo/export/pdf', [LaporanJatuhTempoController::class, 'exportPdf'])->name('laporan.jatuh.tempo.export.pdf');
        Route::get('/jatuh-tempo/export/excel', [LaporanJatuhTempoController::class, 'exportExcel'])->name('laporan.jatuh.tempo.export.excel');
        
        Route::get('/kredit-macet', [LaporanKreditMacetController::class, 'index'])->name('laporan.kredit.macet');
        Route::get('/kredit-macet/export/pdf', [LaporanKreditMacetController::class, 'exportPdf'])->name('laporan.kredit.macet.export.pdf');
        Route::get('/kredit-macet/export/excel', [LaporanKreditMacetController::class, 'exportExcel'])->name('laporan.kredit.macet.export.excel');
    });

    // Route Laporan Buku Besar
    Route::prefix('laporan')->group(function () {
        Route::get('/buku-besar', [\App\Http\Controllers\LaporanBukuBesarController::class, 'index'])->name('laporan.buku_besar');
        Route::get('/buku-besar/export/pdf', [\App\Http\Controllers\LaporanBukuBesarController::class, 'exportPdf'])->name('laporan.buku_besar.export.pdf');
        Route::get('/buku-besar/export/excel', [\App\Http\Controllers\LaporanBukuBesarController::class, 'exportExcel'])->name('laporan.buku_besar.export.excel');
    });

    // Route Laporan Neraca Saldo
    Route::prefix('laporan')->group(function () {
        Route::get('/neraca-saldo', [\App\Http\Controllers\LaporanNeracaSaldoController::class, 'index'])->name('laporan.neraca_saldo');
        Route::get('/neraca-saldo/export/pdf', [\App\Http\Controllers\LaporanNeracaSaldoController::class, 'exportPdf'])->name('laporan.neraca_saldo.export.pdf');
        Route::get('/neraca-saldo/export/excel', [\App\Http\Controllers\LaporanNeracaSaldoController::class, 'exportExcel'])->name('laporan.neraca_saldo.export.excel');
    });

    // Route Laporan Kas Simpanan
    Route::prefix('laporan')->group(function () {
        Route::get('/kas-simpanan', [\App\Http\Controllers\LaporanKasSimpananController::class, 'index'])->name('laporan.kas_simpanan');
        Route::get('/kas-simpanan/export/pdf', [\App\Http\Controllers\LaporanKasSimpananController::class, 'exportPdf'])->name('laporan.kas_simpanan.export.pdf');
        Route::get('/kas-simpanan/export/excel', [\App\Http\Controllers\LaporanKasSimpananController::class, 'exportExcel'])->name('laporan.kas_simpanan.export.excel');
    });

    // Route Laporan Kas Pinjaman
    Route::prefix('laporan')->group(function () {
        Route::get('/kas-pinjaman', [\App\Http\Controllers\LaporanKasPinjamanController::class, 'index'])->name('laporan.kas_pinjaman');
        Route::get('/kas-pinjaman/export/pdf', [\App\Http\Controllers\LaporanKasPinjamanController::class, 'exportPdf'])->name('laporan.kas_pinjaman.export.pdf');
        Route::get('/kas-pinjaman/export/excel', [\App\Http\Controllers\LaporanKasPinjamanController::class, 'exportExcel'])->name('laporan.kas_pinjaman.export.excel');
    });

    // Route Laporan Target & Realisasi
    Route::prefix('laporan')->group(function () {
        Route::get('/target-realisasi', [\App\Http\Controllers\LaporanTargetRealisasiController::class, 'index'])->name('laporan.target_realisasi');
        Route::get('/target-realisasi/export/pdf', [\App\Http\Controllers\LaporanTargetRealisasiController::class, 'exportPdf'])->name('laporan.target_realisasi.export.pdf');
        Route::get('/target-realisasi/export/excel', [\App\Http\Controllers\LaporanTargetRealisasiController::class, 'exportExcel'])->name('laporan.target_realisasi.export.excel');
    });

    // Route Laporan Pengeluaran Pinjaman
    Route::prefix('laporan')->group(function () {
        Route::get('/pengeluaran-pinjaman', [\App\Http\Controllers\LaporanPengeluaranPinjamanController::class, 'index'])->name('laporan.pengeluaran_pinjaman');
        Route::get('/pengeluaran-pinjaman/export/pdf', [\App\Http\Controllers\LaporanPengeluaranPinjamanController::class, 'exportPdf'])->name('laporan.pengeluaran_pinjaman.export.pdf');
        Route::get('/pengeluaran-pinjaman/export/excel', [\App\Http\Controllers\LaporanPengeluaranPinjamanController::class, 'exportExcel'])->name('laporan.pengeluaran_pinjaman.export.excel');
    });

    // Route Laporan Angsuran Pinjaman
    Route::prefix('laporan')->group(function () {
        Route::get('/angsuran-pinjaman', [\App\Http\Controllers\LaporanAngsuranPinjamanController::class, 'index'])->name('laporan.angsuran_pinjaman');
        Route::get('/angsuran-pinjaman/export/pdf', [\App\Http\Controllers\LaporanAngsuranPinjamanController::class, 'exportPdf'])->name('laporan.angsuran_pinjaman.export.pdf');
        Route::get('/angsuran-pinjaman/export/excel', [\App\Http\Controllers\LaporanAngsuranPinjamanController::class, 'exportExcel'])->name('laporan.angsuran_pinjaman.export.excel');
    });

    // Route Laporan Rekapitulasi
    Route::prefix('laporan')->group(function () {
        Route::get('/rekapitulasi', [\App\Http\Controllers\LaporanRekapitulasiController::class, 'index'])->name('laporan.rekapitulasi');
        Route::get('/rekapitulasi/export/pdf', [\App\Http\Controllers\LaporanRekapitulasiController::class, 'exportPdf'])->name('laporan.rekapitulasi.export.pdf');
        Route::get('/rekapitulasi/export/excel', [\App\Http\Controllers\LaporanRekapitulasiController::class, 'exportExcel'])->name('laporan.rekapitulasi.export.excel');
    });

    // Route Laporan Saldo Kas
    Route::prefix('laporan')->group(function () {
        Route::get('/saldo-kas', [\App\Http\Controllers\LaporanSaldoKasController::class, 'index'])->name('laporan.saldo_kas');
        Route::get('/saldo-kas/export/pdf', [\App\Http\Controllers\LaporanSaldoKasController::class, 'exportPdf'])->name('laporan.saldo_kas.export.pdf');
        Route::get('/saldo-kas/export/excel', [\App\Http\Controllers\LaporanSaldoKasController::class, 'exportExcel'])->name('laporan.saldo_kas.export.excel');
    });

    // Route Laporan SHU
    Route::prefix('laporan')->group(function () {
        Route::get('/shu', [\App\Http\Controllers\LaporanShuController::class, 'index'])->name('laporan.shu');
        Route::get('/shu/export/pdf', [\App\Http\Controllers\LaporanShuController::class, 'exportPdf'])->name('laporan.shu.export.pdf');
        Route::get('/shu/export/excel', [\App\Http\Controllers\LaporanShuController::class, 'exportExcel'])->name('laporan.shu.export.excel');
    });

    // Route Laporan Toserda
    Route::prefix('laporan')->group(function () {
        Route::get('/toserda', [\App\Http\Controllers\LaporanToserdaController::class, 'index'])->name('laporan.toserda');
        Route::get('/toserda/export/pdf', [\App\Http\Controllers\LaporanToserdaController::class, 'exportPdf'])->name('laporan.toserda.export.pdf');
        Route::get('/toserda/export/excel', [\App\Http\Controllers\LaporanToserdaController::class, 'exportExcel'])->name('laporan.toserda.export.excel');
    });
    
}); // End of admin routes