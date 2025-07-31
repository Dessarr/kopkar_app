<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\data_anggota;
use App\Models\jns_simpan;
use App\Models\TblTransSp;
use Illuminate\Support\Facades\DB;

echo "=== Script Update Simpanan Pokok untuk Anggota Lama ===\n\n";

try {
    // Get simpanan pokok from master data
    $jenisPokok = jns_simpan::where('jns_simpan', 'Simpanan Pokok')->first();
    
    if (!$jenisPokok) {
        echo "❌ Jenis simpanan 'Simpanan Pokok' tidak ditemukan di master data!\n";
        exit(1);
    }
    
    echo "✅ Jenis simpanan pokok ditemukan: ID = {$jenisPokok->id}, Jumlah = " . number_format($jenisPokok->jumlah, 0, ',', '.') . "\n\n";
    
    // Get all active members
    $anggotaAktif = data_anggota::where('aktif', 'Y')->get();
    echo "📊 Total anggota aktif: " . $anggotaAktif->count() . "\n\n";
    
    $addedCount = 0;
    $existingCount = 0;
    
    foreach ($anggotaAktif as $anggota) {
        // Check if member already has simpanan pokok
        $existingPokok = TblTransSp::where('no_ktp', $anggota->no_ktp)
            ->where('jenis_id', $jenisPokok->id)
            ->where('akun', 'Setoran')
            ->where('dk', 'D')
            ->first();
        
        if ($existingPokok) {
            $existingCount++;
            continue; // Skip if already exists
        }
        
        // Add simpanan pokok for this member
        TblTransSp::create([
            'tgl_transaksi' => now(),
            'no_ktp' => $anggota->no_ktp,
            'anggota_id' => $anggota->id,
            'jenis_id' => $jenisPokok->id,
            'jumlah' => $jenisPokok->jumlah,
            'keterangan' => 'Setoran Simpanan Pokok - Anggota Lama',
            'akun' => 'Setoran',
            'dk' => 'D',
            'kas_id' => 1,
            'update_data' => now(),
            'user_name' => 'admin',
            'nama_penyetor' => $anggota->nama,
            'no_identitas' => $anggota->no_ktp,
            'alamat' => $anggota->alamat,
            'id_cabang' => $anggota->id_cabang ?? 1
        ]);
        
        $addedCount++;
        echo "✅ Added simpanan pokok for: {$anggota->nama} ({$anggota->no_ktp})\n";
    }
    
    echo "\n=== Hasil Update ===\n";
    echo "✅ Added: {$addedCount} records\n";
    echo "⏭️  Skipped (already exists): {$existingCount} records\n";
    echo "📊 Total processed: " . ($addedCount + $existingCount) . " records\n";
    
    // Verify results
    $totalPokok = TblTransSp::where('jenis_id', $jenisPokok->id)
        ->where('akun', 'Setoran')
        ->where('dk', 'D')
        ->sum('jumlah');
    
    echo "💰 Total simpanan pokok in database: " . number_format($totalPokok, 0, ',', '.') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 