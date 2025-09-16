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
            Log::info('Raw Excel data (first 3 rows):', array_slice($excelData, 0, 3));
            
            // Since Excel doesn't have proper headers, assume first 3 columns are the data we need
            // Column 0 = tgl_transaksi, Column 1 = no_ktp, Column 2 = jumlah
            $headerMap = [
                'tgl_transaksi' => 0,
                'no_ktp' => 1,
                'jumlah' => 2
            ];
            
            // Debug: Log the first few rows to see the data structure
            Log::info('First 3 rows of Excel data:', array_slice($excelData, 0, 3));
            Log::info('Using column mapping:', $headerMap);
            
            // No need to remove header row since first row is already data

            // Process Excel data
            $processedData = [];
            $errors = [];
            $successCount = 0;

            foreach ($excelData as $index => $row) {
                $rowNumber = $index + 2; // +2 because we removed header and array is 0-indexed

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
                    
                    $jumlah = (float) str_replace('.', '', $jumlahRaw);

                    // Validate date format
                    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $tglTransaksi)) {
                        $errors[] = "Baris {$rowNumber}: Format tanggal tidak valid (harus YYYY-MM-DD)";
                        continue;
                    }

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
                // Delete existing upload data for this period
                DB::table('billing_upload_temp')
                    ->where('bulan', $bulan)
                    ->where('tahun', $tahun)
                    ->delete();

                // Insert new upload data to tbl_trans_sp_temp (SIMPLE TABLE)
                foreach (array_chunk($processedData, 100) as $chunk) {
                    DB::table('tbl_trans_sp_temp')->insert($chunk);
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
            
            // Call stored procedure bayar_upload
            DB::statement("CALL bayar_upload('{$periode}')");
            
            Log::info("Stored procedure bayar_upload called successfully for period: {$periode}");
            
        } catch (\Exception $e) {
            Log::error("Error calling stored procedure bayar_upload: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update main billing table with upload data
     */
    private function updateMainBillingWithUpload($bulan, $tahun)
    {
        // Get upload data for this period
        $uploadData = DB::table('billing_upload_temp')
            ->select('no_ktp', DB::raw('SUM(jumlah) as total_upload'))
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
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
                    'jumlah' => $upload->total_upload,
                    'keterangan' => 'Upload Excel ' . $bulan . '-' . $tahun,
                    'tagihan_simpanan_wajib' => 0,
                    'tagihan_simpanan_sukarela' => 0,
                    'tagihan_simpanan_khusus_2' => 0,
                    'tagihan_pinjaman' => 0,
                    'tagihan_pinjaman_jasa' => 0,
                    'tagihan_toserda' => 0,
                    'total_tagihan_simpanan' => 0,
                    'selisih' => 0,
                    'saldo_simpanan_sukarela' => 0,
                    'saldo_akhir_simpanan_sukarela' => 0
                ]
            );
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