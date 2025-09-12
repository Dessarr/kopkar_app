<?php

namespace App\Exports;

use App\Models\tbl_mobil;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TblMobilExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $search;
    protected $jenis;
    protected $merek;
    protected $pabrikan;
    protected $warna;
    protected $tahun;
    protected $status_aktif;
    protected $status_stnk;

    public function __construct($search = null, $jenis = null, $merek = null, $pabrikan = null, $warna = null, $tahun = null, $status_aktif = null, $status_stnk = null)
    {
        $this->search = $search;
        $this->jenis = $jenis;
        $this->merek = $merek;
        $this->pabrikan = $pabrikan;
        $this->warna = $warna;
        $this->tahun = $tahun;
        $this->status_aktif = $status_aktif;
        $this->status_stnk = $status_stnk;
    }

    public function collection()
    {
        $query = tbl_mobil::query();

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->jenis) {
            $query->byJenis($this->jenis);
        }

        if ($this->merek) {
            $query->byMerek($this->merek);
        }

        if ($this->pabrikan) {
            $query->byPabrikan($this->pabrikan);
        }

        if ($this->warna) {
            $query->byWarna($this->warna);
        }

        if ($this->tahun) {
            $query->byTahun($this->tahun);
        }

        if ($this->status_aktif) {
            $query->byStatusAktif($this->status_aktif);
        }

        if ($this->status_stnk) {
            $query->byStatusStnk($this->status_stnk);
        }

        return $query->ordered()->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama Mobil',
            'Jenis',
            'Merek',
            'Pabrikan',
            'Warna',
            'Tahun',
            'No Polisi',
            'No Rangka',
            'No Mesin',
            'No BPKB',
            'Tgl Berlaku STNK',
            'File PIC',
            'Status Aktif',
            'Status STNK',
        ];
    }

    public function map($mobil): array
    {
        return [
            $mobil->id,
            $mobil->nama,
            $mobil->jenis,
            $mobil->merek,
            $mobil->pabrikan,
            $mobil->warna,
            $mobil->tahun,
            $mobil->no_polisi,
            $mobil->no_rangka,
            $mobil->no_mesin,
            $mobil->no_bpkb,
            $mobil->tgl_berlaku_stnk,
            $mobil->file_pic,
            $mobil->status_aktif_text,
            $mobil->status_stnk,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
