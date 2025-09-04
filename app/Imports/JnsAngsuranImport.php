<?php

namespace App\Imports;

use App\Models\jns_angsuran;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class JnsAngsuranImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    public function model(array $row)
    {
        return new jns_angsuran([
            'ket' => $row['jumlah_bulan'],
            'aktif' => $row['status_aktif'] === 'Aktif' ? 'Y' : 'T',
        ]);
    }

    public function rules(): array
    {
        return [
            'jumlah_bulan' => 'required|integer|min:1|max:120',
            'status_aktif' => 'required|in:Aktif,Nonaktif',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'jumlah_bulan.required' => 'Jumlah Bulan harus diisi',
            'jumlah_bulan.integer' => 'Jumlah Bulan harus berupa angka',
            'jumlah_bulan.min' => 'Jumlah Bulan minimal 1',
            'jumlah_bulan.max' => 'Jumlah Bulan maksimal 120',
            'status_aktif.required' => 'Status Aktif harus diisi',
            'status_aktif.in' => 'Status Aktif harus Aktif atau Nonaktif',
        ];
    }
}
