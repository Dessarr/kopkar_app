<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\suku_bunga;

class SukuBungaSeeder extends Seeder
{
    public function run()
    {
        $defaultValues = [
            'bunga_biasa' => '12', // 12% untuk pinjaman biasa
            'bunga_barang' => '15', // 15% untuk pinjaman barang
            'biaya_adm' => '1', // 1% biaya admin
        ];

        foreach ($defaultValues as $key => $value) {
            $existing = suku_bunga::where('opsi_key', $key)->first();
            
            if (!$existing) {
                suku_bunga::create([
                    'opsi_key' => $key,
                    'opsi_val' => $value,
                    'id_cabang' => '1', // Default cabang
                ]);
                
                $this->command->info("Created suku bunga: {$key} = {$value}%");
            } else {
                $this->command->info("Suku bunga {$key} already exists");
            }
        }
    }
}
