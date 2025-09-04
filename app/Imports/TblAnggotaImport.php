<?php

namespace App\Imports;

use App\Models\tbl_anggota;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Carbon\Carbon;

class TblAnggotaImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    public function model(array $row)
    {
        return new tbl_anggota([
            'nama' => $row['nama'],
            'identitas' => $row['identitas'],
            'no_ktp' => $row['no_ktp'],
            'jk' => $row['jenis_kelamin'] == 'Laki-laki' ? 'L' : 'P',
            'tmp_lahir' => $row['tempat_lahir'],
            'tgl_lahir' => $row['tanggal_lahir'] ? Carbon::createFromFormat('d/m/Y', $row['tanggal_lahir'])->format('Y-m-d') : null,
            'status' => $row['status'],
            'agama' => $row['agama'],
            'departement' => $row['departemen'],
            'pekerjaan' => $row['pekerjaan'],
            'alamat' => $row['alamat'],
            'kota' => $row['kota'],
            'notelp' => $row['no_telepon'],
            'tgl_daftar' => $row['tanggal_daftar'] ? Carbon::createFromFormat('d/m/Y', $row['tanggal_daftar'])->format('Y-m-d') : null,
            'jabatan_id' => $row['jabatan_id'],
            'aktif' => $row['status_aktif'] == 'Aktif' ? 'Y' : 'N',
            'bank' => $row['bank'],
            'nama_pemilik_rekening' => $row['nama_pemilik_rekening'],
            'no_rekening' => $row['no_rekening'],
            'simpanan_wajib' => $row['simpanan_wajib'] ?? 0,
            'simpanan_sukarela' => $row['simpanan_sukarela'] ?? 0,
            'simpanan_khusus_2' => $row['simpanan_khusus_2'] ?? 0,
            'id_cabang' => $row['id_cabang'],
        ]);
    }

    public function rules(): array
    {
        return [
            'nama' => 'required|string|max:255',
            'identitas' => 'required|string|max:255',
            'no_ktp' => 'required|string|max:300|unique:tbl_anggota,no_ktp',
            'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
            'tempat_lahir' => 'nullable|string|max:225',
            'tanggal_lahir' => 'nullable|date_format:d/m/Y',
            'status' => 'nullable|string|max:30',
            'agama' => 'nullable|string|max:30',
            'departemen' => 'nullable|string|max:255',
            'pekerjaan' => 'nullable|string|max:30',
            'alamat' => 'nullable|string',
            'kota' => 'nullable|string|max:255',
            'no_telepon' => 'nullable|string|max:12',
            'tanggal_daftar' => 'nullable|date_format:d/m/Y',
            'jabatan_id' => 'nullable|integer',
            'status_aktif' => 'required|in:Aktif,Tidak Aktif',
            'bank' => 'nullable|string|max:50',
            'nama_pemilik_rekening' => 'nullable|string|max:150',
            'no_rekening' => 'nullable|string|max:50',
            'simpanan_wajib' => 'nullable|numeric|min:0',
            'simpanan_sukarela' => 'nullable|numeric|min:0',
            'simpanan_khusus_2' => 'nullable|numeric|min:0',
            'id_cabang' => 'nullable|string|max:8',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'nama.required' => 'Nama harus diisi',
            'identitas.required' => 'Identitas harus diisi',
            'no_ktp.required' => 'No KTP harus diisi',
            'no_ktp.unique' => 'No KTP sudah terdaftar',
            'jenis_kelamin.required' => 'Jenis Kelamin harus diisi',
            'jenis_kelamin.in' => 'Jenis Kelamin harus "Laki-laki" atau "Perempuan"',
            'status_aktif.required' => 'Status Aktif harus diisi',
            'status_aktif.in' => 'Status Aktif harus "Aktif" atau "Tidak Aktif"',
        ];
    }
}
