<?php

namespace App\Imports;

use App\Models\tbl_barang;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;

class TblBarangImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    public function model(array $row)
    {
        return new tbl_barang([
            'nm_barang' => $row['nama_barang'],
            'type' => $row['type'],
            'merk' => $row['merk'],
            'harga' => $row['harga'],
            'jml_brg' => $row['jumlah_barang'],
            'ket' => $row['keterangan'],
            'id_cabang' => $row['id_cabang'],
        ]);
    }

    public function rules(): array
    {
        return [
            'nama_barang' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'merk' => 'required|string|max:50',
            'harga' => 'required|numeric|min:0',
            'jumlah_barang' => 'required|integer|min:0',
            'keterangan' => 'required|string|max:255',
            'id_cabang' => 'nullable|string|max:8',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama_barang.required' => 'Nama Barang harus diisi',
            'nama_barang.max' => 'Nama Barang maksimal 255 karakter',
            'type.required' => 'Type harus diisi',
            'type.max' => 'Type maksimal 50 karakter',
            'merk.required' => 'Merk harus diisi',
            'merk.max' => 'Merk maksimal 50 karakter',
            'harga.required' => 'Harga harus diisi',
            'harga.numeric' => 'Harga harus berupa angka',
            'harga.min' => 'Harga minimal 0',
            'jumlah_barang.required' => 'Jumlah Barang harus diisi',
            'jumlah_barang.integer' => 'Jumlah Barang harus berupa angka bulat',
            'jumlah_barang.min' => 'Jumlah Barang minimal 0',
            'keterangan.required' => 'Keterangan harus diisi',
            'keterangan.max' => 'Keterangan maksimal 255 karakter',
            'id_cabang.max' => 'ID Cabang maksimal 8 karakter',
        ];
    }
}
