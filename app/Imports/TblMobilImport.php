<?php

namespace App\Imports;

use App\Models\tbl_mobil;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TblMobilImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new tbl_mobil([
            'nama' => $row['nama_mobil'],
            'jenis' => $row['jenis'] ?? null,
            'merek' => $row['merek'] ?? null,
            'pabrikan' => $row['pabrikan'] ?? null,
            'warna' => $row['warna'] ?? null,
            'tahun' => $row['tahun'] ?? null,
            'no_polisi' => $row['no_polisi'] ?? null,
            'no_rangka' => $row['no_rangka'] ?? null,
            'no_mesin' => $row['no_mesin'] ?? null,
            'no_bpkb' => $row['no_bpkb'] ?? null,
            'tgl_berlaku_stnk' => $row['tgl_berlaku_stnk'] ?? null,
            'file_pic' => $row['file_pic'] ?? null,
            'aktif' => $row['status_aktif'] === 'Aktif' ? 'Y' : 'N',
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_mobil' => 'required|string|max:255',
            'jenis' => 'nullable|string|max:100',
            'merek' => 'nullable|string|max:225',
            'pabrikan' => 'nullable|string|max:100',
            'warna' => 'nullable|string|max:50',
            'tahun' => 'nullable|integer|min:1900|max:' . date('Y'),
            'no_polisi' => 'nullable|string|max:15',
            'no_rangka' => 'nullable|string|max:50',
            'no_mesin' => 'nullable|string|max:50',
            'no_bpkb' => 'nullable|string|max:50',
            'tgl_berlaku_stnk' => 'nullable|date',
            'file_pic' => 'nullable|string|max:100',
            'status_aktif' => 'required|in:Aktif,Nonaktif',
        ];
    }
}