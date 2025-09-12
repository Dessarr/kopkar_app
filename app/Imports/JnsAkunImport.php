<?php

namespace App\Imports;

use App\Models\jns_akun;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;

class JnsAkunImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function model(array $row)
    {
        return new jns_akun([
            'kd_aktiva' => $row['kode_aktiva'],
            'jns_trans' => $row['jenis_transaksi'],
            'akun' => $row['akun'],
            'laba_rugi' => $row['laba_rugi'] ?? null,
            'pemasukan' => $row['pemasukan'] === 'Ya' || $row['pemasukan'] === 1,
            'pengeluaran' => $row['pengeluaran'] === 'Ya' || $row['pengeluaran'] === 1,
            'aktif' => $row['status'] === 'Aktif' || $row['status'] === 1,
        ]);
    }

    public function rules(): array
    {
        return [
            'kode_aktiva' => 'required|string|max:10',
            'jenis_transaksi' => 'required|string|max:50',
            'akun' => 'required|string|max:50',
            'laba_rugi' => 'nullable|string|max:50',
            'pemasukan' => 'required|boolean',
            'pengeluaran' => 'required|boolean',
            'status' => 'required|string|in:Aktif,Tidak Aktif',
        ];
    }
}