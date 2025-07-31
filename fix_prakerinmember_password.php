<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\data_anggota;
use Illuminate\Support\Facades\Hash;

echo "=== Script Fix Password Prakerinmember ===\n\n";

try {
    // Cari user prakerinmember
    $member = data_anggota::where('no_ktp', '1234567890123456')->first();
    
    if (!$member) {
        echo "❌ User prakerinmember tidak ditemukan!\n";
        echo "No KTP: 1234567890123456\n";
        exit(1);
    }
    
    echo "✅ User ditemukan: {$member->nama} ({$member->no_ktp})\n";
    echo "📊 Status aktif: " . ($member->aktif === 'Y' ? 'Aktif' : 'Tidak Aktif') . "\n\n";
    
    // Password yang akan digunakan
    $password = '1234567890123456';
    
    // Generate hash baru dengan bcrypt
    $newHash = Hash::make($password);
    
    echo "🔐 Password: {$password}\n";
    echo "🔑 Hash baru: {$newHash}\n";
    echo "📏 Panjang hash: " . strlen($newHash) . " karakter\n\n";
    
    // Verifikasi hash
    if (Hash::check($password, $newHash)) {
        echo "✅ Hash verification: BERHASIL\n";
    } else {
        echo "❌ Hash verification: GAGAL\n";
        exit(1);
    }
    
    // Update password di database
    $member->pass_word = $newHash;
    $member->save();
    
    echo "✅ Password berhasil diupdate di database!\n\n";
    
    // Verifikasi ulang dari database
    $updatedMember = data_anggota::where('no_ktp', '1234567890123456')->first();
    
    if (Hash::check($password, $updatedMember->pass_word)) {
        echo "✅ Database verification: BERHASIL\n";
        echo "🎉 User prakerinmember sekarang bisa login dengan password: {$password}\n";
    } else {
        echo "❌ Database verification: GAGAL\n";
        echo "Hash di database: {$updatedMember->pass_word}\n";
    }
    
    echo "\n=== Informasi Login ===\n";
    echo "No KTP: 1234567890123456\n";
    echo "Password: 1234567890123456\n";
    echo "Status: " . ($member->aktif === 'Y' ? 'Aktif' : 'Tidak Aktif') . "\n";
    
    if ($member->aktif !== 'Y') {
        echo "\n⚠️  PERHATIAN: User tidak aktif! Set status aktif = 'Y' jika ingin bisa login.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 