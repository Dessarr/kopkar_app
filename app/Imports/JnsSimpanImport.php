<?php

namespace App\Imports;

use App\Models\jns_simpan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;

class JnsSimpanImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    public function model(array $row)
    {
        return new jns_simpan([
            'jns_simpan' => $row['jenis_simpanan'],
            'jumlah' => $row['jumlah_minimum'],
            'tampil' => $row['status_tampil'] == 'Tampil' ? 'Y' : 'T',
            'urut' => $row['urutan'],
        ]);
    }

    public function rules(): array
    {
        return [
            '*.jenis_simpanan' => 'required|string|max:30',
            '*.jumlah_minimum' => 'required|numeric|min:0',
            '*.status_tampil' => 'required|in:Tampil,Tidak Tampil',
            '*.urutan' => 'required|integer|min:1|max:99',
        ];
    }
}