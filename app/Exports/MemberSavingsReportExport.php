<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MemberSavingsReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $savingsData;

    public function __construct($savingsData)
    {
        $this->savingsData = $savingsData;
    }

    public function collection()
    {
        return $this->savingsData;
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Jenis Simpanan',
            'Jumlah (Rp)',
            'Status',
            'Keterangan'
        ];
    }

    public function map($saving): array
    {
        return [
            \Carbon\Carbon::parse($saving->tgl_transaksi)->format('d M Y'),
            $saving->jenis_simpanan_text,
            $saving->jumlah,
            $saving->jenis_simpanan_text,
            $saving->keterangan ?: 'Setoran simpanan'
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
            'A' => 15, // Tanggal
            'B' => 20, // Jenis Simpanan
            'C' => 18, // Jumlah
            'D' => 20, // Status
            'E' => 30, // Keterangan
        ];
    }
}