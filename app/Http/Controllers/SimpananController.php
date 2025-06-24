<?php

namespace App\Http\Controllers;

use App\Models\Simpanan;
use App\Models\Anggota;
use App\Models\JenisSimpanan;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class SimpananController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $simpanan = Simpanan::with(['anggota', 'jenisSimpanan', 'createdBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json([
            'status' => 'success',
            'data' => $simpanan
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $anggota = Anggota::where('status', 'aktif')->get();
        $jenisSimpanan = JenisSimpanan::where('is_active', true)->get();
        
        return response()->json([
            'status' => 'success',
            'data' => [
                'anggota' => $anggota,
                'jenis_simpanan' => $jenisSimpanan
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
            'jenis_simpanan_id' => 'required|exists:jenis_simpanan,id',
            'nomor_rekening' => 'required|unique:simpanan,nomor_rekening|max:20',
            'saldo_awal' => 'required|numeric|min:0',
            'status' => 'nullable|in:aktif,tutup,suspend'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $simpanan = Simpanan::create([
                'id' => Str::uuid(),
                'anggota_id' => $request->anggota_id,
                'jenis_simpanan_id' => $request->jenis_simpanan_id,
                'nomor_rekening' => $request->nomor_rekening,
                'saldo_awal' => $request->saldo_awal,
                'saldo_akhir' => $request->saldo_awal,
                'tanggal_buka' => now()->toDateString(),
                'status' => $request->status ?? 'aktif',
                'created_by' => auth()->id() ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Simpanan berhasil dibuat',
                'data' => $simpanan->load(['anggota', 'jenisSimpanan'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal membuat simpanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $simpanan = Simpanan::with(['anggota', 'jenisSimpanan', 'createdBy', 'transaksiSimpanan'])
            ->find($id);

        if (!$simpanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Simpanan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $simpanan
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $simpanan = Simpanan::with(['anggota', 'jenisSimpanan'])->find($id);
        $jenisSimpanan = JenisSimpanan::where('is_active', true)->get();

        if (!$simpanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Simpanan tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'simpanan' => $simpanan,
                'jenis_simpanan' => $jenisSimpanan
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $simpanan = Simpanan::find($id);

        if (!$simpanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Simpanan tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'jenis_simpanan_id' => 'nullable|exists:jenis_simpanan,id',
            'nomor_rekening' => 'nullable|max:20|unique:simpanan,nomor_rekening,' . $id,
            'status' => 'nullable|in:aktif,tutup,suspend'
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
            
            if ($request->has('jenis_simpanan_id')) {
                $updateData['jenis_simpanan_id'] = $request->jenis_simpanan_id;
            }
            
            if ($request->has('nomor_rekening')) {
                $updateData['nomor_rekening'] = $request->nomor_rekening;
            }
            
            if ($request->has('status')) {
                $updateData['status'] = $request->status;
                if ($request->status === 'tutup') {
                    $updateData['tanggal_tutup'] = now()->toDateString();
                }
            }

            $simpanan->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'Simpanan berhasil diperbarui',
                'data' => $simpanan->load(['anggota', 'jenisSimpanan'])
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui simpanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $simpanan = Simpanan::find($id);

        if (!$simpanan) {
            return response()->json([
                'status' => 'error',
                'message' => 'Simpanan tidak ditemukan'
            ], 404);
        }

        // Cek apakah ada transaksi
        if ($simpanan->transaksiSimpanan()->count() > 0) {
            return response()->json([
                'status' => 'error',
                'message' => 'Tidak dapat menghapus simpanan yang memiliki transaksi'
            ], 400);
        }

        try {
            $simpanan->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Simpanan berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus simpanan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get simpanan by anggota
     */
    public function getByAnggota(string $anggotaId)
    {
        $simpanan = Simpanan::with(['jenisSimpanan', 'transaksiSimpanan'])
            ->where('anggota_id', $anggotaId)
            ->where('status', 'aktif')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $simpanan
        ]);
    }
}
