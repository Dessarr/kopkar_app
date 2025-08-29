<?php
/**
 * Test File untuk Filter Pengajuan Penarikan
 * 
 * File ini digunakan untuk menguji apakah filter pada sistem pengajuan penarikan
 * berfungsi dengan baik. Jalankan file ini untuk memverifikasi:
 * 
 * 1. Filter berdasarkan status
 * 2. Filter berdasarkan jenis simpanan
 * 3. Filter berdasarkan rentang tanggal
 * 4. Pencarian berdasarkan nama/ID/KTP
 * 5. Pagination dengan filter
 */

// Test URL untuk filter
$baseUrl = 'http://127.0.0.1:8000/pengajuan-penarikan';

echo "=== TEST FILTER PENGAJUAN PENARIKAN ===\n\n";

// Test 1: Filter Status
echo "1. Testing Filter Status:\n";
$statusTests = [
    '0' => 'Menunggu Konfirmasi',
    '1' => 'Disetujui', 
    '2' => 'Ditolak',
    '3' => 'Terlaksana',
    '4' => 'Batal'
];

foreach ($statusTests as $status => $label) {
    $url = $baseUrl . "?status={$status}";
    echo "   - Status {$status} ({$label}): {$url}\n";
}

// Test 2: Filter Jenis Simpanan
echo "\n2. Testing Filter Jenis Simpanan:\n";
$jenisTests = [
    '1' => 'Simpanan Wajib',
    '2' => 'Simpanan Sukarela',
    '3' => 'Simpanan Khusus'
];

foreach ($jenisTests as $jenis => $label) {
    $url = $baseUrl . "?jenis_simpanan={$jenis}";
    echo "   - Jenis {$jenis} ({$label}): {$url}\n";
}

// Test 3: Filter Tanggal
echo "\n3. Testing Filter Tanggal:\n";
$dateTests = [
    'today' => ['date_from' => date('Y-m-d'), 'date_to' => date('Y-m-d')],
    'this_month' => ['date_from' => date('Y-m-01'), 'date_to' => date('Y-m-t')],
    'this_year' => ['date_from' => date('Y-01-01'), 'date_to' => date('Y-12-31')],
    'last_month' => ['date_from' => date('Y-m-01', strtotime('-1 month')), 'date_to' => date('Y-m-t', strtotime('-1 month'))]
];

foreach ($dateTests as $period => $dates) {
    $url = $baseUrl . "?date_from={$dates['date_from']}&date_to={$dates['date_to']}";
    echo "   - {$period}: {$url}\n";
}

// Test 4: Pencarian
echo "\n4. Testing Pencarian:\n";
$searchTests = [
    'nama' => 'JAJANG PERMANA',
    'ajuan_id' => 'S.21.08.001',
    'ktp' => '1234567890123456'
];

foreach ($searchTests as $type => $value) {
    $url = $baseUrl . "?search=" . urlencode($value);
    echo "   - {$type}: {$url}\n";
}

// Test 5: Kombinasi Filter
echo "\n5. Testing Kombinasi Filter:\n";
$combinedTests = [
    'status_pending_this_month' => "?status=0&date_from=" . date('Y-m-01') . "&date_to=" . date('Y-m-t'),
    'status_approved_simpanan_wajib' => "?status=1&jenis_simpanan=1",
    'search_with_status' => "?search=JAJANG&status=3",
    'full_filter' => "?status=0&jenis_simpanan=1&date_from=" . date('Y-m-01') . "&date_to=" . date('Y-m-t') . "&search=test"
];

foreach ($combinedTests as $name => $params) {
    $url = $baseUrl . $params;
    echo "   - {$name}: {$url}\n";
}

// Test 6: Pagination dengan Filter
echo "\n6. Testing Pagination dengan Filter:\n";
$paginationTests = [
    'page_2_status_pending' => "?status=0&page=2",
    'page_3_search' => "?search=test&page=3",
    'page_1_date_filter' => "?date_from=" . date('Y-m-01') . "&date_to=" . date('Y-m-t') . "&page=1"
];

foreach ($paginationTests as $name => $params) {
    $url = $baseUrl . $params;
    echo "   - {$name}: {$url}\n";
}

echo "\n=== INSTRUKSI TESTING ===\n";
echo "1. Buka browser dan akses URL di atas satu per satu\n";
echo "2. Verifikasi bahwa filter berfungsi dengan benar\n";
echo "3. Periksa apakah data yang ditampilkan sesuai dengan filter\n";
echo "4. Test pagination dengan filter aktif\n";
echo "5. Test kombinasi multiple filter\n";
echo "6. Test reset filter\n";

echo "\n=== EXPECTED BEHAVIOR ===\n";
echo "- Filter status: Menampilkan data sesuai status yang dipilih\n";
echo "- Filter jenis: Menampilkan data sesuai jenis simpanan\n";
echo "- Filter tanggal: Menampilkan data dalam rentang tanggal\n";
echo "- Pencarian: Menampilkan data yang mengandung kata kunci\n";
echo "- Pagination: Tetap mempertahankan filter saat pindah halaman\n";
echo "- Reset: Menghapus semua filter dan menampilkan semua data\n";

echo "\n=== TROUBLESHOOTING ===\n";
echo "Jika filter tidak berfungsi:\n";
echo "1. Periksa console browser untuk error JavaScript\n";
echo "2. Periksa network tab untuk request yang gagal\n";
echo "3. Periksa log Laravel untuk error server\n";
echo "4. Pastikan database memiliki data untuk testing\n";
echo "5. Periksa apakah route dan controller berfungsi\n";
