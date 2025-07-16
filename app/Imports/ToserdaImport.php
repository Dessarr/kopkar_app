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
    protected $kasId;
    protected $bulan;
    protected $tahun;
    protected $rowCount = 0;
    protected $failures = [];

    /**
     * Create a new import instance.
     *
     * @param int $kasId
     * @param string $bulan
     * @param string $tahun
     * @return void
     */
    public function __construct($bulan, $tahun, $kasId)
    {
        $this->kasId = $kasId;
        $this->bulan = $bulan;
        $this->tahun = $tahun;
    }

    /**
     * @param array $row
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function model(array $row)
    {
        // Skip empty rows
        if (empty($row['tgl_transaksi']) && empty($row['no_ktp']) && empty($row['jumlah'])) {
            return null;
        }
        
        $this->rowCount++;
        
        // Debug the row data
        Log::info('Processing row ' . $this->rowCount . ':', $row);
        
        // Check if required fields exist
        if (!isset($row['tgl_transaksi']) || empty($row['tgl_transaksi'])) {
            Log::error('Missing tanggal in row ' . $this->rowCount);
            $this->failures[] = new Failure(
                $this->rowCount,
                'tgl_transaksi',
                ['Kolom tanggal harus diisi'],
                $row
            );
            return null;
        }
        
        if (!isset($row['no_ktp']) || empty($row['no_ktp'])) {
            Log::error('Missing no_ktp in row ' . $this->rowCount);
            $this->failures[] = new Failure(
                $this->rowCount,
                'no_ktp',
                ['Kolom no_ktp harus diisi'],
                $row
            );
            return null;
        }
        
        if (!isset($row['jumlah']) || empty($row['jumlah'])) {
            Log::error('Missing jumlah in row ' . $this->rowCount);
            $this->failures[] = new Failure(
                $this->rowCount,
                'jumlah',
                ['Kolom jumlah harus diisi'],
                $row
            );
            return null;
        }
        
        // Check if anggota exists
        $anggota = data_anggota::where('no_ktp', $row['no_ktp'])->first();
        
        if (!$anggota && isset($row['nama'])) {
            // Create new anggota if not exists but name is provided
            $anggota = data_anggota::create([
                'no_ktp' => $row['no_ktp'],
                'nama' => $row['nama'],
                'status' => 'aktif',
            ]);
        }
        
        // Format date - handle various formats from Excel
        $tanggal = null;
        try {
            $rawDate = $row['tgl_transaksi'];
            Log::info('Raw date value: ' . print_r($rawDate, true));
            
            if ($rawDate instanceof \DateTime) {
                $tanggal = Carbon::instance($rawDate);
            } else if (is_numeric($rawDate)) {
                // Excel date format
                $tanggal = Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate));
            } else if (is_string($rawDate)) {
                // Try to parse string date format
                // Handle Excel's date+time format like "2025-05-16        13:00"
                if (preg_match('/^(\d{4}-\d{2}-\d{2})\s+(\d{2}:\d{2})$/', trim($rawDate), $matches)) {
                    $dateStr = $matches[1];
                    $timeStr = $matches[2];
                    $tanggal = Carbon::createFromFormat('Y-m-d H:i', "$dateStr $timeStr");
                } else {
                    // Try other common formats
                    $formats = ['Y-m-d', 'd/m/Y', 'm/d/Y', 'd-m-Y', 'Y/m/d'];
                    foreach ($formats as $format) {
                        try {
                            $tanggal = Carbon::createFromFormat($format, trim($rawDate));
                            if ($tanggal) break;
                        } catch (\Exception $e) {
                            continue;
                        }
                    }
                    
                    // If still no valid date, try generic parse
                    if (!$tanggal) {
                        $tanggal = Carbon::parse(trim($rawDate));
                    }
                }
            }
            
            if (!$tanggal) {
                throw new \Exception("Tidak dapat memproses format tanggal");
            }
            
            Log::info('Parsed date: ' . $tanggal->toDateTimeString());
            
        } catch (\Exception $e) {
            Log::error('Date parsing error in row ' . $this->rowCount . ': ' . $e->getMessage());
            $this->failures[] = new Failure(
                $this->rowCount,
                'tgl_transaksi',
                ['Format tanggal tidak valid: ' . $e->getMessage()],
                $row
            );
            return null;
        }
        
        // Verify kas_id exists in either table
        $kasExists = DB::table('nama_kas_tbl')->where('id', $this->kasId)->exists() ||
                    DB::table('data_kas')->where('id', $this->kasId)->exists();
                    
        if (!$kasExists) {
            Log::error('Invalid kas_id: ' . $this->kasId);
            $this->failures[] = new Failure(
                $this->rowCount,
                'kas_id',
                ["ID Kas tidak valid: " . $this->kasId],
                $row
            );
            return null;
        }

        return new TblTransToserda([
            'tgl_transaksi' => $tanggal,
            'no_ktp' => $row['no_ktp'],
            'jumlah' => $row['jumlah'],
            'keterangan' => $row['keterangan'] ?? null,
            'dk' => $row['dk'] ?? 'D',
            'kas_id' => $this->kasId,
            'jns_trans' => $row['jns_trans'] ?? '155', // Default to Toserda code
            'user_name' => auth()->check() ? auth()->user()->u_name : 'system',
        ]);
    }

    /**
     * Get the validation rules that apply to the import.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'tgl_transaksi' => 'required',
            'no_ktp' => 'required',
            'jumlah' => 'required|numeric',
            'dk' => 'nullable|in:D,K',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array
     */
    public function customValidationMessages()
    {
        return [
            'tgl_transaksi.required' => 'Kolom tanggal harus diisi',
            'no_ktp.required' => 'Kolom no_ktp harus diisi',
            'jumlah.required' => 'Kolom jumlah harus diisi',
            'jumlah.numeric' => 'Kolom jumlah harus berupa angka',
            'dk.in' => 'Kolom dk harus berisi D atau K',
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