<?php

namespace App\Exports;

use App\Models\TblUser;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TblUserExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $search;
    protected $status;
    protected $level;
    protected $cabang;

    public function __construct($search = null, $status = null, $level = null, $cabang = null)
    {
        $this->search = $search;
        $this->status = $status;
        $this->level = $level;
        $this->cabang = $cabang;
    }

    public function collection()
    {
        $query = TblUser::with('cabang');

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->status) {
            $query->where('aktif', $this->status);
        }

        if ($this->level) {
            $query->where('level', $this->level);
        }

        if ($this->cabang) {
            $query->where('id_cabang', $this->cabang);
        }

        return $query->ordered()->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Username',
            'Level',
            'Cabang',
            'Status Aktif',
            'Tanggal Dibuat',
            'Tanggal Diupdate',
        ];
    }

    public function map($pengguna): array
    {
        return [
            $pengguna->id,
            $pengguna->u_name,
            $pengguna->level_text,
            $pengguna->cabang ? $pengguna->cabang->nama : '-',
            $pengguna->status_aktif_text,
            $pengguna->created_at ? $pengguna->created_at->format('d/m/Y H:i:s') : '-',
            $pengguna->updated_at ? $pengguna->updated_at->format('d/m/Y H:i:s') : '-',
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
            'A' => 8,
            'B' => 20,
            'C' => 20,
            'D' => 25,
            'E' => 15,
            'F' => 20,
            'G' => 20,
        ];
    }
}