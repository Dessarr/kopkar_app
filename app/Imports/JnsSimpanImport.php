<?php

namespace App\Imports;

use App\Models\jns_simpan;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class JnsSimpanImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

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
            'jenis_simpanan' => 'required|string|max:30',
            'jumlah_minimum' => 'required|numeric|min:0',
            'status_tampil' => 'required|in:Tampil,Tidak Tampil',
            'urutan' => 'required|integer|min:1|max:99',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'jenis_simpanan.required' => 'Jenis Simpanan harus diisi',
            'jenis_simpanan.max' => 'Jenis Simpanan maksimal 30 karakter',
            'jumlah_minimum.required' => 'Jumlah Minimum harus diisi',
            'jumlah_minimum.numeric' => 'Jumlah Minimum harus berupa angka',
            'jumlah_minimum.min' => 'Jumlah Minimum minimal 0',
            'status_tampil.required' => 'Status Tampil harus diisi',
            'status_tampil.in' => 'Status Tampil harus "Tampil" atau "Tidak Tampil"',
            'urutan.required' => 'Urutan harus diisi',
            'urutan.integer' => 'Urutan harus berupa angka',
            'urutan.min' => 'Urutan minimal 1',
            'urutan.max' => 'Urutan maksimal 99',
        ];
    }
}
