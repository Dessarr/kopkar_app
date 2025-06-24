<?php

namespace App\Http\Controllers;

use App\Models\Kas;
use App\Models\TransaksiKas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class KasController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kas = Kas::where('is_active', true)->get();
        $transaksi = TransaksiKas::with(['kas', 'createdBy'])
            ->orderBy('tanggal_transaksi', 'desc')
            ->paginate(10);

        return view('kas.index', compact('kas', 'transaksi'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $listKas = Kas::where('is_active', true)->get();
        return view('kas.create', compact('listKas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kas_id' => 'required|exists:kas,id',
            'jenis_transaksi' => 'required|in:pemasukan,pengeluaran,transfer',
            'jumlah' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string|max:255',
            'untuk_kas_id' => 'required_if:jenis_transaksi,transfer|exists:kas,id',
        ]);

        try {
            DB::beginTransaction();

            $transaksi = new TransaksiKas();
            $transaksi->kas_id = $request->kas_id;
            $transaksi->jenis_transaksi = $request->jenis_transaksi;
            $transaksi->jumlah = $request->jumlah;
            $transaksi->keterangan = $request->keterangan;
            $transaksi->created_by = Auth::id();

            if ($request->jenis_transaksi === 'transfer') {
                $transaksi->dari_kas_id = $request->kas_id;
                $transaksi->untuk_kas_id = $request->untuk_kas_id;
            }

            $transaksi->save();

            DB::commit();
            return redirect()->route('kas.index')->with('success', 'Transaksi kas berhasil disimpan');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $transaksi = TransaksiKas::with(['kas', 'dariKas', 'untukKas', 'createdBy'])
            ->findOrFail($id);
        return view('kas.show', compact('transaksi'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function report(Request $request)
    {
        $request->validate([
            'tanggal_mulai' => 'nullable|date',
            'tanggal_selesai' => 'nullable|date|after_or_equal:tanggal_mulai',
            'kas_id' => 'nullable|exists:kas,id'
        ]);

        $query = TransaksiKas::with(['kas', 'createdBy']);

        if ($request->tanggal_mulai) {
            $query->whereDate('tanggal_transaksi', '>=', $request->tanggal_mulai);
        }
        if ($request->tanggal_selesai) {
            $query->whereDate('tanggal_transaksi', '<=', $request->tanggal_selesai);
        }
        if ($request->kas_id) {
            $query->where('kas_id', $request->kas_id);
        }

        $transaksi = $query->orderBy('tanggal_transaksi', 'desc')->get();
        $listKas = Kas::where('is_active', true)->get();

        return view('kas.report', compact('transaksi', 'listKas'));
    }
}
