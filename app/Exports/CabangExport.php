<?php

namespace App\Exports;

use App\Models\Cabang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CabangExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    public function collection()
    {
        return Cabang::orderBy('id_cabang')->get();
    }

    public function headings(): array
    {
        return [
            'ID Cabang',
            'Nama Cabang',
            'Alamat',
            'No. Telepon',
        ];
    }

    public function map($cabang): array
    {
        return [
            $cabang->id_cabang,
            $cabang->nama,
            $cabang->alamat,
            $cabang->no_telp,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E8F5E8']
                ]
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 15,
            'B' => 30,
            'C' => 50,
            'D' => 20,
        ];
    }
}
