<?php

require_once 'vendor/autoload.php';

// Load Laravel
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\data_anggota;
use Illuminate\Support\Facades\Hash;

echo "=== Script Fix All Passwords (Password = No KTP) ===\n\n";

try {
    // Get all active members
    $anggotaAktif = data_anggota::where('aktif', 'Y')->get();
    echo "📊 Total anggota aktif: " . $anggotaAktif->count() . "\n\n";
    
    $updatedCount = 0;
    $skippedCount = 0;
    
    foreach ($anggotaAktif as $anggota) {
        // Password = no_ktp
        $password = $anggota->no_ktp;
        
        // Generate new bcrypt hash
        $newHash = Hash::make($password);
        
        // Verify hash
        if (!Hash::check($password, $newHash)) {
            echo "❌ Hash verification failed for: {$anggota->nama} ({$anggota->no_ktp})\n";
            continue;
        }
        
        // Check if password already updated (starts with $2y$)
        if (str_starts_with($anggota->pass_word, '$2y$')) {
            $skippedCount++;
            echo "⏭️  Skipped (already Laravel bcrypt): {$anggota->nama} ({$anggota->no_ktp})\n";
            continue;
        }
        
        // Update password in database
        $anggota->pass_word = $newHash;
        $anggota->save();
        
        $updatedCount++;
        echo "✅ Updated {$anggota->nama} ({$anggota->no_ktp}): Password = {$password}\n";
    }
    
    echo "\n=== Hasil Update ===\n";
    echo "✅ Updated: {$updatedCount} records\n";
    echo "⏭️  Skipped (already Laravel): {$skippedCount} records\n";
    echo "📊 Total processed: " . ($updatedCount + $skippedCount) . " records\n";
    
    // Verify results
    $totalMembers = data_anggota::where('aktif', 'Y')->count();
    $laravelHashCount = data_anggota::where('aktif', 'Y')
        ->where('pass_word', 'like', '$2y$%')
        ->count();
    
    echo "\n=== Verifikasi Database ===\n";
    echo "📊 Total anggota aktif: {$totalMembers}\n";
    echo "🔐 Password Laravel bcrypt: {$laravelHashCount}\n";
    echo "🔐 Password CodeIgniter: " . ($totalMembers - $laravelHashCount) . "\n";
    
    if ($laravelHashCount == $totalMembers) {
        echo "🎉 SEMUA PASSWORD SUDAH BERHASIL DIUPDATE KE LARAVEL BCRYPT!\n";
    } else {
        echo "⚠️  Masih ada " . ($totalMembers - $laravelHashCount) . " password yang belum diupdate\n";
    }
    
    echo "\n=== Informasi Login ===\n";
    echo "Format login: No KTP = Password\n";
    echo "Contoh:\n";
    echo "- No KTP: 1234567890123456, Password: 1234567890123456\n";
    echo "- No KTP: 9876543210987654, Password: 9876543210987654\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
} 