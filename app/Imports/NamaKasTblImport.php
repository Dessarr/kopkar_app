<?php

namespace App\Imports;

use App\Models\NamaKasTbl;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\Importable;

class NamaKasTblImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use Importable, SkipsErrors;

    public function model(array $row)
    {
        return new NamaKasTbl([
            'nama' => $row['nama_kas'],
            'aktif' => $row['status_aktif'] === 'Aktif' ? 'Y' : 'T',
            'tmpl_simpan' => $row['tampil_simpanan'] === 'Ya' ? 'Y' : 'T',
            'tmpl_penarikan' => $row['tampil_penarikan'] === 'Ya' ? 'Y' : 'T',
            'tmpl_pinjaman' => $row['tampil_pinjaman'] === 'Ya' ? 'Y' : 'T',
            'tmpl_bayar' => $row['tampil_bayar'] === 'Ya' ? 'Y' : 'T',
            'tmpl_pemasukan' => $row['tampil_pemasukan'] === 'Ya' ? 'Y' : 'T',
            'tmpl_pengeluaran' => $row['tampil_pengeluaran'] === 'Ya' ? 'Y' : 'T',
            'tmpl_transfer' => $row['tampil_transfer'] === 'Ya' ? 'Y' : 'T',
        ]);
    }

    public function rules(): array
    {
        return [
            '*.nama_kas' => 'required|string|max:255',
            '*.status_aktif' => 'required|in:Aktif,Tidak Aktif',
            '*.tampil_simpanan' => 'required|in:Ya,Tidak',
            '*.tampil_penarikan' => 'required|in:Ya,Tidak',
            '*.tampil_pinjaman' => 'required|in:Ya,Tidak',
            '*.tampil_bayar' => 'required|in:Ya,Tidak',
            '*.tampil_pemasukan' => 'required|in:Ya,Tidak',
            '*.tampil_pengeluaran' => 'required|in:Ya,Tidak',
            '*.tampil_transfer' => 'required|in:Ya,Tidak',
        ];
    }
}
