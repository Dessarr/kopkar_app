<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\suku_bunga;

class SukuBungaController extends Controller
{
    public function index()
    {
        $sukuBunga = suku_bunga::all();
        return view('settings.suku_bunga', compact('sukuBunga'));
    }

    public function update(Request $request)
    {
        $fieldKeys = [
            'bg_tab', 'bg_pinjam', 'biaya_adm', 'denda', 'denda_hari', 'dana_cadangan',
            'jasa_anggota', 'dana_pengurus', 'dana_karyawan', 'dana_pend', 'dana_sosial',
            'jasa_usaha', 'jasa_modal', 'pjk_pph', 'pinjaman_bunga_tipe', 'bunga_biasa', 'bunga_barang'
        ];
        foreach ($fieldKeys as $key) {
            $value = $request->input($key, '-');
            $rows = suku_bunga::where('opsi_key', $key)->get();
            if ($rows->count() > 0) {
                foreach ($rows as $row) {
                    $row->opsi_val = $value;
                    $row->save();
                }
            } else {
                suku_bunga::create([
                    'opsi_key' => $key,
                    'opsi_val' => $value,
                    'id_cabang' => '1', // atau sesuaikan kebutuhan
                ]);
            }
        }
        return redirect()->route('settings.suku_bunga')->with('success', 'Data suku bunga berhasil diperbarui!');
    }
}