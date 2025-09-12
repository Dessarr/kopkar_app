<?php

namespace App\Exports;

use App\Models\jns_simpan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JnsSimpanExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = jns_simpan::query();

        // Apply filters
        if (isset($this->filters['search']) && !empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('id', 'like', '%' . $search . '%')
                  ->orWhere('jns_simpan', 'like', '%' . $search . '%')
                  ->orWhere('jumlah', 'like', '%' . $search . '%');
            });
        }

        if (isset($this->filters['status']) && $this->filters['status'] !== '') {
            $query->where('tampil', $this->filters['status']);
        }

        if (isset($this->filters['type']) && $this->filters['type'] !== '') {
            $query->where('jns_simpan', 'like', '%' . $this->filters['type'] . '%');
        }

        return $query->orderBy('urut')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Jenis Simpanan',
            'Jumlah Minimum',
            'Status Tampil',
            'Urutan',
        ];
    }

    public function map($simpan): array
    {
        return [
            $simpan->id,
            $simpan->jns_simpan,
            $simpan->jumlah,
            $simpan->tampil == 'Y' ? 'Tampil' : 'Tidak Tampil',
            $simpan->urut,
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
            'A' => 10,  // ID
            'B' => 30,  // Jenis Simpanan
            'C' => 20,  // Jumlah Minimum
            'D' => 15,  // Status Tampil
            'E' => 10,  // Urutan
        ];
    }
}