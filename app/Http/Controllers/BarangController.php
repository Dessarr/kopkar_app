<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class BarangController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $barang = Barang::where('is_deleted', false)
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json([
            'status' => 'success',
            'data' => $barang
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Form create barang'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama' => 'required|max:255',
            'tipe' => 'nullable|max:50',
            'merk' => 'nullable|max:50',
            'harga' => 'required|numeric|min:0',
            'jumlah' => 'required|integer|min:0',
            'keterangan' => 'nullable|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $barang = Barang::create([
                'id' => Str::uuid(),
                'nama' => $request->nama,
                'tipe' => $request->tipe,
                'merk' => $request->merk,
                'harga' => $request->harga,
                'jumlah' => $request->jumlah,
                'keterangan' => $request->keterangan
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Barang berhasil ditambahkan',
                'data' => $barang
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan barang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $barang = Barang::where('is_deleted', false)->find($id);

        if (!$barang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $barang
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $barang = Barang::where('is_deleted', false)->find($id);

        if (!$barang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $barang
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $barang = Barang::where('is_deleted', false)->find($id);

        if (!$barang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'nullable|max:255',
            'tipe' => 'nullable|max:50',
            'merk' => 'nullable|max:50',
            'harga' => 'nullable|numeric|min:0',
            'jumlah' => 'nullable|integer|min:0',
            'keterangan' => 'nullable|max:255'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $barang->update($request->only(['nama', 'tipe', 'merk', 'harga', 'jumlah', 'keterangan']));

            return response()->json([
                'status' => 'success',
                'message' => 'Barang berhasil diperbarui',
                'data' => $barang
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui barang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $barang = Barang::where('is_deleted', false)->find($id);

        if (!$barang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        try {
            $barang->update(['is_deleted' => true]);

            return response()->json([
                'status' => 'success',
                'message' => 'Barang berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menghapus barang',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update stok barang
     */
    public function updateStok(Request $request, string $id)
    {
        $barang = Barang::where('is_deleted', false)->find($id);

        if (!$barang) {
            return response()->json([
                'status' => 'error',
                'message' => 'Barang tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'jumlah' => 'required|integer',
            'tipe' => 'required|in:tambah,kurang'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            if ($request->tipe === 'tambah') {
                $barang->increment('jumlah', $request->jumlah);
            } else {
                if ($barang->jumlah < $request->jumlah) {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Stok tidak mencukupi'
                    ], 400);
                }
                $barang->decrement('jumlah', $request->jumlah);
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Stok berhasil diperbarui',
                'data' => $barang->fresh()
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui stok',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
