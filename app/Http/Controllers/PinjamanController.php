<?php

namespace App\Http\Controllers;

use App\Models\Pinjaman;
use App\Models\Anggota;
use App\Models\JenisPinjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class PinjamanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pinjaman = Pinjaman::with(['anggota', 'jenisPinjaman', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json([
            'status' => 'success',
            'data' => $pinjaman
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $anggota = Anggota::where('status', 'aktif')->get();
        $jenisPinjaman = JenisPinjaman::where('is_active', true)->get();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'anggota' => $anggota,
                'jenis_pinjaman' => $jenisPinjaman
            ]
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'anggota_id' => 'required|exists:anggota,id',
            'jenis_pinjaman_id' => 'required|exists:jenis_pinjaman,id',
            'nominal_pengajuan' => 'required|numeric|min:0',
            'jangka_waktu' => 'required|integer|min:1',
            'tujuan_pinjaman' => 'nullable|string',
            'jaminan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Generate nomor pinjaman
            $nomorPinjaman = 'PJ' . date('Ymd') . str_pad(Pinjaman::count() + 1, 4, '0', STR_PAD_LEFT);
            
            $pinjaman = Pinjaman::create([
                'id' => Str::uuid(),
                'nomor_pinjaman' => $nomorPinjaman,
                'anggota_id' => $request->anggota_id,
                'jenis_pinjaman_id' => $request->jenis_pinjaman_id,
                'nominal_pengajuan' => $request->nominal_pengajuan,
                'jangka_waktu' => $request->jangka_waktu,
                'bunga_persen' => JenisPinjaman::find($request->jenis_pinjaman_id)->bunga_persen,
                'tanggal_pengajuan' => now()->toDateString(),
                'status' => 'draft',
                'tujuan_pinjaman' => $request->tujuan_pinjaman,
                'jaminan' => $request->jaminan,
                'created_by' => auth()->id() ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pengajuan pinjaman berhasil dibuat',
                'data' => $pinjaman->load(['anggota', 'jenisPinjaman'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat pengajuan pinjaman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pinjaman = Pinjaman::with(['anggota', 'jenisPinjaman', 'createdBy', 'jadwalAngsuran', 'pembayaranAngsuran'])
            ->find($id);

        if (!$pinjaman) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pinjaman tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $pinjaman
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pinjaman = Pinjaman::with(['anggota', 'jenisPinjaman'])->find($id);
        $jenisPinjaman = JenisPinjaman::where('is_active', true)->get();

        if (!$pinjaman) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pinjaman tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'pinjaman' => $pinjaman,
                'jenis_pinjaman' => $jenisPinjaman
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $pinjaman = Pinjaman::find($id);

        if (!$pinjaman) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pinjaman tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nominal_disetujui' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:draft,submitted,under_review,approved,rejected,disbursed,active,completed,overdue,written_off',
            'alasan_penolakan' => 'nullable|string',
            'catatan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $updateData = [];
            
            if ($request->has('nominal_disetujui')) {
                $updateData['nominal_disetujui'] = $request->nominal_disetujui;
            }
            
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
                
                // Update tanggal berdasarkan status
                if ($request->status === 'approved') {
                    $updateData['tanggal_persetujuan'] = now()->toDateString();
                } elseif ($request->status === 'disbursed') {
                    $updateData['tanggal_pencairan'] = now()->toDateString();
                    $updateData['tanggal_jatuh_tempo'] = now()->addMonths($pinjaman->jangka_waktu)->toDateString();
                }
            }
            
            if ($request->has('alasan_penolakan')) {
                $updateData['alasan_penolakan'] = $request->alasan_penolakan;
            }
            
            if ($request->has('catatan')) {
                $updateData['catatan'] = $request->catatan;
            }

            $pinjaman->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'Pinjaman berhasil diperbarui',
                'data' => $pinjaman->load(['anggota', 'jenisPinjaman'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui pinjaman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pinjaman = Pinjaman::find($id);

        if (!$pinjaman) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pinjaman tidak ditemukan'
            ], 404);
        }

        // Cek apakah sudah ada pembayaran
        if ($pinjaman->pembayaranAngsuran()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak dapat menghapus pinjaman yang sudah ada pembayaran'
            ], 400);
        }

        try {
            $pinjaman->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Pinjaman berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus pinjaman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get pinjaman by anggota
     */
    public function getByAnggota(string $anggotaId)
    {
        $pinjaman = Pinjaman::with(['jenisPinjaman', 'jadwalAngsuran'])
            ->where('anggota_id', $anggotaId)
            ->whereIn('status', ['active', 'overdue'])
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $pinjaman
        ]);
    }

    /**
     * Approve pinjaman
     */
    public function approve(Request $request, string $id)
    {
        $pinjaman = Pinjaman::find($id);

        if (!$pinjaman) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pinjaman tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nominal_disetujui' => 'required|numeric|min:0|max:' . $pinjaman->nominal_pengajuan,
            'catatan' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $pinjaman->update([
                'nominal_disetujui' => $request->nominal_disetujui,
                'status' => 'approved',
                'tanggal_persetujuan' => now()->toDateString(),
                'catatan' => $request->catatan,
                'admin_approval_id' => auth()->id() ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pinjaman berhasil disetujui',
                'data' => $pinjaman->load(['anggota', 'jenisPinjaman'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menyetujui pinjaman',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Reject pinjaman
     */
    public function reject(Request $request, string $id)
    {
        $pinjaman = Pinjaman::find($id);

        if (!$pinjaman) {
            return response()->json([
                'status' => 'error',
                'message' => 'Pinjaman tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'alasan_penolakan' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $pinjaman->update([
                'status' => 'rejected',
                'alasan_penolakan' => $request->alasan_penolakan,
                'admin_approval_id' => auth()->id() ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Pinjaman berhasil ditolak',
                'data' => $pinjaman->load(['anggota', 'jenisPinjaman'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menolak pinjaman',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
