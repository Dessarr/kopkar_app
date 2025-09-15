<?php

namespace App\Imports;

use App\Models\DataKas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\WithChunkReading;

class DataKasImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, WithChunkReading
{
    public function model(array $row)
    {
        return new DataKas([
            'nama' => $row['nama_kas'] ?? $row['nama'],
            'aktif' => $this->convertToYorT($row['status_aktif'] ?? $row['aktif'] ?? 'Y'),
            'tmpl_simpan' => $this->convertToYorT($row['template_simpanan'] ?? $row['tmpl_simpan'] ?? 'N'),
            'tmpl_penarikan' => $this->convertToYorT($row['template_penarikan'] ?? $row['tmpl_penarikan'] ?? 'N'),
            'tmpl_pinjaman' => $this->convertToYorT($row['template_pinjaman'] ?? $row['tmpl_pinjaman'] ?? 'N'),
            'tmpl_bayar' => $this->convertToYorT($row['template_bayar'] ?? $row['tmpl_bayar'] ?? 'N'),
            'tmpl_pemasukan' => $this->convertToYorT($row['template_pemasukan'] ?? $row['tmpl_pemasukan'] ?? 'N'),
            'tmpl_pengeluaran' => $this->convertToYorT($row['template_pengeluaran'] ?? $row['tmpl_pengeluaran'] ?? 'N'),
            'tmpl_transfer' => $this->convertToYorT($row['template_transfer'] ?? $row['tmpl_transfer'] ?? 'N'),
        ]);
    }

    public function rules(): array
    {
        return [
            '*.nama_kas' => 'required|string|max:255',
            '*.status_aktif' => 'nullable|string|in:Y,T,Aktif,Tidak Aktif,aktif,tidak_aktif',
            '*.template_simpanan' => 'nullable|string|in:Y,T,Ya,Tidak,ya,tidak',
            '*.template_penarikan' => 'nullable|string|in:Y,T,Ya,Tidak,ya,tidak',
            '*.template_pinjaman' => 'nullable|string|in:Y,T,Ya,Tidak,ya,tidak',
            '*.template_bayar' => 'nullable|string|in:Y,T,Ya,Tidak,ya,tidak',
            '*.template_pemasukan' => 'nullable|string|in:Y,T,Ya,Tidak,ya,tidak',
            '*.template_pengeluaran' => 'nullable|string|in:Y,T,Ya,Tidak,ya,tidak',
            '*.template_transfer' => 'nullable|string|in:Y,T,Ya,Tidak,ya,tidak',
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

    private function convertToYorT($value)
    {
        if (is_null($value)) {
            return 'N';
        }

        $value = strtolower(trim($value));
        
        if (in_array($value, ['y', 'ya', 'yes', 'aktif', '1', 'true'])) {
            return 'Y';
        }
        
        if (in_array($value, ['t', 'tidak', 'no', 'tidak aktif', '0', 'false'])) {
            return 'T';
        }
        
        return 'N';
    }
}
