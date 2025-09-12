<?php

namespace App\Exports;

use App\Models\jns_akun;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JnsAkunExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    public function collection()
    {
        return jns_akun::orderBy('kd_aktiva')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Aktiva',
            'Jenis Transaksi',
            'Akun',
            'Laba Rugi',
            'Pemasukan',
            'Pengeluaran',
            'Status'
        ];
    }

    public function map($akun): array
    {
        return [
            $akun->kd_aktiva,
            $akun->jns_trans,
            $akun->akun,
            $akun->laba_rugi ?? '',
            $akun->pemasukan ? 'Ya' : 'Tidak',
            $akun->pengeluaran ? 'Ya' : 'Tidak',
            $akun->aktif ? 'Aktif' : 'Tidak Aktif'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '14AE5C']
                ],
                'font' => ['color' => ['rgb' => 'FFFFFF']]
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 30,
            'C' => 15,
            'D' => 15,
            'E' => 12,
            'F' => 12,
            'G' => 12
        ];
    }
}
