<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_pengajuan_penarikan;
use App\Models\TblTransSp;
use App\Models\data_anggota;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use App\Services\ActivityLogService;

class DtaPengajuanPenarikanController extends Controller
{
    public function testDatabase()
    {
        try {
            // Check if table exists
            $tableExists = \Schema::hasTable('tbl_pengajuan_penarikan');
            
            // Get total count without any filters
            $totalCount = data_pengajuan_penarikan::count();
            
            // Get sample data
            $sampleData = data_pengajuan_penarikan::take(5)->get();
            
            // Check relationships
            $withRelationships = data_pengajuan_penarikan::with(['anggota', 'jenisSimpanan'])->take(3)->get();
            
            // Get database connection info
            $connection = \DB::connection();
            $databaseName = $connection->getDatabaseName();
            
            $debugInfo = [
                'table_exists' => $tableExists,
                'database_name' => $databaseName,
                'total_count' => $totalCount,
                'sample_data' => $sampleData->toArray(),
                'relationships_working' => $withRelationships->toArray(),
                'connection_status' => $connection->getPdo() ? 'connected' : 'disconnected'
            ];
            
            Log::info('Database Debug Info', $debugInfo);
            
            return response()->json($debugInfo);
            
        } catch (\Exception $e) {
            Log::error('Database Debug Error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], 500);
        }
    }

    public function testSearch(Request $request)
    {
        try {
            $search = $request->get('search', '');
            Log::info('Test search called', ['search_term' => $search]);
            
            // Test basic search without relationships
            $basicSearch = data_pengajuan_penarikan::where('ajuan_id', 'like', "%{$search}%")->get();
            Log::info('Basic search results', ['count' => $basicSearch->count(), 'results' => $basicSearch->toArray()]);
            
            // Test search with relationships
            $searchWithRelations = data_pengajuan_penarikan::where(function($q) use ($search) {
                $q->where('ajuan_id', 'like', "%{$search}%")
                  ->orWhereHas('anggota', function($subQ) use ($search) {
                      $subQ->where('nama', 'like', "%{$search}%")
                           ->orWhere('no_ktp', 'like', "%{$search}%");
                  });
            })->with(['anggota', 'jenisSimpanan'])->get();
            
            Log::info('Search with relationships results', ['count' => $searchWithRelations->count()]);
            
            // Get sample data to check structure
            $sampleData = data_pengajuan_penarikan::with(['anggota', 'jenisSimpanan'])->take(3)->get();
            
            return response()->json([
                'search_term' => $search,
                'basic_search_count' => $basicSearch->count(),
                'relationship_search_count' => $searchWithRelations->count(),
                'sample_data' => $sampleData->toArray(),
                'message' => 'Search test completed'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Search test error', ['error' => $e->getMessage()]);
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function index(Request $request)
    {
        // Start with basic query
        $query = data_pengajuan_penarikan::query();

        // Add relationships
        $query->with(['anggota', 'jenisSimpanan']);

        // ===== FILTER SISTEM BARU =====
        
        // 1. Filter Status (Multiple Selection)
        if ($request->filled('status_filter')) {
            $statusArray = is_array($request->status_filter) ? $request->status_filter : [$request->status_filter];
            $query->whereIn('status', $statusArray);
        }

        // 2. Filter Jenis Simpanan (Multiple Selection)
        if ($request->filled('jenis_filter')) {
            $jenisArray = is_array($request->jenis_filter) ? $request->jenis_filter : [$request->jenis_filter];
            $query->whereIn('jenis', $jenisArray);
        }

        // 3. Filter Tanggal (Date Range)
        if ($request->filled('date_from')) {
            $query->whereDate('tgl_input', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('tgl_input', '<=', $request->date_to);
        }

        // 4. Filter Periode Bulan (21-20)
        if ($request->filled('periode_bulan')) {
            $periode = $request->periode_bulan; // Format: YYYY-MM
            $tglDari = date('Y-m-21', strtotime($periode . '-01 -1 month'));
            $tglSampai = $periode . '-20';
            $query->whereDate('tgl_input', '>=', $tglDari)
                  ->whereDate('tgl_input', '<=', $tglSampai);
        }

        // 5. Filter Pencarian (Nama, Ajuan ID, KTP, No Ajuan)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function($q) use ($search) {
                $q->where('ajuan_id', 'like', "%{$search}%")
                  ->orWhere('no_ajuan', 'like', "%{$search}%")
                  ->orWhereHas('anggota', function($subQ) use ($search) {
                      $subQ->where('nama', 'like', "%{$search}%")
                           ->orWhere('no_ktp', 'like', "%{$search}%")
                           ->orWhere('identitas', 'like', "%{$search}%");
                  });
            });
        }

        // 6. Filter Departemen
        if ($request->filled('departemen_filter')) {
            $departemenArray = is_array($request->departemen_filter) ? $request->departemen_filter : [$request->departemen_filter];
            $query->whereHas('anggota', function($subQ) use ($departemenArray) {
                $subQ->whereIn('departement', $departemenArray);
            });
        }

        // 7. Filter Nominal Range
        if ($request->filled('nominal_min')) {
            $query->where('nominal', '>=', $request->nominal_min);
        }
        if ($request->filled('nominal_max')) {
            $query->where('nominal', '<=', $request->nominal_max);
        }

        // 8. Filter Cabang
        if ($request->filled('cabang_filter')) {
            $cabangArray = is_array($request->cabang_filter) ? $request->cabang_filter : [$request->cabang_filter];
            $query->whereIn('id_cabang', $cabangArray);
        }

        // Get the data with pagination
        $dataPengajuan = $query->orderBy('tgl_input', 'desc')->paginate(15);
        
        // Get data for filter dropdowns
        try {
            $jenisSimpanan = \App\Models\jns_simpan::where('tampil', 'Y')->get();
            $departemen = \App\Models\data_anggota::select('departement')->distinct()->whereNotNull('departement')->pluck('departement');
            $cabang = \App\Models\data_anggota::select('id_cabang')->distinct()->whereNotNull('id_cabang')->pluck('id_cabang');
        } catch (\Exception $e) {
            $jenisSimpanan = collect([]);
            $departemen = collect([]);
            $cabang = collect([]);
        }
        
        return view('simpanan.pengajuan_penarikan', compact('dataPengajuan', 'jenisSimpanan', 'departemen', 'cabang'));
    }

    public function show($id)
    {
        $pengajuan = data_pengajuan_penarikan::with(['anggota', 'jenisSimpanan'])->findOrFail($id);
        
        // Log admin viewing detail
        Log::info('Admin melihat detail pengajuan penarikan', [
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name ?? 'admin',
            'pengajuan_id' => $pengajuan->id,
            'ajuan_id' => $pengajuan->ajuan_id,
            'member_name' => $pengajuan->anggota->nama ?? 'N/A',
            'member_ktp' => $pengajuan->anggota->no_ktp ?? 'N/A',
            'nominal' => $pengajuan->nominal,
            'status' => $pengajuan->status,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
        
        // Get jenis simpanan for filter dropdown - always get this
        try {
            $jenisSimpanan = \App\Models\jns_simpan::where('tampil', 'Y')->get();
        } catch (\Exception $e) {
            // If there's an error, provide empty collection
            $jenisSimpanan = collect([]);
        }
        
        return view('simpanan.pengajuan_penarikan', compact('pengajuan', 'jenisSimpanan'));
    }

    public function approve($id, Request $request)
    {
        try {
            $pengajuan = data_pengajuan_penarikan::with(['anggota'])->findOrFail($id);
            
            if ($pengajuan->status !== 0) {
                ActivityLogService::logFailed(
                    'approve',
                    'pengajuan_penarikan',
                    'Admin mencoba menyetujui pengajuan dengan status tidak valid',
                    'Pengajuan tidak dapat disetujui karena status bukan pending',
                    $pengajuan->toArray(),
                    null,
                    $pengajuan->id,
                    'data_pengajuan_penarikan'
                );
                
                return redirect()->back()->with('error', 'Pengajuan tidak dapat disetujui.');
            }

            // Log before approval and processing
            ActivityLogService::logPending(
                'approve',
                'pengajuan_penarikan',
                'Admin memulai proses persetujuan dan penarikan simpanan',
                $pengajuan->toArray(),
                $request->all(),
                $pengajuan->id,
                'data_pengajuan_penarikan'
            );

            // Store old values for logging
            $oldValues = $pengajuan->toArray();

            // Create transaction record for withdrawal (Kredit/Penarikan)
            $transaksi = new TblTransSp();
            $transaksi->tgl_transaksi = $request->tgl_cair ?? now();
            $transaksi->no_ktp = $pengajuan->anggota->no_ktp;
            $transaksi->anggota_id = $pengajuan->anggota_id;
            $transaksi->jenis_id = $pengajuan->jenis;
            $transaksi->jumlah = $pengajuan->nominal;
            $transaksi->keterangan = $pengajuan->keterangan;
            $transaksi->akun = 'Penarikan';
            $transaksi->dk = 'K'; // Kredit untuk penarikan (mengurangi saldo)
            $transaksi->kas_id = 1; // Default kas
            $transaksi->update_data = now();
            $transaksi->user_name = auth()->user()->name ?? 'admin';
            $transaksi->nama_penyetor = $pengajuan->anggota->nama;
            $transaksi->no_identitas = $pengajuan->anggota->no_ktp;
            $transaksi->alamat = $pengajuan->anggota->alamat;
            $transaksi->id_cabang = $pengajuan->id_cabang;
            $transaksi->save();

            // Update pengajuan status to Terlaksana (3)
            $pengajuan->status = 3; // Terlaksana (langsung diproses)
            $pengajuan->alasan = $request->alasan ?? '';
            $pengajuan->tgl_cair = $request->tgl_cair ?? now();
            $pengajuan->tgl_update = now();
            $pengajuan->save();

            // Log successful approval and processing
            ActivityLogService::logSuccess(
                'approve',
                'pengajuan_penarikan',
                "Berhasil menyetujui dan memproses pengajuan penarikan - Ajuan ID: {$pengajuan->ajuan_id}, Nominal: Rp " . number_format($pengajuan->nominal, 0, ',', '.'),
                $oldValues,
                $pengajuan->toArray(),
                $pengajuan->id,
                'data_pengajuan_penarikan'
            );

            return redirect()->route('admin.pengajuan.penarikan.index')
                ->with('success', 'Pengajuan penarikan berhasil disetujui dan diproses');
        } catch (\Exception $e) {
            // Log error
            ActivityLogService::logFailed(
                'approve',
                'pengajuan_penarikan',
                'Gagal menyetujui dan memproses pengajuan penarikan - Error sistem',
                $e->getMessage(),
                null,
                $request->all(),
                $id,
                'data_pengajuan_penarikan'
            );
            
            return redirect()->back()->with('error', 'Gagal menyetujui pengajuan: ' . $e->getMessage());
        }
    }

    public function reject($id, Request $request)
    {
        try {
            $request->validate([
                'alasan' => 'required|string|max:500'
            ]);

            $pengajuan = data_pengajuan_penarikan::with(['anggota'])->findOrFail($id);
            
            if ($pengajuan->status !== 0) {
                Log::warning('Admin mencoba menolak pengajuan dengan status tidak valid', [
                    'admin_id' => auth()->id(),
                    'admin_name' => auth()->user()->name ?? 'admin',
                    'pengajuan_id' => $pengajuan->id,
                    'ajuan_id' => $pengajuan->ajuan_id,
                    'current_status' => $pengajuan->status,
                    'expected_status' => 0,
                    'ip_address' => request()->ip()
                ]);
                return redirect()->back()->with('error', 'Pengajuan tidak dapat ditolak.');
            }

            // Log before rejection
            Log::info('Admin memulai proses penolakan pengajuan penarikan', [
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name ?? 'admin',
                'pengajuan_id' => $pengajuan->id,
                'ajuan_id' => $pengajuan->ajuan_id,
                'member_name' => $pengajuan->anggota->nama ?? 'N/A',
                'member_ktp' => $pengajuan->anggota->no_ktp ?? 'N/A',
                'nominal' => $pengajuan->nominal,
                'alasan' => $request->alasan,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            $pengajuan->status = 2; // Ditolak
            $pengajuan->alasan = $request->alasan;
            $pengajuan->tgl_update = now();
            $pengajuan->save();

            // Log successful rejection
            Log::info('Pengajuan penarikan berhasil ditolak', [
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name ?? 'admin',
                'pengajuan_id' => $pengajuan->id,
                'ajuan_id' => $pengajuan->ajuan_id,
                'member_name' => $pengajuan->anggota->nama ?? 'N/A',
                'member_ktp' => $pengajuan->anggota->no_ktp ?? 'N/A',
                'nominal' => $pengajuan->nominal,
                'alasan' => $pengajuan->alasan,
                'status_changed_from' => 0,
                'status_changed_to' => 2,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toDateTimeString()
            ]);

            return redirect()->route('admin.pengajuan.penarikan.index')
                ->with('success', 'Pengajuan penarikan berhasil ditolak');
        } catch (\Exception $e) {
            Log::error('Gagal menolak pengajuan penarikan', [
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name ?? 'admin',
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->back()->with('error', 'Gagal menolak pengajuan: ' . $e->getMessage());
        }
    }



    public function destroy($id)
    {
        try {
            $pengajuan = data_pengajuan_penarikan::with(['anggota'])->findOrFail($id);
            
            if ($pengajuan->status !== 0) {
                Log::warning('Admin mencoba menghapus pengajuan dengan status tidak valid', [
                    'admin_id' => auth()->id(),
                    'admin_name' => auth()->user()->name ?? 'admin',
                    'pengajuan_id' => $pengajuan->id,
                    'ajuan_id' => $pengajuan->ajuan_id,
                    'current_status' => $pengajuan->status,
                    'expected_status' => 0,
                    'ip_address' => request()->ip()
                ]);
                return redirect()->back()->with('error', 'Hanya pengajuan pending yang dapat dihapus.');
            }

            // Log before deletion
            Log::info('Admin memulai proses penghapusan pengajuan penarikan', [
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name ?? 'admin',
                'pengajuan_id' => $pengajuan->id,
                'ajuan_id' => $pengajuan->ajuan_id,
                'member_name' => $pengajuan->anggota->nama ?? 'N/A',
                'member_ktp' => $pengajuan->anggota->no_ktp ?? 'N/A',
                'nominal' => $pengajuan->nominal,
                'status' => $pengajuan->status,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent()
            ]);

            $pengajuan->delete();

            // Log successful deletion
            Log::info('Pengajuan penarikan berhasil dihapus', [
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name ?? 'admin',
                'pengajuan_id' => $id,
                'ajuan_id' => $pengajuan->ajuan_id ?? 'N/A',
                'member_name' => $pengajuan->anggota->nama ?? 'N/A',
                'member_ktp' => $pengajuan->anggota->no_ktp ?? 'N/A',
                'nominal' => $pengajuan->nominal ?? 'N/A',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toDateTimeString()
            ]);

            return redirect()->route('admin.pengajuan.penarikan.index')
                ->with('success', 'Pengajuan penarikan berhasil dihapus');
        } catch (\Exception $e) {
            Log::error('Gagal menghapus pengajuan penarikan', [
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name ?? 'admin',
                'pengajuan_id' => $id,
                'error' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toDateTimeString()
            ]);
            return redirect()->back()->with('error', 'Gagal menghapus pengajuan: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        // Log export attempt
        Log::info('Admin mencoba export data pengajuan penarikan ke Excel', [
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name ?? 'admin',
            'filters' => [
                'status' => $request->status ?? 'all',
                'date_from' => $request->date_from ?? 'none',
                'date_to' => $request->date_to ?? 'none',
                'search' => $request->search ?? 'none'
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString()
        ]);

        // Implementation for Excel export
        // This would use Laravel Excel package
        return redirect()->back()->with('info', 'Fitur export Excel akan segera tersedia');
    }

    public function exportPdf(Request $request)
    {
        // Log export attempt
        Log::info('Admin mencoba export data pengajuan penarikan ke PDF', [
            'admin_id' => auth()->id(),
            'admin_name' => auth()->user()->name ?? 'admin',
            'filters' => [
                'status' => $request->status ?? 'all',
                'date_from' => $request->date_from ?? 'none',
                'date_to' => $request->date_to ?? 'none',
                'search' => $request->search ?? 'none'
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toDateTimeString()
        ]);

        // Implementation for PDF export
        // This would use DomPDF or similar package
        return redirect()->back()->with('info', 'Fitur export PDF akan segera tersedia');
    }
}
