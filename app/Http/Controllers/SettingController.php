<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\identitas_koperasi;

class SettingController extends Controller
{
    public function index()
    {
        $identitasKoperasi = identitas_koperasi::all();
        return view('settings.identitas_koperasi', compact('identitasKoperasi'));
    }

    public function update(Request $request)
    {
        $fieldKeys = [
            'nama_lembaga', 'nama_ketua', 'hp_ketua', 'alamat', 'telepon', 'kota', 'email', 'web',
            'npwp', 'no_badan_hukum', 'tgl_berdiri', 'tgl_pengesahan', 'bidang_usaha', 'status_kantor',
            'status_kepemilikan', 'luas_tanah', 'luas_bangunan', 'modal_sendiri', 'modal_luar',
            'jumlah_anggota', 'jumlah_karyawan', 'jumlah_pengurus', 'jumlah_pengawas', 'jumlah_simpanan', 'jumlah_pinjaman'
        ];
        foreach ($fieldKeys as $key) {
            $value = $request->input($key, '-');
            $rows = identitas_koperasi::where('opsi_key', $key)->get();
            if ($rows->count() > 0) {
                foreach ($rows as $row) {
                    $row->opsi_val = $value;
                    $row->save();
                }
            } else {
                identitas_koperasi::create([
                    'opsi_key' => $key,
                    'opsi_val' => $value,
                    'id_cabang' => '1', // atau sesuaikan kebutuhan
                ]);
            }
        }
        return redirect()->route('settings.identitas_koperasi')->with('success', 'Data identitas koperasi berhasil diperbarui!');
    }
}