<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TblAnggotaExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data;
    }

    public function headings(): array
    {
        return [
            'No',
            'ID Koperasi',
            'Nama Lengkap',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Umur',
            'Status',
            'Agama',
            'Departemen',
            'Pekerjaan',
            'Alamat',
            'Kota',
            'No. Telepon',
            'Tanggal Daftar',
            'Status Aktif',
            'Bank',
            'Nama Pemilik Rekening',
            'No. Rekening',
            'Simpanan Wajib',
            'Simpanan Sukarela',
            'Simpanan Khusus 2',
        ];
    }

    public function map($anggota): array
    {
        static $no = 1;
        
        return [
            $no++,
            $anggota->no_ktp,
            $anggota->nama,
            $anggota->jenis_kelamin_text,
            $anggota->tmp_lahir,
            $anggota->tgl_lahir ? $anggota->tgl_lahir->format('d/m/Y') : '-',
            $anggota->umur ?? '-',
            $anggota->status,
            $anggota->agama,
            $anggota->departement,
            $anggota->pekerjaan,
            $anggota->alamat,
            $anggota->kota,
            $anggota->notelp,
            $anggota->tgl_daftar ? $anggota->tgl_daftar->format('d/m/Y') : '-',
            $anggota->status_aktif_text,
            $anggota->bank,
            $anggota->nama_pemilik_rekening,
            $anggota->no_rekening,
            'Rp ' . number_format($anggota->simpanan_wajib, 0, ',', '.'),
            'Rp ' . number_format($anggota->simpanan_sukarela, 0, ',', '.'),
            'Rp ' . number_format($anggota->simpanan_khusus_2, 0, ',', '.'),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // No
            'B' => 15,  // ID Koperasi
            'C' => 25,  // Nama Lengkap
            'D' => 15,  // Jenis Kelamin
            'E' => 15,  // Tempat Lahir
            'F' => 15,  // Tanggal Lahir
            'G' => 8,   // Umur
            'H' => 15,  // Status
            'I' => 15,  // Agama
            'J' => 20,  // Departemen
            'K' => 20,  // Pekerjaan
            'L' => 30,  // Alamat
            'M' => 15,  // Kota
            'N' => 15,  // No. Telepon
            'O' => 15,  // Tanggal Daftar
            'P' => 15,  // Status Aktif
            'Q' => 15,  // Bank
            'R' => 25,  // Nama Pemilik Rekening
            'S' => 20,  // No. Rekening
            'T' => 20,  // Simpanan Wajib
            'U' => 20,  // Simpanan Sukarela
            'V' => 20,  // Simpanan Khusus 2
        ];
    }
}