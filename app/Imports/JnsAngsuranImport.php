<?php

namespace App\Imports;

use App\Models\jns_angsuran;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class JnsAngsuranImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
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
            'status_aktif' => 'required|in:Aktif,Tidak Aktif',
        ];
    }

    public function batchSize(): int
    {
        return 100;
    }

    public function chunkSize(): int
    {
        return 100;
    }
}