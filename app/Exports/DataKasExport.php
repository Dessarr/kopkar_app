<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DataKasExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data = null)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return $this->data ?? collect();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Kas',
            'Status Aktif',
            'Template Simpanan',
            'Template Penarikan',
            'Template Pinjaman',
            'Template Bayar',
            'Template Pemasukan',
            'Template Pengeluaran',
            'Template Transfer',
            'Total Fitur Aktif',
            'Kategori',
            'Created At',
            'Updated At'
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
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,   // ID
            'B' => 20,  // Nama Kas
            'C' => 15,  // Status Aktif
            'D' => 20,  // Template Simpanan
            'E' => 20,  // Template Penarikan
            'F' => 20,  // Template Pinjaman
            'G' => 15,  // Template Bayar
            'H' => 20,  // Template Pemasukan
            'I' => 20,  // Template Pengeluaran
            'J' => 20,  // Template Transfer
            'K' => 18,  // Total Fitur Aktif
            'L' => 15,  // Kategori
            'M' => 20,  // Created At
            'N' => 20,  // Updated At
        ];
    }
}
