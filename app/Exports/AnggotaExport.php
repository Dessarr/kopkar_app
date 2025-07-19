<?php

namespace App\Exports;

use App\Models\data_anggota;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AnggotaExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return data_anggota::orderBy('nama')->get();
    }

    public function headings(): array
    {
        return [
            'ID Koperasi',
            'Nama',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Status',
            'Agama',
            'Departemen',
            'Pekerjaan',
            'Alamat',
            'Kota',
            'No. Telepon',
            'Bank',
            'Nama Pemilik Rekening',
            'No. Rekening',
            'Simpanan Wajib',
            'Simpanan Sukarela',
            'Simpanan Khusus 2',
            'Status Anggota',
            'Tanggal Daftar'
        ];
    }

    public function map($anggota): array
    {
        return [
            $anggota->no_ktp,
            $anggota->nama,
            $anggota->jk == 'L' ? 'Laki-laki' : 'Perempuan',
            $anggota->tmp_lahir,
            $anggota->tgl_lahir,
            $anggota->status,
            $anggota->agama,
            $anggota->departement,
            $anggota->pekerjaan,
            $anggota->alamat,
            $anggota->kota,
            $anggota->notelp,
            $anggota->bank,
            $anggota->nama_pemilik_rekening,
            $anggota->no_rekening,
            number_format($anggota->simpanan_wajib, 0, ',', '.'),
            number_format($anggota->simpanan_sukarela, 0, ',', '.'),
            number_format($anggota->simpanan_khusus_2, 0, ',', '.'),
            $anggota->aktif == 'Y' ? 'Aktif' : 'Tidak Aktif',
            $anggota->tgl_daftar
        ];
    }
} 