<?php

namespace App\Exports;

use App\Models\tbl_mobil;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TblMobilExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths
{
    protected $search;
    protected $jenis;
    protected $merek;
    protected $pabrikan;
    protected $warna;
    protected $tahun;
    protected $statusAktif;
    protected $statusStnk;

    public function __construct($search = null, $jenis = null, $merek = null, $pabrikan = null, $warna = null, $tahun = null, $statusAktif = null, $statusStnk = null)
    {
        $this->search = $search;
        $this->jenis = $jenis;
        $this->merek = $merek;
        $this->pabrikan = $pabrikan;
        $this->warna = $warna;
        $this->tahun = $tahun;
        $this->statusAktif = $statusAktif;
        $this->statusStnk = $statusStnk;
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

        if ($this->statusAktif) {
            $query->byStatusAktif($this->statusAktif);
        }

        if ($this->statusStnk) {
            $query->byStatusStnk($this->statusStnk);
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
            'Status Aktif',
            'Status STNK',
        ];
    }

    public function map($mobil): array
    {
        return [
            $mobil->id,
            $mobil->nama,
            $mobil->jenis ?? '-',
            $mobil->merek ?? '-',
            $mobil->pabrikan ?? '-',
            $mobil->warna ?? '-',
            $mobil->tahun_formatted,
            $mobil->no_polisi ?? '-',
            $mobil->no_rangka ?? '-',
            $mobil->no_mesin ?? '-',
            $mobil->no_bpkb ?? '-',
            $mobil->tgl_berlaku_stnk_formatted,
            $mobil->status_aktif_text,
            $mobil->status_stnk,
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
            'B' => 25,
            'C' => 15,
            'D' => 15,
            'E' => 15,
            'F' => 12,
            'G' => 8,
            'H' => 15,
            'I' => 20,
            'J' => 20,
            'K' => 20,
            'L' => 18,
            'M' => 15,
            'N' => 15,
        ];
    }
}