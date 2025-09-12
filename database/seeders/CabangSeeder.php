<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CabangSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $cabangs = [
            [
                'id_cabang' => 'CB0001',
                'nama' => 'Kantor Pusat',
                'alamat' => 'Jl. Raya Pusat No. 1, Jakarta',
                'no_telp' => '021-12345678'
            ],
            [
                'id_cabang' => 'CB0002',
                'nama' => 'Cabang Jakarta Selatan',
                'alamat' => 'Jl. Sudirman No. 100, Jakarta Selatan',
                'no_telp' => '021-87654321'
            ],
            [
                'id_cabang' => 'CB0003',
                'nama' => 'Cabang Jakarta Utara',
                'alamat' => 'Jl. Kelapa Gading No. 50, Jakarta Utara',
                'no_telp' => '021-11111111'
            ],
            [
                'id_cabang' => 'CB0004',
                'nama' => 'Cabang Jakarta Barat',
                'alamat' => 'Jl. Kebon Jeruk No. 25, Jakarta Barat',
                'no_telp' => '021-22222222'
            ],
            [
                'id_cabang' => 'CB0005',
                'nama' => 'Cabang Jakarta Timur',
                'alamat' => 'Jl. Cakung No. 75, Jakarta Timur',
                'no_telp' => '021-33333333'
            ]
        ];

        foreach ($cabangs as $cabang) {
            DB::table('cabang')->updateOrInsert(
                ['id_cabang' => $cabang['id_cabang']],
                $cabang
            );
        }
    }
}
