<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\data_anggota;
use App\Models\jns_simpan;
use App\Models\TblTransSp;
use Illuminate\Support\Facades\DB;

echo "=== Test Dashboard Member - Simpanan Calculation ===\n\n";

try {
    // Test with member "prakerinmember"
    $member = data_anggota::where('no_ktp', '1234567890123456')->first();
    
    if (!$member) {
        echo "❌ Member 'prakerinmember' tidak ditemukan!\n";
        exit(1);
    }
    
    echo "✅ Member ditemukan: {$member->nama} ({$member->no_ktp})\n\n";
    
    // Get all jenis simpanan from master data
    $jenisSimpanan = jns_simpan::where('tampil', 'Y')->orderBy('urut', 'asc')->get();
    echo "📋 Jenis simpanan dari master data:\n";
    foreach ($jenisSimpanan as $jenis) {
        echo "   - {$jenis->jns_simpan} (ID: {$jenis->id}, Jumlah: " . number_format($jenis->jumlah, 0, ',', '.') . ")\n";
    }
    echo "\n";
    
    // Get simpanan data from tbl_trans_sp
    $simpananData = TblTransSp::where('no_ktp', $member->no_ktp)
        ->where('akun', 'Setoran')
        ->where('dk', 'D')
        ->select('jenis_id', DB::raw('SUM(jumlah) as total'))
        ->groupBy('jenis_id')
        ->pluck('total', 'jenis_id')
        ->toArray();
    
    echo "📊 Data simpanan dari tbl_trans_sp:\n";
    foreach ($simpananData as $jenisId => $total) {
        $jenis = $jenisSimpanan->where('id', $jenisId)->first();
        $nama = $jenis ? $jenis->jns_simpan : "Unknown (ID: {$jenisId})";
        echo "   - {$nama}: " . number_format($total, 0, ',', '.') . "\n";
    }
    echo "\n";
    
    // Calculate total simpanan
    $totalSimpanan = array_sum($simpananData);
    echo "💰 Total simpanan: " . number_format($totalSimpanan, 0, ',', '.') . "\n\n";
    
    // Prepare simpanan data for view (like in controller)
    $simpananList = [];
    foreach ($jenisSimpanan as $jenis) {
        $jumlah = $simpananData[$jenis->id] ?? 0;
        $simpananList[] = [
            'nama' => $jenis->jns_simpan,
            'jumlah' => $jumlah,
            'warna' => getSimpananColor($jenis->jns_simpan)
        ];
    }
    
    echo "🎨 Simpanan yang akan ditampilkan di dashboard:\n";
    foreach ($simpananList as $simpanan) {
        $status = $simpanan['jumlah'] > 0 ? "YA" : "TIDAK";
        echo "   - {$simpanan['nama']}: " . number_format($simpanan['jumlah'], 0, ',', '.') . " (Tampil: {$status})\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

function getSimpananColor($jenisSimpanan)
{
    return match($jenisSimpanan) {
        'Simpanan Wajib' => 'blue',
        'Simpanan Sukarela' => 'red', 
        'Simpanan Khusus 2' => 'yellow',
        'Simpanan Pokok' => 'purple',
        default => 'gray'
    };
} 