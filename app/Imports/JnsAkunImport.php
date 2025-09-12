<?php

namespace App\Imports;

use App\Models\jns_akun;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class JnsAkunImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    public function model(array $row)
    {
        return new jns_akun([
            'kd_aktiva' => $row['kode_aktiva'],
            'jns_trans' => $row['jenis_transaksi'],
            'akun' => $row['akun'],
            'laba_rugi' => $row['laba_rugi'] ?? null,
            'pemasukan' => $this->convertToBoolean($row['pemasukan']),
            'pengeluaran' => $this->convertToBoolean($row['pengeluaran']),
            'aktif' => $this->convertToBoolean($row['status'])
        ]);
    }

    public function rules(): array
    {
        return [
            'kode_aktiva' => 'required|string|max:10',
            'jenis_transaksi' => 'required|string|max:255',
            'akun' => 'required|string|max:50',
            'laba_rugi' => 'nullable|string|max:50',
            'pemasukan' => 'required|string',
            'pengeluaran' => 'required|string',
            'status' => 'required|string'
        ];
    }

    private function convertToBoolean($value)
    {
        if (is_bool($value)) {
            return $value;
        }

        $value = strtolower(trim($value));
        
        if (in_array($value, ['ya', 'yes', '1', 'true', 'aktif'])) {
            return true;
        }
        
        return false;
    }
}
