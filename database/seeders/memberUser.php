<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class memberUser extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('tbl_anggota')->insert([
            'id' => 1,
            'nama' => 'prakerinmember',
            'identitas' => '1234567890',
            'jk' => 'L',
            'tmp_lahir' => 'Jakarta',
            'tgl_lahir' => '2000-01-01',
            'status' => 'Belum Menikah',
            'agama' => 'Islam',
            'departement' => 'IT',
            'pekerjaan' => 'Pelajar',
            'alamat' => 'Jl. Contoh No.1',
            'kota' => 'Jakarta',
            'notelp' => '08123456789',
            'tgl_daftar' => now(),
            'jabatan_id' => 1,
            'aktif' => 'Y',
            'pass_word' => Hash::make('12345678'),
            'file_pic' => null,
            'no_ktp' => '1234567890123456',
            'bank' => 'BCA',
            'nama_pemilik_rekening' => 'prakerinmember',
            'no_rekening' => '1234567890',
            'id_tagihan' => null,
            'simpanan_wajib' => 0,
            'simpanan_sukarela' => 0,
            'simpanan_khusus_2' => 0,
            'id_cabang' => 'CB0001'
        ]);
    }
}