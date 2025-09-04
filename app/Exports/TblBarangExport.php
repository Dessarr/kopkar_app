<?php

namespace App\Exports;

use App\Models\tbl_barang;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TblBarangExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $search;
    protected $type;
    protected $merk;
    protected $cabang;
    protected $status_stok;

    public function __construct($search = null, $type = null, $merk = null, $cabang = null, $status_stok = null)
    {
        $this->search = $search;
        $this->type = $type;
        $this->merk = $merk;
        $this->cabang = $cabang;
        $this->status_stok = $status_stok;
    }

    public function collection()
    {
        $query = tbl_barang::query();

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->type) {
            $query->byType($this->type);
        }

        if ($this->merk) {
            $query->byMerk($this->merk);
        }

        if ($this->cabang) {
            $query->byCabang($this->cabang);
        }

        if ($this->status_stok) {
            $query->byStatusStok($this->status_stok);
        }

        return $query->ordered()->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Barang',
            'Type',
            'Merk',
            'Harga',
            'Jumlah Barang',
            'Keterangan',
            'ID Cabang',
            'Status Stok',
        ];
    }

    public function map($barang): array
    {
        return [
            $barang->id,
            $barang->nm_barang,
            $barang->type,
            $barang->merk,
            $barang->harga,
            $barang->jml_brg,
            $barang->ket,
            $barang->id_cabang,
            $barang->status_stok,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
