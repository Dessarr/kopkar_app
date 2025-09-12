<?php

namespace App\Exports;

use App\Models\jns_angsuran;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JnsAngsuranExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $search;
    protected $status_aktif;
    protected $kategori;

    public function __construct($search = null, $status_aktif = null, $kategori = null)
    {
        $this->search = $search;
        $this->status_aktif = $status_aktif;
        $this->kategori = $kategori;
    }

    public function collection()
    {
        $query = jns_angsuran::query();

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->status_aktif) {
            $query->byStatusAktif($this->status_aktif);
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
            'Kategori',
            'Status Aktif',
        ];
    }

    public function map($angsuran): array
    {
        return [
            $angsuran->id,
            $angsuran->ket,
            $angsuran->kategori_angsuran,
            $angsuran->status_aktif_text,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
