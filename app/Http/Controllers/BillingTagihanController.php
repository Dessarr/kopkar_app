<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\data_anggota;
use App\Models\TblTransTagihan;

class BillingTagihanController extends Controller
{
    /**
     * Tampilkan halaman billing tagihan
     */
    public function index()
    {
        $jenisSimpanan = [
            ['id' => 41, 'nama' => 'Simpanan Wajib'],
            ['id' => 32, 'nama' => 'Simpanan Sukarela'],
            ['id' => 51, 'nama' => 'Simpanan Khusus 1'],
            ['id' => 52, 'nama' => 'Simpanan Khusus 2'],
            ['id' => 40, 'nama' => 'Simpanan Pokok'],
            ['id' => 156, 'nama' => 'Tabungan Perumahan']
        ];
        
        return view('billing.tagihan', compact('jenisSimpanan'));
    }
    
    /**
     * Generate billing untuk jenis simpanan tertentu
     */
    public function generateBilling(Request $request)
    {
        $request->validate([
            'jenis_id' => 'required|integer',
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020'
        ]);
        
        $jenisId = $request->jenis_id;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        
        // Tentukan field berdasarkan jenis_id
        $fieldMap = [
            41 => 'simpanan_wajib',
            32 => 'simpanan_sukarela', 
            51 => 'simpanan_khusus_1',
            52 => 'simpanan_khusus_2',
            40 => 'simpanan_pokok',
            156 => 'simpanan_perumahan'
        ];
        
        $fieldName = $fieldMap[$jenisId] ?? 'simpanan_wajib';
        
        DB::beginTransaction();
        
        try {
            // 1. Ambil data anggota yang aktif dan memiliki target simpanan > 0
            $anggotaList = data_anggota::where('aktif', 'Y')
                ->where($fieldName, '>', 0)
                ->get();
            
            $count = 0;
            
            foreach ($anggotaList as $anggota) {
                // 2. Cek apakah sudah ada tagihan untuk periode ini
                $existingTagihan = TblTransTagihan::where('no_ktp', $anggota->no_ktp)
                    ->where('jenis_id', $jenisId)
                    ->whereYear('tgl_transaksi', $tahun)
                    ->whereMonth('tgl_transaksi', $bulan)
                    ->first();
                
                if (!$existingTagihan) {
                    // 3. Buat tagihan baru
                    TblTransTagihan::create([
                        'tgl_transaksi' => $tahun . '-' . str_pad($bulan, 2, '0', STR_PAD_LEFT) . '-01',
                        'no_ktp' => $anggota->no_ktp,
                        'anggota_id' => $anggota->id,
                        'jenis_id' => $jenisId,
                        'jumlah' => $anggota->$fieldName,
                        'keterangan' => 'Tagihan ' . $this->getJenisNama($jenisId) . ' ' . $this->getBulanNama($bulan) . ' ' . $tahun,
                        'akun' => 'Tagihan',
                        'dk' => 'D',
                        'kas_id' => 1,
                        'user_name' => Auth::user()->name ?? 'admin'
                    ]);
                    
                    $count++;
                }
            }
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil generate {$count} tagihan untuk " . $this->getJenisNama($jenisId) . " periode " . $this->getBulanNama($bulan) . " {$tahun}"
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Hapus tagihan untuk periode tertentu
     */
    public function deleteBilling(Request $request)
    {
        $request->validate([
            'jenis_id' => 'required|integer',
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|min:2020'
        ]);
        
        $jenisId = $request->jenis_id;
        $bulan = $request->bulan;
        $tahun = $request->tahun;
        
        DB::beginTransaction();
        
        try {
            $deleted = TblTransTagihan::where('jenis_id', $jenisId)
                ->whereYear('tgl_transaksi', $tahun)
                ->whereMonth('tgl_transaksi', $bulan)
                ->delete();
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => "Berhasil menghapus {$deleted} tagihan untuk " . $this->getJenisNama($jenisId) . " periode " . $this->getBulanNama($bulan) . " {$tahun}"
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
    }
    
    /**
     * Lihat data tagihan
     */
    public function viewBilling(Request $request)
    {
        $jenisId = $request->get('jenis_id');
        $bulan = $request->get('bulan', date('m'));
        $tahun = $request->get('tahun', date('Y'));
        
        $query = TblTransTagihan::with(['anggota', 'jenis_simpanan'])
            ->orderBy('tgl_transaksi', 'desc');
        
        if ($jenisId) {
            $query->where('jenis_id', $jenisId);
        }
        
        if ($bulan && $tahun) {
            $query->whereYear('tgl_transaksi', $tahun)
                  ->whereMonth('tgl_transaksi', $bulan);
        }
        
        $tagihan = $query->paginate(20);
        
        return response()->json([
            'success' => true,
            'data' => $tagihan
        ]);
    }
    
    /**
     * Get nama jenis simpanan
     */
    private function getJenisNama($jenisId)
    {
        $jenisMap = [
            41 => 'Simpanan Wajib',
            32 => 'Simpanan Sukarela',
            51 => 'Simpanan Khusus 1',
            52 => 'Simpanan Khusus 2',
            40 => 'Simpanan Pokok',
            156 => 'Tabungan Perumahan'
        ];
        
        return $jenisMap[$jenisId] ?? 'Simpanan';
    }
    
    /**
     * Get nama bulan
     */
    private function getBulanNama($bulan)
    {
        $bulanMap = [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ];
        
        return $bulanMap[$bulan] ?? 'Bulan';
    }
}