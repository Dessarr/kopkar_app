<?php

namespace App\Exports;

use App\Models\jns_angsuran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JnsAngsuranExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $search;
    protected $statusAktif;
    protected $kategori;

    public function __construct($search = null, $statusAktif = null, $kategori = null)
    {
        $this->search = $search;
        $this->statusAktif = $statusAktif;
        $this->kategori = $kategori;
    }

    public function collection()
    {
        $query = jns_angsuran::query();

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->statusAktif) {
            $query->byStatusAktif($this->statusAktif);
        }

        if ($this->kategori) {
            $query->byKategori($this->kategori);
        }

        return $query->ordered()->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Jumlah Bulan',
            'Kategori Angsuran',
            'Status Aktif',
        ];
    }

    public function map($angsuran): array
    {
        return [
            $angsuran->id,
            $angsuran->ket_formatted,
            $angsuran->kategori_angsuran,
            $angsuran->status_aktif_text,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E3F2FD']
                ]
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 10,
            'B' => 20,
            'C' => 25,
            'D' => 20,
        ];
    }
}