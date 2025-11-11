<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\BillingUploadImport;

class BillingUploadController extends Controller
{
    /**
     * Upload Excel file for billing data
     */
    public function uploadExcel(Request $request)
    {
        try {
            $request->validate([
                'excel_file' => 'required|file|mimes:xlsx,xls',
                'bulan' => 'required|string',
                'tahun' => 'required|string'
            ]);

            $bulan = $request->input('bulan');
            $tahun = $request->input('tahun');
            $file = $request->file('excel_file');

            // Import Excel data
            $import = new BillingUploadImport();
            $data = Excel::toArray($import, $file);

            if (empty($data) || empty($data[0])) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'File Excel kosong atau tidak valid'
                ], 400);
            }

            $excelData = $data[0];
            
            // Debug: Log the raw Excel data
            Log::info('Raw Excel data (first 5 rows):', array_slice($excelData, 0, 5));
            
            // Detect header row and column mapping
            $headerRowIndex = 0;
            $headerMap = [
                'tgl_transaksi' => null,
                'no_ktp' => null,
                'jumlah' => null
            ];
            
            // Check first row to see if it's a header row
            $firstRow = $excelData[0] ?? [];
            $isHeaderRow = false;
            
            // Common header patterns
            $headerPatterns = [
                'tgl_transaksi' => ['tgl_transaksi', 'tanggal', 'tgl', 'date', 'tanggal transaksi'],
                'no_ktp' => ['no_ktp', 'ktp', 'no ktp', 'nik', 'nomor ktp'],
                'jumlah' => ['jumlah', 'nominal', 'total', 'amount', 'nilai']
            ];
            
            // Check if first row contains header-like text
            foreach ($firstRow as $colIndex => $cellValue) {
                $cellValueLower = strtolower(trim($cellValue ?? ''));
                
                foreach ($headerPatterns as $field => $patterns) {
                    foreach ($patterns as $pattern) {
                        if (strpos($cellValueLower, $pattern) !== false) {
                            $headerMap[$field] = $colIndex;
                            $isHeaderRow = true;
                            break;
                        }
                    }
                }
            }
            
            // If header row detected, skip it; otherwise use default mapping
            if ($isHeaderRow && $headerMap['tgl_transaksi'] !== null && 
                $headerMap['no_ktp'] !== null && $headerMap['jumlah'] !== null) {
                $headerRowIndex = 1;
                Log::info('Header row detected, using column mapping:', $headerMap);
            } else {
                // Default mapping: assume first 3 columns are the data we need
                // Column 0 = tgl_transaksi, Column 1 = no_ktp, Column 2 = jumlah
                $headerMap = [
                    'tgl_transaksi' => 0,
                    'no_ktp' => 1,
                    'jumlah' => 2
                ];
                $headerRowIndex = 0;
                Log::info('No header row detected, using default column mapping:', $headerMap);
            }
            
            // Remove header row if detected
            if ($isHeaderRow) {
                $excelData = array_slice($excelData, 1);
            }

            // Process Excel data
            $processedData = [];
            $errors = [];
            $successCount = 0;

            foreach ($excelData as $index => $row) {
                $rowNumber = $index + $headerRowIndex + 1; // +1 for Excel row number (1-indexed)

                try {
                    // Get data from mapped positions
                    $tglTransaksi = isset($row[$headerMap['tgl_transaksi']]) ? trim($row[$headerMap['tgl_transaksi']]) : '';
                    $noKtp = isset($row[$headerMap['no_ktp']]) ? trim($row[$headerMap['no_ktp']]) : '';
                    $jumlahRaw = isset($row[$headerMap['jumlah']]) ? trim($row[$headerMap['jumlah']]) : '';
                    
                    // Validate required fields
                    if (empty($tglTransaksi) || empty($noKtp) || empty($jumlahRaw)) {
                        $errors[] = "Baris {$rowNumber}: Data tidak lengkap (tgl_transaksi: '{$tglTransaksi}', no_ktp: '{$noKtp}', jumlah: '{$jumlahRaw}')";
                        continue;
                    }
                    
                    $jumlah = (float) str_replace(['.', ','], '', $jumlahRaw);

                    // Validate and normalize date format
                    $tglTransaksiNormalized = $this->normalizeDate($tglTransaksi);
                    if (!$tglTransaksiNormalized) {
                        $errors[] = "Baris {$rowNumber}: Format tanggal tidak valid: '{$tglTransaksi}' (harus YYYY-MM-DD atau format Excel)";
                        continue;
                    }
                    $tglTransaksi = $tglTransaksiNormalized;

                    // Validate KTP number
                    if (!is_numeric($noKtp)) {
                        $errors[] = "Baris {$rowNumber}: Nomor KTP tidak valid";
                        continue;
                    }

                    // Validate amount
                    if ($jumlah <= 0) {
                        $errors[] = "Baris {$rowNumber}: Jumlah harus lebih dari 0";
                        continue;
                    }

                    // Check if member exists
                    $memberExists = DB::table('tbl_anggota')
                        ->where('no_ktp', $noKtp)
                        ->where('aktif', 'Y')
                        ->exists();

                    if (!$memberExists) {
                        $errors[] = "Baris {$rowNumber}: Anggota dengan No KTP {$noKtp} tidak ditemukan atau tidak aktif";
                        continue;
                    }

                    $processedData[] = [
                        'tgl_transaksi' => $tglTransaksi,
                        'no_ktp' => $noKtp,
                        'jumlah' => $jumlah,
                        'bulan' => $bulan,
                        'tahun' => $tahun
                    ];

                    $successCount++;

                } catch (\Exception $e) {
                    $errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                }
            }

            if (empty($processedData)) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tidak ada data valid yang dapat diproses'
                ], 400);
            }

            // Begin transaction
            DB::beginTransaction();

            try {
                // Delete existing upload data for this period from billing_upload_temp
                DB::table('billing_upload_temp')
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->delete();
                
                // Delete existing data from tbl_trans_sp_temp for the period
                // Since tbl_trans_sp_temp doesn't have bulan/tahun columns, delete by date range
                $startDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->startOfMonth()->toDateString();
                $endDate = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();
                
                DB::table('tbl_trans_sp_temp')
                    ->whereBetween('tgl_transaksi', [$startDate, $endDate])
                    ->delete();

                // Insert new upload data to billing_upload_temp (for tracking and querying)
                foreach (array_chunk($processedData, 100) as $chunk) {
                    DB::table('billing_upload_temp')->insert($chunk);
                }

                // Also insert to tbl_trans_sp_temp for stored procedure processing
                foreach (array_chunk($processedData, 100) as $chunk) {
                    // Remove bulan and tahun from chunk for tbl_trans_sp_temp (if table doesn't have these columns)
                    $chunkForSpTemp = array_map(function($item) {
                        return [
                            'tgl_transaksi' => $item['tgl_transaksi'],
                            'no_ktp' => $item['no_ktp'],
                            'jumlah' => $item['jumlah'],
                        ];
                    }, $chunk);
                    DB::table('tbl_trans_sp_temp')->insert($chunkForSpTemp);
                }

                // Call stored procedure to process data to tbl_trans_sp_bayar_temp
                $this->callStoredProcedureBayarUpload($bulan, $tahun);

                // Update main billing table with upload data
                $this->updateMainBillingWithUpload($bulan, $tahun);

                DB::commit();

                $message = "Berhasil upload {$successCount} data";
                if (!empty($errors)) {
                    $message .= " dengan " . count($errors) . " error";
                }

                return response()->json([
                    'status' => 'success',
                    'message' => $message,
                    'success_count' => $successCount,
                    'error_count' => count($errors),
                    'errors' => $errors
                ]);

            } catch (\Exception $e) {
                DB::rollback();
                throw $e;
            }

        } catch (\Exception $e) {
            Log::error('Error uploading Excel: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Call stored procedure bayar_upload to process data from tbl_trans_sp_temp to tbl_trans_sp_bayar_temp
     */
    private function callStoredProcedureBayarUpload($bulan, $tahun)
    {
        try {
            $periode = $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT);
            
            // Check if stored procedure exists before calling
            $procedureExists = DB::select("
                SELECT COUNT(*) as count 
                FROM information_schema.routines 
                WHERE routine_schema = DATABASE() 
                AND routine_name = 'bayar_upload'
            ");
            
            if (isset($procedureExists[0]) && $procedureExists[0]->count > 0) {
                // Call stored procedure bayar_upload
                DB::statement("CALL bayar_upload('{$periode}')");
                Log::info("Stored procedure bayar_upload called successfully for period: {$periode}");
            } else {
                // If stored procedure doesn't exist, process manually
                Log::warning("Stored procedure bayar_upload not found, processing manually");
                $this->processUploadDataManually($bulan, $tahun);
            }
            
        } catch (\Exception $e) {
            Log::error("Error calling stored procedure bayar_upload: " . $e->getMessage());
            // Try manual processing as fallback
            try {
                Log::info("Attempting manual processing as fallback");
                $this->processUploadDataManually($bulan, $tahun);
            } catch (\Exception $fallbackError) {
                Log::error("Manual processing also failed: " . $fallbackError->getMessage());
                throw $e; // Throw original error
            }
        }
    }

    /**
     * Manually process upload data from tbl_trans_sp_temp to tbl_trans_sp_bayar_temp
     * This is a fallback when stored procedure is not available
     */
    private function processUploadDataManually($bulan, $tahun)
    {
        // Get upload data grouped by no_ktp
        $uploadData = DB::table('tbl_trans_sp_temp')
            ->select('no_ktp', DB::raw('SUM(jumlah) as total_upload'))
            ->groupBy('no_ktp')
            ->get();

        foreach ($uploadData as $upload) {
            // Get anggota_id from tbl_anggota
            $anggota = DB::table('tbl_anggota')
                ->where('no_ktp', $upload->no_ktp)
                ->first();
            
            $anggotaId = $anggota ? $anggota->id : null;
            
            // Update or insert into main billing table
            DB::table('tbl_trans_sp_bayar_temp')->updateOrInsert(
                [
                    'tgl_transaksi' => \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString(),
                    'no_ktp' => $upload->no_ktp,
                ],
                [
                    'anggota_id' => $anggotaId,
                    'jumlah' => DB::raw('COALESCE(jumlah, 0) + ' . ($upload->total_upload ?? 0)),
                    'keterangan' => 'Upload Excel ' . $bulan . '-' . $tahun,
                    'tagihan_simpanan_wajib' => DB::raw('COALESCE(tagihan_simpanan_wajib, 0)'),
                    'tagihan_simpanan_sukarela' => DB::raw('COALESCE(tagihan_simpanan_sukarela, 0)'),
                    'tagihan_simpanan_khusus_2' => DB::raw('COALESCE(tagihan_simpanan_khusus_2, 0)'),
                    'tagihan_pinjaman' => DB::raw('COALESCE(tagihan_pinjaman, 0)'),
                    'tagihan_pinjaman_jasa' => DB::raw('COALESCE(tagihan_pinjaman_jasa, 0)'),
                    'tagihan_toserda' => DB::raw('COALESCE(tagihan_toserda, 0)'),
                    'total_tagihan_simpanan' => DB::raw('COALESCE(total_tagihan_simpanan, 0)'),
                    'selisih' => DB::raw('COALESCE(selisih, 0)'),
                    'saldo_simpanan_sukarela' => DB::raw('COALESCE(saldo_simpanan_sukarela, 0)'),
                    'saldo_akhir_simpanan_sukarela' => DB::raw('COALESCE(saldo_akhir_simpanan_sukarela, 0)')
                ]
            );
        }
        
        Log::info("Manual processing completed for period: {$bulan}-{$tahun}");
    }

    /**
     * Update main billing table with upload data
     * This method ensures data from billing_upload_temp is properly synced to tbl_trans_sp_bayar_temp
     */
    private function updateMainBillingWithUpload($bulan, $tahun)
    {
        try {
            // Get upload data for this period from billing_upload_temp
            $uploadData = DB::table('billing_upload_temp')
                ->select('no_ktp', DB::raw('SUM(jumlah) as total_upload'))
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->groupBy('no_ktp')
                ->get();

            if ($uploadData->isEmpty()) {
                Log::warning("No upload data found in billing_upload_temp for period: {$bulan}-{$tahun}");
                return;
            }

            $endOfMonth = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->endOfMonth()->toDateString();

            foreach ($uploadData as $upload) {
                // Get anggota_id from tbl_anggota
                $anggota = DB::table('tbl_anggota')
                    ->where('no_ktp', $upload->no_ktp)
                    ->where('aktif', 'Y')
                    ->first();
                
                if (!$anggota) {
                    Log::warning("Anggota dengan No KTP {$upload->no_ktp} tidak ditemukan atau tidak aktif");
                    continue;
                }
                
                $anggotaId = $anggota->id;
                
                // Check if record already exists
                $existing = DB::table('tbl_trans_sp_bayar_temp')
                    ->where('tgl_transaksi', $endOfMonth)
                    ->where('no_ktp', $upload->no_ktp)
                    ->first();

                if ($existing) {
                    // Update existing record - add to jumlah if not already processed by stored procedure
                    DB::table('tbl_trans_sp_bayar_temp')
                        ->where('tgl_transaksi', $endOfMonth)
                        ->where('no_ktp', $upload->no_ktp)
                        ->update([
                            'anggota_id' => $anggotaId,
                            'jumlah' => DB::raw('COALESCE(jumlah, 0) + ' . ($upload->total_upload ?? 0)),
                            'keterangan' => DB::raw("CONCAT(COALESCE(keterangan, ''), ' | Upload Excel {$bulan}-{$tahun}')"),
                        ]);
                } else {
                    // Insert new record
                    DB::table('tbl_trans_sp_bayar_temp')->insert([
                        'tgl_transaksi' => $endOfMonth,
                        'no_ktp' => $upload->no_ktp,
                        'anggota_id' => $anggotaId,
                        'jumlah' => $upload->total_upload ?? 0,
                        'keterangan' => 'Upload Excel ' . $bulan . '-' . $tahun,
                        'tagihan_simpanan_wajib' => 0,
                        'tagihan_simpanan_sukarela' => 0,
                        'tagihan_simpanan_khusus_2' => 0,
                        'tagihan_simpanan_pokok' => 0,
                        'tagihan_pinjaman' => 0,
                        'tagihan_pinjaman_jasa' => 0,
                        'tagihan_toserda' => 0,
                        'total_tagihan_simpanan' => 0,
                        'selisih' => 0,
                        'saldo_simpanan_sukarela' => 0,
                        'saldo_akhir_simpanan_sukarela' => 0
                    ]);
                }
            }

            Log::info("Successfully updated main billing table with upload data for period: {$bulan}-{$tahun}");
            
        } catch (\Exception $e) {
            Log::error("Error updating main billing table: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Normalize date from various Excel formats to YYYY-MM-DD
     */
    private function normalizeDate($dateInput)
    {
        if (empty($dateInput)) {
            return false;
        }

        // If already in YYYY-MM-DD format
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dateInput)) {
            return $dateInput;
        }

        // Try to parse as Excel date (numeric timestamp)
        if (is_numeric($dateInput)) {
            try {
                // Excel date starts from 1900-01-01, but PHP uses 1970-01-01
                // Excel date = days since 1900-01-01
                // Unix timestamp = seconds since 1970-01-01
                // For dates after 1970, we can use: (ExcelDate - 25569) * 86400
                $excelTimestamp = ($dateInput - 25569) * 86400;
                $date = date('Y-m-d', $excelTimestamp);
                if ($date && $date !== '1970-01-01') {
                    return $date;
                }
            } catch (\Exception $e) {
                // Continue to other formats
            }
        }

        // Try various date formats
        $formats = [
            'Y-m-d',
            'd/m/Y',
            'd-m-Y',
            'Y/m/d',
            'd.m.Y',
            'Y.m.d',
        ];

        foreach ($formats as $format) {
            try {
                $date = \Carbon\Carbon::createFromFormat($format, $dateInput);
                if ($date) {
                    return $date->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        // Try Carbon's flexible parser
        try {
            $date = \Carbon\Carbon::parse($dateInput);
            return $date->format('Y-m-d');
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Debug method to check upload data
     */
    public function debugUploadData($bulan, $tahun)
    {
        try {
            // Check billing_upload_temp table
            $uploadData = DB::table('billing_upload_temp')
                ->where('bulan', $bulan)
                ->where('tahun', $tahun)
                ->get();

            // Check main billing table
            $mainBillingData = DB::table('tbl_trans_sp_bayar_temp')
                ->whereMonth('tgl_transaksi', $bulan)
                ->whereYear('tgl_transaksi', $tahun)
                ->get();

            return response()->json([
                'status' => 'success',
                'debug_info' => [
                    'periode' => $bulan . '-' . $tahun,
                    'upload_table' => [
                        'total_records' => $uploadData->count(),
                        'sample_data' => $uploadData->take(3)->toArray()
                    ],
                    'main_billing_table' => [
                        'total_records' => $mainBillingData->count(),
                        'sample_data' => $mainBillingData->take(3)->toArray()
                    ]
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Debug error: ' . $e->getMessage()
            ], 500);
        }
    }
}