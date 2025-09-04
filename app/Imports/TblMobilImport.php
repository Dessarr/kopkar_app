<?php

namespace App\Imports;

use App\Models\tbl_mobil;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class TblMobilImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    public function model(array $row)
    {
        return new tbl_mobil([
            'nama' => $row['nama_mobil'],
            'jenis' => $row['jenis'],
            'merek' => $row['merek'],
            'pabrikan' => $row['pabrikan'],
            'warna' => $row['warna'],
            'tahun' => $row['tahun'],
            'no_polisi' => $row['no_polisi'],
            'no_rangka' => $row['no_rangka'],
            'no_mesin' => $row['no_mesin'],
            'no_bpkb' => $row['no_bpkb'],
            'tgl_berlaku_stnk' => $row['tgl_berlaku_stnk'],
            'file_pic' => $row['file_pic'],
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

    public function customValidationMessages()
    {
        return [
            'nama_mobil.required' => 'Nama Mobil harus diisi',
            'nama_mobil.max' => 'Nama Mobil maksimal 255 karakter',
            'jenis.max' => 'Jenis maksimal 100 karakter',
            'merek.max' => 'Merek maksimal 225 karakter',
            'pabrikan.max' => 'Pabrikan maksimal 100 karakter',
            'warna.max' => 'Warna maksimal 50 karakter',
            'tahun.integer' => 'Tahun harus berupa angka',
            'tahun.min' => 'Tahun minimal 1900',
            'tahun.max' => 'Tahun maksimal ' . date('Y'),
            'no_polisi.max' => 'No Polisi maksimal 15 karakter',
            'no_rangka.max' => 'No Rangka maksimal 50 karakter',
            'no_mesin.max' => 'No Mesin maksimal 50 karakter',
            'no_bpkb.max' => 'No BPKB maksimal 50 karakter',
            'tgl_berlaku_stnk.date' => 'Tanggal Berlaku STNK harus berupa tanggal yang valid',
            'file_pic.max' => 'File PIC maksimal 100 karakter',
            'status_aktif.required' => 'Status Aktif harus diisi',
            'status_aktif.in' => 'Status Aktif harus Aktif atau Nonaktif',
        ];
    }
}
