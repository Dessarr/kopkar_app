<?php

namespace App\Imports;

use App\Models\TblTransToserda;
use App\Models\data_anggota;
use App\Models\NamaKasTbl;
use App\Models\DataKas;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\WithBatchInserts;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;
use Maatwebsite\Excel\Validators\Failure;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ToserdaImport implements ToModel, WithHeadingRow, WithValidation, WithBatchInserts, SkipsOnFailure, SkipsEmptyRows, WithCalculatedFormulas
{
    protected $rowCount = 0;
    protected $failures = [];

    public function __construct()
    {
        // Tidak perlu parameter
    }

    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['tgl_transaksi']) && empty($row['no_ktp']) && empty($row['jumlah'])) {
            return null;
        }
        $this->rowCount++;
        Log::info('Processing row ' . $this->rowCount . ':', $row);

        // Validasi manual (untuk error message lebih jelas)
        if (!isset($row['tgl_transaksi']) || empty($row['tgl_transaksi'])) {
            $this->failures[] = new Failure(
                $this->rowCount,
                'tgl_transaksi',
                ['Kolom tgl_transaksi harus diisi'],
                $row
            );
            return null;
        }
        if (!isset($row['no_ktp']) || empty($row['no_ktp'])) {
            $this->failures[] = new Failure(
                $this->rowCount,
                'no_ktp',
                ['Kolom no_ktp harus diisi'],
                $row
            );
            return null;
        }
        if (!isset($row['jumlah']) || empty($row['jumlah'])) {
            $this->failures[] = new Failure(
                $this->rowCount,
                'jumlah',
                ['Kolom jumlah harus diisi'],
                $row
            );
            return null;
        }
        if (!isset($row['jns_trans']) || empty($row['jns_trans'])) {
            $row['jns_trans'] = '155'; // Default toserda
        }

        // Format date
        $tanggal = null;
        try {
            $rawDate = $row['tgl_transaksi'];
            if ($rawDate instanceof \DateTime) {
                $tanggal = Carbon::instance($rawDate);
            } else if (is_numeric($rawDate)) {
                $tanggal = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate));
            } else if (is_string($rawDate)) {
                $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Y/m/d'];
                foreach ($formats as $format) {
                    try {
                        $tanggal = Carbon::createFromFormat($format, trim($rawDate));
                        if ($tanggal) break;
                    } catch (\Exception $e) {
                        continue;
                    }
                }
                if (!$tanggal) {
                    $tanggal = Carbon::parse(trim($rawDate));
                }
            }
            if (!$tanggal) {
                throw new \Exception("Format tanggal tidak valid");
            }
        } catch (\Exception $e) {
            $this->failures[] = new Failure(
                $this->rowCount,
                'tgl_transaksi',
                ['Format tanggal tidak valid: ' . $e->getMessage()],
                $row
            );
            return null;
        }

        return new TblTransToserda([
            'tgl_transaksi' => $tanggal,
            'no_ktp' => $row['no_ktp'],
            'jumlah' => $row['jumlah'],
            'jns_trans' => $row['jns_trans'],
            'dk' => 'D', // default debit
            'user_name' => auth()->check() ? auth()->user()->name : 'system',
        ]);
    }

    public function rules(): array
    {
        return [
            'tgl_transaksi' => 'required',
            'no_ktp' => 'required',
            'jumlah' => 'required|numeric',
            'jns_trans' => 'required',
        ];
    }

    public function customValidationMessages()
    {
        return [
            'tgl_transaksi.required' => 'Kolom tgl_transaksi harus diisi',
            'no_ktp.required' => 'Kolom no_ktp harus diisi',
            'jumlah.required' => 'Kolom jumlah harus diisi',
            'jumlah.numeric' => 'Kolom jumlah harus berupa angka',
            'jns_trans.required' => 'Kolom jns_trans harus diisi',
        ];
    }
    
    /**
     * Get the number of rows imported.
     *
     * @return int
     */
    public function getRowCount(): int
    {
        return $this->rowCount;
    }
    
    /**
     * @return array
     */
    public function getFailures(): array
    {
        return $this->failures;
    }
    
    /**
     * @param Failure[] $failures
     */
    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->failures[] = $failure;
            Log::error('Row ' . $failure->row() . ' validation failed: ' . implode(', ', $failure->errors()));
        }
    }
    
    /**
     * @return int
     */
    public function batchSize(): int
    {
        return 100;
    }
} 