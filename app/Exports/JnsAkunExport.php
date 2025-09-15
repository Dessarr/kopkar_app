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
    protected $search;
    protected $status;
    protected $akunType;

    public function __construct($search = null, $status = null, $akunType = null)
    {
        $this->search = $search;
        $this->status = $status;
        $this->akunType = $akunType;
    }

    public function collection()
    {
        $query = jns_akun::query();

        if ($this->search && !empty(trim($this->search))) {
            $query->search(trim($this->search));
        }

        if ($this->status !== null) {
            $statusValue = $this->status == '1' ? 'Y' : 'N';
            $query->where('aktif', $statusValue);
        }

        if ($this->akunType && $this->akunType !== '') {
            $query->where('akun', $this->akunType);
        }

        return $query->orderBy('kd_aktiva')->get();
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
            $akun->pemasukan === 'Y' ? 'Ya' : 'Tidak',
            $akun->pengeluaran === 'Y' ? 'Ya' : 'Tidak',
            $akun->aktif === 'Y' ? 'Aktif' : 'Tidak Aktif'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '14AE5C']
                ]
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
