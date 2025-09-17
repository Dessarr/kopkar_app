<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\data_anggota;

class DataAnggotaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Sample data anggota sesuai struktur tabel tbl_anggota
        $anggota = [
            [
                'id' => 1,
                'nama' => 'John Doe',
                'identitas' => '1234567890123456',
                'no_ktp' => '1234567890123456',
                'jk' => 'L',
                'tmp_lahir' => 'Jakarta',
                'tgl_lahir' => '1990-01-01',
                'status' => 'Aktif',
                'agama' => 'Islam',
                'departement' => 'IT',
                'pekerjaan' => 'Programmer',
                'alamat' => 'Jl. Contoh No. 123',
                'kota' => 'Jakarta',
                'notelp' => '081234567890',
                'tgl_daftar' => '2020-01-01',
                'jabatan_id' => 1,
                'aktif' => 'Y',
                'pass_word' => 'password123',
                'file_pic' => 'default.jpg',
                'bank' => 'BCA',
                'nama_pemilik_rekening' => 'John Doe',
                'no_rekening' => '1234567890',
                'id_tagihan' => 'TAG001',
                'simpanan_wajib' => 0.00,
                'simpanan_sukarela' => 0.00,
                'simpanan_khusus_2' => 0.00,
                'jns_trans' => '1',
                'id_cabang' => '001',
                'status_bayar' => 'Belum Lunas',
            ],
            [
                'id' => 2,
                'nama' => 'Jane Smith',
                'identitas' => '1234567890123457',
                'no_ktp' => '1234567890123457',
                'jk' => 'P',
                'tmp_lahir' => 'Bandung',
                'tgl_lahir' => '1992-05-15',
                'status' => 'Aktif',
                'agama' => 'Kristen',
                'departement' => 'HR',
                'pekerjaan' => 'HR Manager',
                'alamat' => 'Jl. Contoh No. 456',
                'kota' => 'Bandung',
                'notelp' => '081234567891',
                'tgl_daftar' => '2020-01-01',
                'jabatan_id' => 2,
                'aktif' => 'Y',
                'pass_word' => 'password123',
                'file_pic' => 'default.jpg',
                'bank' => 'Mandiri',
                'nama_pemilik_rekening' => 'Jane Smith',
                'no_rekening' => '0987654321',
                'id_tagihan' => 'TAG002',
                'simpanan_wajib' => 0.00,
                'simpanan_sukarela' => 0.00,
                'simpanan_khusus_2' => 0.00,
                'jns_trans' => '1',
                'id_cabang' => '001',
                'status_bayar' => 'Belum Lunas',
            ],
            [
                'id' => 3,
                'nama' => 'Ahmad Rahman',
                'identitas' => '1234567890123458',
                'no_ktp' => '1234567890123458',
                'jk' => 'L',
                'tmp_lahir' => 'Surabaya',
                'tgl_lahir' => '1988-12-10',
                'status' => 'Aktif',
                'agama' => 'Islam',
                'departement' => 'Finance',
                'pekerjaan' => 'Accountant',
                'alamat' => 'Jl. Contoh No. 789',
                'kota' => 'Surabaya',
                'notelp' => '081234567892',
                'tgl_daftar' => '2020-01-01',
                'jabatan_id' => 3,
                'aktif' => 'Y',
                'pass_word' => 'password123',
                'file_pic' => 'default.jpg',
                'bank' => 'BNI',
                'nama_pemilik_rekening' => 'Ahmad Rahman',
                'no_rekening' => '1122334455',
                'id_tagihan' => 'TAG003',
                'simpanan_wajib' => 0.00,
                'simpanan_sukarela' => 0.00,
                'simpanan_khusus_2' => 0.00,
                'jns_trans' => '1',
                'id_cabang' => '001',
                'status_bayar' => 'Belum Lunas',
            ],
        ];

        foreach ($anggota as $data) {
            data_anggota::create($data);
        }
    }
}