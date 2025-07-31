<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\jns_simpan;
use App\Models\TblTransSp;
use Illuminate\Support\Facades\DB;

echo "=== Script Fix Simpanan Pokok (Update nilai 0 menjadi 100.000) ===\n\n";

try {
    // Get simpanan pokok from master data
    $jenisPokok = jns_simpan::where('jns_simpan', 'Simpanan Pokok')->first();
    
    if (!$jenisPokok) {
        echo "❌ Jenis simpanan 'Simpanan Pokok' tidak ditemukan di master data!\n";
        exit(1);
    }
    
    echo "✅ Jenis simpanan pokok: ID = {$jenisPokok->id}, Jumlah = " . number_format($jenisPokok->jumlah, 0, ',', '.') . "\n\n";
    
    // Check current data
    $recordsWithZero = TblTransSp::where('jenis_id', $jenisPokok->id)
        ->where('akun', 'Setoran')
        ->where('dk', 'D')
        ->where('jumlah', 0)
        ->count();
    
    $recordsWithValue = TblTransSp::where('jenis_id', $jenisPokok->id)
        ->where('akun', 'Setoran')
        ->where('dk', 'D')
        ->where('jumlah', '>', 0)
        ->count();
    
    echo "📊 Current data:\n";
    echo "   Records with jumlah = 0: {$recordsWithZero}\n";
    echo "   Records with jumlah > 0: {$recordsWithValue}\n\n";
    
    if ($recordsWithZero == 0) {
        echo "✅ Tidak ada data yang perlu diperbaiki (semua sudah benar)\n";
        exit(0);
    }
    
    // Update records with jumlah = 0
    $updated = TblTransSp::where('jenis_id', $jenisPokok->id)
        ->where('akun', 'Setoran')
        ->where('dk', 'D')
        ->where('jumlah', 0)
        ->update(['jumlah' => $jenisPokok->jumlah]);
    
    echo "✅ Updated {$updated} records from 0 to " . number_format($jenisPokok->jumlah, 0, ',', '.') . "\n\n";
    
    // Verify results
    $recordsWithZeroAfter = TblTransSp::where('jenis_id', $jenisPokok->id)
        ->where('akun', 'Setoran')
        ->where('dk', 'D')
        ->where('jumlah', 0)
        ->count();
    
    $recordsWithValueAfter = TblTransSp::where('jenis_id', $jenisPokok->id)
        ->where('akun', 'Setoran')
        ->where('dk', 'D')
        ->where('jumlah', '>', 0)
        ->count();
    
    $totalPokok = TblTransSp::where('jenis_id', $jenisPokok->id)
        ->where('akun', 'Setoran')
        ->where('dk', 'D')
        ->sum('jumlah');
    
    echo "📊 After update:\n";
    echo "   Records with jumlah = 0: {$recordsWithZeroAfter}\n";
    echo "   Records with jumlah > 0: {$recordsWithValueAfter}\n";
    echo "💰 Total simpanan pokok: " . number_format($totalPokok, 0, ',', '.') . "\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 