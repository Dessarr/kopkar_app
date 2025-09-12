<?php

namespace App\Imports;

use App\Models\TblUser;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class TblUserImport implements ToModel, WithHeadingRow, WithValidation
{
    public function model(array $row)
    {
        return new TblUser([
            'u_name' => $row['username'],
            'pass_word' => bcrypt($row['password']),
            'id_cabang' => $row['id_cabang'],
            'aktif' => $row['status_aktif'] === 'Aktif' ? 'Y' : 'N',
            'level' => $row['level'],
        ]);
    }

    public function rules(): array
    {
        return [
            'username' => 'required|string|max:255|unique:tbl_user,u_name',
            'password' => 'required|string|min:6',
            'id_cabang' => 'required|string|exists:cabang,id_cabang',
            'status_aktif' => 'required|in:Aktif,Tidak Aktif',
            'level' => 'required|string|in:admin,pinjaman,simpanan,kas,laporan,supervisor,manager',
        ];
    }
}