<?php

namespace App\Http\Controllers;

use App\Models\Anggota;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;

class AnggotaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $anggota = Anggota::with(['createdBy', 'updatedBy'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);
        
        return response()->json([
            'status' => 'success',
            'data' => $anggota
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Form create anggota'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'member_code' => 'required|unique:anggota,member_code|max:20',
            'nama' => 'required|max:100',
            'alamat' => 'nullable',
            'no_ktp' => 'nullable|unique:anggota,no_ktp|max:20',
            'tempat_lahir' => 'nullable|max:50',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'phone' => 'nullable|max:20',
            'email' => 'nullable|email|max:100',
            'pekerjaan' => 'nullable|max:50',
            'gaji_pokok' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:aktif,non_aktif,suspend,keluar'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $anggota = Anggota::create([
                'id' => Str::uuid(),
                'member_code' => $request->member_code,
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'no_ktp' => $request->no_ktp,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'phone' => $request->phone,
                'email' => $request->email,
                'pekerjaan' => $request->pekerjaan,
                'tanggal_gabung' => now()->toDateString(),
                'gaji_pokok' => $request->gaji_pokok ?? 0,
                'status' => $request->status ?? 'aktif',
                'created_by' => auth()->id() ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Anggota berhasil ditambahkan',
                'data' => $anggota
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal menambahkan anggota',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $anggota = Anggota::with(['createdBy', 'updatedBy', 'simpanan', 'pinjaman'])
            ->find($id);

        if (!$anggota) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anggota tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $anggota
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $anggota = Anggota::find($id);

        if (!$anggota) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anggota tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $anggota
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $anggota = Anggota::find($id);

        if (!$anggota) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anggota tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'member_code' => 'required|max:20|unique:anggota,member_code,' . $id,
            'nama' => 'required|max:100',
            'alamat' => 'nullable',
            'no_ktp' => 'nullable|max:20|unique:anggota,no_ktp,' . $id,
            'tempat_lahir' => 'nullable|max:50',
            'tanggal_lahir' => 'nullable|date',
            'jenis_kelamin' => 'nullable|in:L,P',
            'phone' => 'nullable|max:20',
            'email' => 'nullable|email|max:100',
            'pekerjaan' => 'nullable|max:50',
            'gaji_pokok' => 'nullable|numeric|min:0',
            'status' => 'nullable|in:aktif,non_aktif,suspend,keluar'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $anggota->update([
                'member_code' => $request->member_code,
                'nama' => $request->nama,
                'alamat' => $request->alamat,
                'no_ktp' => $request->no_ktp,
                'tempat_lahir' => $request->tempat_lahir,
                'tanggal_lahir' => $request->tanggal_lahir,
                'jenis_kelamin' => $request->jenis_kelamin,
                'phone' => $request->phone,
                'email' => $request->email,
                'pekerjaan' => $request->pekerjaan,
                'gaji_pokok' => $request->gaji_pokok ?? $anggota->gaji_pokok,
                'status' => $request->status ?? $anggota->status,
                'updated_by' => auth()->id() ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Anggota berhasil diperbarui',
                'data' => $anggota
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal memperbarui anggota',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $anggota = Anggota::find($id);

        if (!$anggota) {
            return response()->json([
                'status' => 'error',
                'message' => 'Anggota tidak ditemukan'
            ], 404);
        }

        try {
            // Soft delete - update status menjadi keluar
            $anggota->update([
                'status' => 'keluar',
                'tanggal_keluar' => now()->toDateString(),
                'updated_by' => auth()->id() ?? null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Anggota berhasil dikeluarkan'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengeluarkan anggota',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
