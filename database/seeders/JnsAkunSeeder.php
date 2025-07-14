<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\jns_akun;

class JnsAkunSeeder extends Seeder
{
    public function run()
    {
        // Check if Toserda account type already exists
        $existingToserda = jns_akun::where('akun', 'like', '%Toserda%')->first();
        
        if (!$existingToserda) {
            // Create Toserda account type
            jns_akun::create([
                'kd_aktiva' => 'TSR', // Toserda short code
                'jns_trans' => 'Toserda', // Transaction type
                'akun' => 'Pendapatan Toserda', // Account description
                'aktif' => 'Y', // Active status
                'created_at' => now(),
                'updated_at' => now()
            ]);
        }
    }
} 