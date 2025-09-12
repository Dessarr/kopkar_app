<?php

namespace App\Exports;

use App\Models\jns_simpan;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class JnsSimpanExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $search;
    protected $type;
    protected $status;

    public function __construct($search = null, $type = null, $status = null)
    {
        $this->search = $search;
        $this->type = $type;
        $this->status = $status;
    }

    public function collection()
    {
        $query = jns_simpan::query();

        if ($this->search) {
            $query->where(function($q) {
                $q->where('jns_simpan', 'like', '%' . $this->search . '%')
                  ->orWhere('id', 'like', '%' . $this->search . '%');
            });
        }

        if ($this->type) {
            $query->where('jns_simpan', 'like', '%' . $this->type . '%');
        }

        if ($this->status) {
            $query->where('tampil', $this->status);
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
            1 => ['font' => ['bold' => true]],
        ];
    }
}
