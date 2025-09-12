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
    protected $filters;

    public function __construct($filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        $query = jns_akun::query();

        // Apply filters
        if (!empty($this->filters['search'])) {
            $query->search($this->filters['search']);
        }

        if (!empty($this->filters['status'])) {
            $query->byStatus($this->filters['status']);
        }

        if (!empty($this->filters['jns_trans'])) {
            $query->byJnsTrans($this->filters['jns_trans']);
        }

        if (!empty($this->filters['pemasukan'])) {
            $query->byPemasukan($this->filters['pemasukan']);
        }

        if (!empty($this->filters['pengeluaran'])) {
            $query->byPengeluaran($this->filters['pengeluaran']);
        }

        // Apply sorting
        $sortBy = $this->filters['sort_by'] ?? 'id';
        $sortOrder = $this->filters['sort_order'] ?? 'asc';
        $query->orderBy($sortBy, $sortOrder);

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'No',
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
        static $counter = 0;
        $counter++;

        return [
            $counter,
            $akun->kd_aktiva,
            $akun->jns_trans,
            $akun->akun,
            $akun->laba_rugi ?? '-',
            $akun->pemasukan ? 'Ya' : 'Tidak',
            $akun->pengeluaran ? 'Ya' : 'Tidak',
            $akun->aktif ? 'Aktif' : 'Tidak Aktif'
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
            'A' => 8,   // No
            'B' => 15,  // Kode Aktiva
            'C' => 25,  // Jenis Transaksi
            'D' => 20,  // Akun
            'E' => 15,  // Laba Rugi
            'F' => 12,  // Pemasukan
            'G' => 12,  // Pengeluaran
            'H' => 12,  // Status
        ];
    }
}