<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TEST KREDIT VIEW ===\n\n";

try {
    // Test view v_tagihan_kredit_detil
    $result = DB::select('SELECT * FROM v_tagihan_kredit_detil WHERE no_ktp = ?', ['2025090003']);
    
    if (!empty($result)) {
        echo "✅ Data ditemukan: " . count($result) . " records\n\n";
        
        foreach ($result as $row) {
            echo "=== PINJAMAN ID: " . $row->id . " ===\n";
            echo "No KTP: " . $row->no_ktp . "\n";
            echo "Jenis Pinjaman: " . $row->jenis_pinjaman . " (" . ($row->jenis_pinjaman == '1' ? 'Biasa' : ($row->jenis_pinjaman == '2' ? 'Bank' : 'Barang')) . ")\n";
            echo "Status: " . $row->status . "\n";
            echo "Lunas: " . $row->lunas . "\n";
            echo "Total Pinjaman: " . number_format($row->total_pinjaman) . "\n";
            echo "Sisa Pinjaman: " . number_format($row->sisa_pinjaman) . "\n";
            echo "Angsuran Per Bulan: " . number_format($row->angsuran_per_bulan) . "\n";
            echo "Sudah Bayar: " . $row->sudah_bayar . " bulan\n";
            echo "Total Bayar: " . number_format($row->total_bayar) . "\n";
            echo "Tagihan Tak Terbayar: " . number_format($row->tagihan_tak_terbayar) . "\n";
            echo "Tempo Bulan Depan: " . $row->tempo_bulan_depan . "\n";
            echo "Status Pembayaran: " . $row->status_pembayaran . "\n";
            echo "---\n";
        }
    } else {
        echo "❌ Tidak ada data untuk no_ktp: 2025090003\n";
        
        // Cek apakah ada data di view
        $allData = DB::select('SELECT * FROM v_tagihan_kredit_detil LIMIT 5');
        echo "Total data di view: " . count($allData) . " records\n";
        
        if (!empty($allData)) {
            echo "Contoh data:\n";
            $sample = $allData[0];
            echo "- No KTP: " . $sample->no_ktp . "\n";
            echo "- Jenis: " . $sample->jenis_pinjaman . "\n";
            echo "- Total: " . number_format($sample->total_pinjaman) . "\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== DONE ===\n";
