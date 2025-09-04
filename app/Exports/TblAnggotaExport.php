<?php

namespace App\Exports;

use App\Models\tbl_anggota;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class TblAnggotaExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $search;
    protected $status;
    protected $jk;
    protected $departemen;
    protected $kota;

    public function __construct($search = null, $status = null, $jk = null, $departemen = null, $kota = null)
    {
        $this->search = $search;
        $this->status = $status;
        $this->jk = $jk;
        $this->departemen = $departemen;
        $this->kota = $kota;
    }

    public function collection()
    {
        $query = tbl_anggota::query();

        if ($this->search) {
            $query->search($this->search);
        }

        if ($this->status) {
            $query->where('aktif', $this->status);
        }

        if ($this->jk) {
            $query->where('jk', $this->jk);
        }

        if ($this->departemen) {
            $query->where('departement', 'like', '%' . $this->departemen . '%');
        }

        if ($this->kota) {
            $query->where('kota', 'like', '%' . $this->kota . '%');
        }

        return $query->orderBy('nama')->get();
    }

    public function headings(): array
    {
        return [
            'ID',
            'Nama',
            'Identitas',
            'No KTP',
            'Jenis Kelamin',
            'Tempat Lahir',
            'Tanggal Lahir',
            'Status',
            'Agama',
            'Departemen',
            'Pekerjaan',
            'Alamat',
            'Kota',
            'No Telepon',
            'Tanggal Daftar',
            'Status Aktif',
            'Bank',
            'Nama Pemilik Rekening',
            'No Rekening',
            'Simpanan Wajib',
            'Simpanan Sukarela',
            'Simpanan Khusus 2',
            'ID Cabang',
        ];
    }

    public function map($anggota): array
    {
        return [
            $anggota->id,
            $anggota->nama,
            $anggota->identitas,
            $anggota->no_ktp,
            $anggota->jenis_kelamin_text,
            $anggota->tmp_lahir,
            $anggota->tgl_lahir ? $anggota->tgl_lahir->format('d/m/Y') : '',
            $anggota->status,
            $anggota->agama,
            $anggota->departement,
            $anggota->pekerjaan,
            $anggota->alamat,
            $anggota->kota,
            $anggota->notelp,
            $anggota->tgl_daftar ? $anggota->tgl_daftar->format('d/m/Y') : '',
            $anggota->status_aktif_text,
            $anggota->bank,
            $anggota->nama_pemilik_rekening,
            $anggota->no_rekening,
            $anggota->simpanan_wajib,
            $anggota->simpanan_sukarela,
            $anggota->simpanan_khusus_2,
            $anggota->id_cabang,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
