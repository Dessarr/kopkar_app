<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_pengajuan;
use App\Models\TblPinjamanH;
use App\Models\suku_bunga;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DtaPengajuanController extends Controller
{
    public function index()
    {
        $dataPengajuan = data_pengajuan::with('anggota')->orderByDesc('tgl_input')->paginate(10);
        return view('pinjaman.data_pengajuan', compact('dataPengajuan'));
    }

    public function approve(string $id, Request $request)
    {
        try {
            DB::beginTransaction();

            $pengajuan = data_pengajuan::findOrFail($id);
            
            // Validasi: hanya bisa dari status pending
            if ($pengajuan->status != 0) {
                return back()->with('error', 'Hanya pengajuan yang masih pending yang dapat disetujui');
            }

            // Update status pengajuan menjadi disetujui
            $pengajuan->status = 1; // Disetujui
            $pengajuan->alasan = $request->input('alasan', '');
            $pengajuan->tgl_cair = $request->input('tgl_cair', now());
            $pengajuan->tgl_update = now();
            $pengajuan->save();

            // INSERT KE tbl_pinjaman_h (sesuai alur yang benar)
            $pinjaman = new TblPinjamanH();
            
            // Generate ID transaksi unik (format: YYMM + 4 digit counter)
            $counter = 1;
            do {
                $id_trans = date('ym') . str_pad($counter, 4, '0', STR_PAD_LEFT);
                $existing = TblPinjamanH::find($id_trans);
                $counter++;
            } while ($existing && $counter < 10000); // Prevent infinite loop
            
            // Validasi panjang ID (maksimal 8 digit)
            if (strlen($id_trans) > 8) {
                throw new \Exception('ID transaksi terlalu panjang: ' . $id_trans);
            }
            
            Log::info('ID transaksi di-generate', [
                'id_trans' => $id_trans,
                'length' => strlen($id_trans),
                'counter' => $counter - 1
            ]);
            
            // Ambil data suku bunga berdasarkan jenis pinjaman
            $jenisBunga = $pengajuan->jenis == '1' ? 'bunga_biasa' : 'bunga_barang';
            $sukuBunga = suku_bunga::where('opsi_key', $jenisBunga)->first();
            
            if (!$sukuBunga) {
                Log::warning('Suku bunga tidak ditemukan', [
                    'opsi_key' => $jenisBunga,
                    'pengajuan_id' => $id,
                    'jenis_pinjaman' => $pengajuan->jenis
                ]);
            }
            
            $bungaPersen = $sukuBunga ? (float)$sukuBunga->opsi_val : 12; // Default 12%
            
            // Jika suku bunga tidak ditemukan, buat default values
            if (!$sukuBunga) {
                $this->createDefaultSukuBunga($jenisBunga);
            }
            
            // Hitung bunga dalam rupiah
            $bungaRp = ($bungaPersen * $pengajuan->nominal) / 100;
            
            // Hitung jumlah angsuran per bulan
            $jumlahAngsuran = $pengajuan->nominal / $pengajuan->lama_ags;
            
            // Biaya admin (default 1% dari nominal)
            $biayaAdmin = ($pengajuan->nominal * 1) / 100;

            $pinjaman->id = $id_trans;
            $pinjaman->no_ktp = $pengajuan->anggota->no_ktp ?? '';
            $pinjaman->tgl_pinjam = $pengajuan->tgl_cair ?? now();
            $pinjaman->anggota_id = $pengajuan->anggota_id;
            $pinjaman->barang_id = 4; // Default barang (uang)
            $pinjaman->lama_angsuran = $pengajuan->lama_ags;
            $pinjaman->jumlah_angsuran = $jumlahAngsuran;
            $pinjaman->jumlah = $pengajuan->nominal;
            $pinjaman->bunga = $bungaPersen;
            $pinjaman->bunga_rp = $bungaRp;
            $pinjaman->biaya_adm = $biayaAdmin;
            $pinjaman->lunas = 'Belum';
            $pinjaman->dk = 'K';
            $pinjaman->kas_id = 2; // Default kas sumber
            $pinjaman->jns_trans = 7; // Jenis transaksi pinjaman
            $pinjaman->status = '1'; // Status pinjaman aktif
            $pinjaman->jenis_pinjaman = $pengajuan->jenis;
            $pinjaman->keterangan = $pengajuan->keterangan ?? '';
            $pinjaman->user_name = 'admin';
            $pinjaman->id_cabang = 1; // Default cabang ID
            $pinjaman->save();

            DB::commit();

            Log::info('Pengajuan disetujui dan data pinjaman dibuat', [
                'pengajuan_id' => $id,
                'pinjaman_id' => $id_trans,
                'anggota_id' => $pengajuan->anggota_id
            ]);

            return back()->with('success', 'Pengajuan disetujui dan data pinjaman berhasil dibuat');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Gagal menyetujui pengajuan', [
                'pengajuan_id' => $id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal menyetujui pengajuan: ' . $e->getMessage());
        }
    }

    public function reject(string $id, Request $request)
    {
        $request->validate(['alasan' => ['required','string','max:500']]);
        $pengajuan = data_pengajuan::findOrFail($id);
        $pengajuan->status = 2; // Ditolak
        $pengajuan->alasan = $request->alasan;
        $pengajuan->tgl_update = now();
        $pengajuan->save();
        Log::info('Admin menolak pengajuan', ['id'=>$id, 'ajuan_id'=>$pengajuan->ajuan_id, 'alasan'=>$request->alasan]);
        return back()->with('success', 'Pengajuan ditolak');
    }

    public function cancel(string $id)
    {
        $pengajuan = data_pengajuan::findOrFail($id);
        $pengajuan->status = 4; // Batal
        $pengajuan->tgl_update = now();
        $pengajuan->save();
        Log::info('Admin membatalkan pengajuan', ['id'=>$id, 'ajuan_id'=>$pengajuan->ajuan_id]);
        return back()->with('success', 'Pengajuan dibatalkan');
    }

    public function destroy(string $id)
    {
        $pengajuan = data_pengajuan::findOrFail($id);
        $pengajuan->delete();
        Log::warning('Admin menghapus pengajuan', ['id'=>$id, 'ajuan_id'=>$pengajuan->ajuan_id]);
        return back()->with('success', 'Pengajuan dihapus');
    }

    public function terlaksana(string $id)
    {
        try {
            $pengajuan = data_pengajuan::findOrFail($id);
            
            // Validasi: hanya bisa dari status disetujui
            if ($pengajuan->status != 1) {
                return back()->with('error', 'Hanya pengajuan yang sudah disetujui yang dapat diubah menjadi terlaksana');
            }

            $pengajuan->status = 3; // Terlaksana
            $pengajuan->tgl_update = now();
            $pengajuan->save();

            Log::info('Admin mengubah status pengajuan menjadi terlaksana', [
                'id' => $id, 
                'ajuan_id' => $pengajuan->ajuan_id
            ]);

            return back()->with('success', 'Status pengajuan berhasil diubah menjadi terlaksana');

        } catch (\Exception $e) {
            Log::error('Gagal ubah status menjadi terlaksana', [
                'id' => $id, 
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Gagal ubah status: ' . $e->getMessage());
        }
    }

    /**
     * Buat default suku bunga jika tidak ada
     */
    private function createDefaultSukuBunga($jenisBunga)
    {
        try {
            $defaultValues = [
                'bunga_biasa' => '12',
                'bunga_barang' => '15',
                'biaya_adm' => '1'
            ];

            if (isset($defaultValues[$jenisBunga])) {
                suku_bunga::create([
                    'opsi_key' => $jenisBunga,
                    'opsi_val' => $defaultValues[$jenisBunga],
                    'id_cabang' => '1'
                ]);

                Log::info('Default suku bunga dibuat', [
                    'opsi_key' => $jenisBunga,
                    'opsi_val' => $defaultValues[$jenisBunga]
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Gagal membuat default suku bunga', [
                'jenis_bunga' => $jenisBunga,
                'error' => $e->getMessage()
            ]);
        }
    }

    public function cetak(string $id)
    {
        $pengajuan = data_pengajuan::findOrFail($id);
        return view('pinjaman.cetak_pengajuan_admin', compact('pengajuan'));
    }
}