<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\data_pengajuan;
use Illuminate\Support\Facades\Log;

class DtaPengajuanController extends Controller
{
    public function index()
    {
        $dataPengajuan = data_pengajuan::orderByDesc('tgl_input')->paginate(10);
        return view('pinjaman.data_pengajuan', compact('dataPengajuan'));
    }

    public function approve(string $id, Request $request)
    {
        $pengajuan = data_pengajuan::findOrFail($id);
        $pengajuan->status = 1; // Disetujui
        $pengajuan->alasan = $request->input('alasan', '');
        $pengajuan->tgl_update = now();
        $pengajuan->save();
        Log::info('Admin menyetujui pengajuan', ['id'=>$id, 'ajuan_id'=>$pengajuan->ajuan_id]);
        return back()->with('success', 'Pengajuan disetujui');
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

    public function cetak(string $id)
    {
        $pengajuan = data_pengajuan::findOrFail($id);
        return view('pinjaman.cetak_pengajuan_admin', compact('pengajuan'));
    }
}