<?php

namespace App\Imports;

use App\Models\TblTransToserda;
use App\Models\data_anggota;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Carbon\Carbon;

class ToserdaImport implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;

    private $kas_id;

    public function __construct($kas_id)
    {
        $this->kas_id = $kas_id;
    }

    public function model(array $row)
    {
        // Find or validate anggota
        $anggota = data_anggota::where('no_ktp', $row['no_ktp'])
                              ->orWhere('nama', 'like', '%' . $row['nama'] . '%')
                              ->first();

        if (!$anggota) {
            throw new \Exception("Anggota dengan No KTP {$row['no_ktp']} atau nama {$row['nama']} tidak ditemukan");
        }

        // Parse and validate date
        $tanggal = isset($row['tanggal']) ? Carbon::createFromFormat('Y-m-d', $row['tanggal'])->startOfDay() : now();

        return new TblTransToserda([
            'tgl_transaksi' => $tanggal,
            'no_ktp' => $anggota->no_ktp,
            'anggota_id' => $anggota->id,
            'jumlah' => $row['jumlah'],
            'keterangan' => $row['keterangan'] ?? 'Import Toserda',
            'dk' => strtoupper($row['dk']),
            'kas_id' => $this->kas_id,
            'jns_trans' => $row['jns_trans'] ?? 'Toserda',
            'user_name' => auth()->user()->name
        ]);
    }

    public function rules(): array
    {
        return [
            'no_ktp' => 'required_without:nama',
            'nama' => 'required_without:no_ktp',
            'jumlah' => 'required|numeric|min:0',
            'dk' => 'required|in:D,K,d,k',
            'tanggal' => 'nullable|date_format:Y-m-d',
            'keterangan' => 'nullable|string',
            'jns_trans' => 'nullable|string'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'no_ktp.required_without' => 'No KTP atau Nama harus diisi',
            'nama.required_without' => 'No KTP atau Nama harus diisi',
            'jumlah.required' => 'Jumlah harus diisi',
            'jumlah.numeric' => 'Jumlah harus berupa angka',
            'jumlah.min' => 'Jumlah tidak boleh negatif',
            'dk.required' => 'Kolom D/K harus diisi',
            'dk.in' => 'Kolom D/K harus berisi D atau K',
            'tanggal.date_format' => 'Format tanggal harus YYYY-MM-DD'
        ];
    }
} 