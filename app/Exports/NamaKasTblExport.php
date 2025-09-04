<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class NamaKasTblExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
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
            'ID',
            'Nama Kas',
            'Status Aktif',
            'Tampil Simpanan',
            'Tampil Penarikan',
            'Tampil Pinjaman',
            'Tampil Bayar',
            'Tampil Pemasukan',
            'Tampil Pengeluaran',
            'Tampil Transfer',
            'Total Fitur Aktif',
            'Kategori Kas',
            'Dibuat',
            'Diperbarui'
        ];
    }

    public function map($kas): array
    {
        return [
            $kas->id,
            $kas->nama,
            $kas->status_aktif_text,
            $kas->tampil_simpanan_text,
            $kas->tampil_penarikan_text,
            $kas->tampil_pinjaman_text,
            $kas->tampil_bayar_text,
            $kas->tampil_pemasukan_text,
            $kas->tampil_pengeluaran_text,
            $kas->tampil_transfer_text,
            $kas->total_fitur_aktif,
            $kas->kategori_kas,
            $kas->created_at ? $kas->created_at->format('d/m/Y H:i') : '-',
            $kas->updated_at ? $kas->updated_at->format('d/m/Y H:i') : '-'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB']
                ]
            ]
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 25,
            'C' => 15,
            'D' => 18,
            'E' => 18,
            'F' => 18,
            'G' => 15,
            'H' => 18,
            'I' => 20,
            'J' => 18,
            'K' => 18,
            'L' => 15,
            'M' => 20,
            'N' => 20
        ];
    }
}
