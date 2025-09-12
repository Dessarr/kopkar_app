<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\NamaKasTbl;

class NamaKasTblSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $data = [
            [
                'nama' => 'Kas Utama',
                'aktif' => 'Y',
                'tmpl_simpan' => 'Y',
                'tmpl_penarikan' => 'Y',
                'tmpl_pinjaman' => 'Y',
                'tmpl_bayar' => 'Y',
                'tmpl_pemasukan' => 'Y',
                'tmpl_pengeluaran' => 'Y',
                'tmpl_transfer' => 'Y',
            ],
            [
                'nama' => 'Kas Operasional',
                'aktif' => 'Y',
                'tmpl_simpan' => 'Y',
                'tmpl_penarikan' => 'Y',
                'tmpl_pinjaman' => 'T',
                'tmpl_bayar' => 'Y',
                'tmpl_pemasukan' => 'Y',
                'tmpl_pengeluaran' => 'Y',
                'tmpl_transfer' => 'T',
            ],
            [
                'nama' => 'Kas Simpanan',
                'aktif' => 'Y',
                'tmpl_simpan' => 'Y',
                'tmpl_penarikan' => 'Y',
                'tmpl_pinjaman' => 'T',
                'tmpl_bayar' => 'T',
                'tmpl_pemasukan' => 'T',
                'tmpl_pengeluaran' => 'T',
                'tmpl_transfer' => 'T',
            ],
            [
                'nama' => 'Kas Pinjaman',
                'aktif' => 'Y',
                'tmpl_simpan' => 'T',
                'tmpl_penarikan' => 'T',
                'tmpl_pinjaman' => 'Y',
                'tmpl_bayar' => 'Y',
                'tmpl_pemasukan' => 'T',
                'tmpl_pengeluaran' => 'T',
                'tmpl_transfer' => 'T',
            ],
            [
                'nama' => 'Kas Cadangan',
                'aktif' => 'T',
                'tmpl_simpan' => 'T',
                'tmpl_penarikan' => 'T',
                'tmpl_pinjaman' => 'T',
                'tmpl_bayar' => 'T',
                'tmpl_pemasukan' => 'Y',
                'tmpl_pengeluaran' => 'Y',
                'tmpl_transfer' => 'Y',
            ],
            [
                'nama' => 'Kas Khusus',
                'aktif' => 'Y',
                'tmpl_simpan' => 'Y',
                'tmpl_penarikan' => 'T',
                'tmpl_pinjaman' => 'T',
                'tmpl_bayar' => 'T',
                'tmpl_pemasukan' => 'Y',
                'tmpl_pengeluaran' => 'Y',
                'tmpl_transfer' => 'T',
            ],
        ];

        foreach ($data as $item) {
            NamaKasTbl::create($item);
        }
    }
}