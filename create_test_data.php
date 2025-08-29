<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Creating Test Data for Member Dashboard ===\n\n";

try {
    // 1. Add test simpanan data
    echo "📊 Adding test simpanan data...\n";
    
    $simpananData = [
        [
            'tgl_transaksi' => '2025-01-15',
            'no_ktp' => '1234567890123456',
            'anggota_id' => 1,
            'jenis_id' => 41, // Simpanan Wajib
            'jumlah' => 50000,
            'keterangan' => 'Setoran simpanan wajib',
            'akun' => 'Setoran',
            'dk' => 'D',
            'kas_id' => 1,
            'update_data' => now(),
            'user_name' => 'admin',
            'nama_penyetor' => 'prakerinmember',
            'no_identitas' => '1234567890123456',
            'alamat' => 'Jl. Contoh No.1',
            'id_cabang' => 1
        ],
        [
            'tgl_transaksi' => '2025-01-20',
            'no_ktp' => '1234567890123456',
            'anggota_id' => 1,
            'jenis_id' => 32, // Simpanan Sukarela
            'jumlah' => 100000,
            'keterangan' => 'Setoran simpanan sukarela',
            'akun' => 'Setoran',
            'dk' => 'D',
            'kas_id' => 1,
            'update_data' => now(),
            'user_name' => 'admin',
            'nama_penyetor' => 'prakerinmember',
            'no_identitas' => '1234567890123456',
            'alamat' => 'Jl. Contoh No.1',
            'id_cabang' => 1
        ],
        [
            'tgl_transaksi' => '2025-02-01',
            'no_ktp' => '1234567890123456',
            'anggota_id' => 1,
            'jenis_id' => 40, // Simpanan Pokok
            'jumlah' => 25000,
            'keterangan' => 'Setoran simpanan pokok',
            'akun' => 'Setoran',
            'dk' => 'D',
            'kas_id' => 1,
            'update_data' => now(),
            'user_name' => 'admin',
            'nama_penyetor' => 'prakerinmember',
            'no_identitas' => '1234567890123456',
            'alamat' => 'Jl. Contoh No.1',
            'id_cabang' => 1
        ]
    ];
    
    foreach ($simpananData as $data) {
        DB::table('tbl_trans_sp')->insert($data);
        echo "   ✅ Added simpanan: " . number_format($data['jumlah'], 0, ',', '.') . " (jenis_id: {$data['jenis_id']})\n";
    }
    
    // 2. Add test pinjaman data
    echo "\n💰 Adding test pinjaman data...\n";
    
    $pinjamanData = [
        [
            'id' => 1,
            'no_ktp' => '1234567890123456',
            'tgl_pinjam' => '2025-01-10',
            'anggota_id' => 1,
            'barang_id' => 0,
            'lama_angsuran' => 12,
            'jumlah_angsuran' => 100000,
            'jumlah' => 1200000,
            'bunga' => 12.00,
            'bunga_rp' => 144000,
            'biaya_adm' => 50000,
            'lunas' => 'Belum',
            'dk' => 'K',
            'kas_id' => 1,
            'jns_trans' => '7',
            'status' => '1',
            'jenis_pinjaman' => '1', // Pinjaman Biasa
            'update_data' => now(),
            'user_name' => 'admin',
            'keterangan' => 'Pinjaman untuk modal usaha',
            'id_cabang' => 1
        ],
        [
            'id' => 2,
            'no_ktp' => '1234567890123456',
            'tgl_pinjam' => '2025-02-15',
            'anggota_id' => 1,
            'barang_id' => 1,
            'lama_angsuran' => 6,
            'jumlah_angsuran' => 50000,
            'jumlah' => 300000,
            'bunga' => 10.00,
            'bunga_rp' => 30000,
            'biaya_adm' => 25000,
            'lunas' => 'Belum',
            'dk' => 'K',
            'kas_id' => 1,
            'jns_trans' => '7',
            'status' => '1',
            'jenis_pinjaman' => '2', // Pinjaman Barang
            'update_data' => now(),
            'user_name' => 'admin',
            'keterangan' => 'Pinjaman barang elektronik',
            'id_cabang' => 1
        ]
    ];
    
    foreach ($pinjamanData as $data) {
        DB::table('tbl_pinjaman_h')->insert($data);
        echo "   ✅ Added pinjaman: " . number_format($data['jumlah'], 0, ',', '.') . " (jenis: {$data['jenis_pinjaman']})\n";
    }
    
    // 3. Add test angsuran data
    echo "\n📅 Adding test angsuran data...\n";
    
    $angsuranData = [
        [
            'tgl_bayar' => '2025-02-10',
            'pinjam_id' => 1,
            'angsuran_ke' => 1,
            'jumlah_bayar' => 100000,
            'bunga' => 12000,
            'denda_rp' => 0,
            'biaya_adm' => 0,
            'terlambat' => 0,
            'ket_bayar' => 'Angsuran ke-1',
            'dk' => 'D',
            'kas_id' => 1,
            'jns_trans' => '8',
            'update_data' => now(),
            'user_name' => 'admin',
            'keterangan' => 'Pembayaran angsuran',
            'id_cabang' => 1
        ],
        [
            'tgl_bayar' => '2025-03-10',
            'pinjam_id' => 1,
            'angsuran_ke' => 2,
            'jumlah_bayar' => 100000,
            'bunga' => 12000,
            'denda_rp' => 0,
            'biaya_adm' => 0,
            'terlambat' => 0,
            'ket_bayar' => 'Angsuran ke-2',
            'dk' => 'D',
            'kas_id' => 1,
            'jns_trans' => '8',
            'update_data' => now(),
            'user_name' => 'admin',
            'keterangan' => 'Pembayaran angsuran',
            'id_cabang' => 1
        ]
    ];
    
    foreach ($angsuranData as $data) {
        DB::table('tbl_pinjaman_d')->insert($data);
        echo "   ✅ Added angsuran: " . number_format($data['jumlah_bayar'], 0, ',', '.') . " (angsuran ke-{$data['angsuran_ke']})\n";
    }
    
    echo "\n🎉 Test data created successfully!\n";
    echo "\n=== Login Credentials ===\n";
    echo "No KTP: 1234567890123456\n";
    echo "Password: 1234567890123456\n";
    echo "\n=== Expected Dashboard Data ===\n";
    echo "💰 Saldo Simpanan:\n";
    echo "   - Simpanan Wajib: 50,000\n";
    echo "   - Simpanan Sukarela: 100,000\n";
    echo "   - Simpanan Pokok: 25,000\n";
    echo "   - Total: 175,000\n";
    echo "\n💳 Tagihan Kredit:\n";
    echo "   - Pinjaman Biasa: 1,200,000\n";
    echo "   - Sisa Pinjaman Biasa: 1,000,000 (sudah bayar 2x)\n";
    echo "   - Pinjaman Barang: 300,000\n";
    echo "   - Sisa Pinjaman Barang: 300,000\n";
    echo "\n📊 Keterangan:\n";
    echo "   - Jumlah Pinjaman: 2\n";
    echo "   - Pinjaman Lunas: 0\n";
    echo "   - Status Pembayaran: Lancar\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
}
