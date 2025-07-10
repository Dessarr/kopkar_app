<?php

namespace App\Imports;

use App\Models\TblTransToserda;
use App\Models\data_anggota;
use App\Models\jns_akun;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\HeadingRowFormatter;

class ToserdaImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows
{
    protected $kas_id;

    public function __construct($kas_id)
    {
        $this->kas_id = $kas_id;
        // Set heading row formatter agar key tidak diubah
        HeadingRowFormatter::default('none');
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Normalisasi key juga di sini (jaga-jaga)
        $normalized = [];
        foreach ($row as $k => $v) {
            $key = strtolower(trim(preg_replace('/\s+/', '', $k)));
            $normalized[$key] = is_string($v) ? trim($v) : $v;
        }
        $row = $normalized;
        // Debug log jika masih error
        \Log::info('Row Excel Import:', $row);
        // Abaikan baris jika semua kolom penting kosong
        if (
            empty($row['no_ktp']) &&
            empty($row['nama']) &&
            empty($row['jumlah']) &&
            empty($row['tgl_transaksi'])
        ) {
            return null;
        }
        
        // Cari anggota berdasarkan no_ktp atau nama
        $anggota = null;
        
        // Konversi no_ktp jika dalam format ilmiah (1.23457E+15)
        $no_ktp = $row['no_ktp'] ?? null;
        if ($no_ktp && is_numeric($no_ktp)) {
            $no_ktp = number_format($no_ktp, 0, '', '');
        }
        
        if (!empty($no_ktp)) {
            $anggota = data_anggota::where('no_ktp', $no_ktp)->first();
            if (!$anggota) {
                $anggota = data_anggota::where('no_ktp', 'like', '%' . substr($no_ktp, -8) . '%')->first();
            }
        } elseif (!empty($row['nama'])) {
            $anggota = data_anggota::where('nama', 'like', '%' . $row['nama'] . '%')->first();
        }
        if (!$anggota) {
            return null;
        }
        
        // Konversi tanggal - menangani berbagai format
        $tanggal = null;
        if (!empty($row['tgl_transaksi'])) {
            try {
                if (is_numeric($row['tgl_transaksi'])) {
                    $tanggal = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['tgl_transaksi'])->format('Y-m-d H:i:s');
                } else {
                    $tanggal = Carbon::parse($row['tgl_transaksi'])->format('Y-m-d H:i:s');
                }
            } catch (\Exception $e) {
                $tanggal = now()->format('Y-m-d H:i:s');
            }
        } else {
            $tanggal = now()->format('Y-m-d H:i:s');
        }
        
        // Parsing jumlah: ambil angka saja
        $jumlah = $row['jumlah'] ?? null;
        if (!empty($jumlah)) {
            // Hilangkan "Rp", titik, koma, dan spasi
            $jumlah = preg_replace('/[^0-9]/', '', $jumlah);
        }
        
        // Cari jns_trans dari jns_akun jika disediakan
        $jns_trans = null;
        if (!empty($row['jns_tran'])) {
            $jnsAkun = jns_akun::where('jns_trans', $row['jns_tran'])->first();
            if ($jnsAkun) {
                $jns_trans = $jnsAkun->jns_trans;
            } else {
                $jns_trans = $row['jns_tran']; // fallback ke value excel
            }
        } else {
            $jnsAkun = jns_akun::find(155);
            if ($jnsAkun) {
                $jns_trans = $jnsAkun->jns_trans;
            }
        }
        
        return new TblTransToserda([
            'tgl_transaksi' => $tanggal,
            'no_ktp' => $anggota->no_ktp,
            'anggota_id' => $anggota->id,
            'jumlah' => $jumlah,
            'keterangan' => $row['keterangan'] ?? 'Upload Toserda',
            'dk' => $row['dk'] ?? 'D',
            'kas_id' => $this->kas_id,
            'jns_trans' => $jns_trans,
            'user_name' => Auth::user()->name,
            'update_data' => now(),
        ]);
    }

    public function prepareForValidation($row, $index)
    {
        // Normalisasi key: lowercase, trim, hilangkan semua whitespace
        $normalized = [];
        foreach ($row as $k => $v) {
            $key = strtolower(trim(preg_replace('/\s+/', '', $k)));
            $normalized[$key] = is_string($v) ? trim($v) : $v;
        }
        if (
            empty($normalized['no_ktp']) &&
            empty($normalized['nama']) &&
            empty($normalized['jumlah']) &&
            empty($normalized['tgl_transaksi'])
        ) {
            return [];
        }
        return $normalized;
    }

    /**
     * Validasi data dari Excel
     */
    public function rules(): array
    {
        return [
            'jumlah' => 'required|numeric',
            'no_ktp' => 'required_without:nama',
            'nama' => 'required_without:no_ktp',
            'dk' => 'nullable|in:D,K',
        ];
    }

    /**
     * Custom messages untuk validasi
     */
    public function customValidationMessages()
    {
        return [
            'jumlah.required' => 'Kolom  jumlah harus diisi',
            'jumlah.numeric' => 'Kolom jumlah harus berupa angka',
            'no_ktp.required_without' => 'Kolom no_ktp atau nama harus diisi',
            'nama.required_without' => 'Kolom no_ktp atau nama harus diisi',
            'dk.in' => 'Kolom dk harus berisi D atau K',
        ];
    }
} 