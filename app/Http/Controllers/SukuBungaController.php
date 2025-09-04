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
        // Validation rules
        $rules = [
            'bg_tab' => 'required|numeric|min:0|max:100',
            'bg_pinjam' => 'required|numeric|min:0|max:100',
            'biaya_adm' => 'nullable|numeric|min:0',
            'denda' => 'nullable|numeric|min:0',
            'denda_hari' => 'nullable|numeric|min:0',
            'dana_cadangan' => 'nullable|numeric|min:0|max:100',
            'jasa_anggota' => 'nullable|numeric|min:0|max:100',
            'dana_pengurus' => 'nullable|numeric|min:0|max:100',
            'dana_karyawan' => 'nullable|numeric|min:0|max:100',
            'dana_pend' => 'nullable|numeric|min:0|max:100',
            'dana_sosial' => 'nullable|numeric|min:0|max:100',
            'jasa_usaha' => 'nullable|numeric|min:0|max:100',
            'jasa_modal' => 'nullable|numeric|min:0|max:100',
            'pjk_pph' => 'nullable|numeric|min:0|max:100',
            'pinjaman_bunga_tipe' => 'nullable|string|max:100',
            'bunga_biasa' => 'nullable|numeric|min:0|max:100',
            'bunga_barang' => 'nullable|numeric|min:0|max:100',
        ];

        $validated = $request->validate($rules);

        try {
            \DB::beginTransaction();
            
            foreach($validated as $key => $value) {
                // Clean currency values
                if (in_array($key, ['biaya_adm', 'denda', 'denda_hari'])) {
                    $value = str_replace(['.', ','], '', $value);
                }
                
                $setting = suku_bunga::where('opsi_key', $key)->first();
                if($setting) {
                    $setting->opsi_val = $value;
                    $setting->save();
                } else {
                    suku_bunga::create([
                        'opsi_key' => $key,
                        'opsi_val' => $value,
                        'id_cabang' => 1
                    ]);
                }
            }
            
            \DB::commit();
            return redirect()->route('settings.suku_bunga')->with('success', 'Data suku bunga berhasil diperbarui!');
            
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }
}