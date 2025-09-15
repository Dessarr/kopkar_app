<?php

/**
 * Test Script untuk Memverifikasi Filter Jenis Akun
 * Jalankan script ini untuk memastikan filter berfungsi dengan baik
 */

require_once 'vendor/autoload.php';

use App\Models\jns_akun;

echo "=== TEST FILTER JENIS AKUN ===\n\n";

// Test 1: Cek data yang ada
echo "1. Cek data yang ada di database:\n";
$totalData = jns_akun::count();
echo "Total data: {$totalData}\n";

$aktifData = jns_akun::where('aktif', 'Y')->count();
echo "Data aktif: {$aktifData}\n";

$tidakAktifData = jns_akun::where('aktif', 'N')->count();
echo "Data tidak aktif: {$tidakAktifData}\n\n";

// Test 2: Cek tipe akun yang ada
echo "2. Cek tipe akun yang tersedia:\n";
$accountTypes = jns_akun::select('akun')
    ->whereNotNull('akun')
    ->distinct()
    ->orderBy('akun')
    ->pluck('akun');
    
foreach ($accountTypes as $type) {
    echo "- {$type}\n";
}
echo "\n";

// Test 3: Test search functionality
echo "3. Test search functionality:\n";
$searchResults = jns_akun::search('A10')->get();
echo "Hasil pencarian 'A10': " . $searchResults->count() . " data\n";

if ($searchResults->count() > 0) {
    foreach ($searchResults as $result) {
        echo "  - {$result->kd_aktiva}: {$result->jns_trans} ({$result->akun})\n";
    }
}
echo "\n";

// Test 4: Test status filter
echo "4. Test status filter:\n";
$aktifResults = jns_akun::where('aktif', 'Y')->get();
echo "Filter aktif (Y): " . $aktifResults->count() . " data\n";

$tidakAktifResults = jns_akun::where('aktif', 'N')->get();
echo "Filter tidak aktif (N): " . $tidakAktifResults->count() . " data\n\n";

// Test 5: Test account type filter
echo "5. Test account type filter:\n";
if ($accountTypes->count() > 0) {
    $firstType = $accountTypes->first();
    $typeResults = jns_akun::where('akun', $firstType)->get();
    echo "Filter tipe '{$firstType}': " . $typeResults->count() . " data\n";
}
echo "\n";

// Test 6: Test kombinasi filter
echo "6. Test kombinasi filter:\n";
$combinedResults = jns_akun::query()
    ->where('aktif', 'Y')
    ->whereNotNull('akun')
    ->get();
echo "Kombinasi aktif + ada tipe akun: " . $combinedResults->count() . " data\n\n";

echo "=== TEST SELESAI ===\n";
echo "Jika tidak ada error, filter seharusnya berfungsi dengan baik.\n";
