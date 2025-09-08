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
        // Validation rules - hanya untuk identitas dasar dan alamat kontak
        $rules = [
            'nama_lembaga' => 'required|string|max:255',
            'nama_ketua' => 'required|string|max:255',
            'alamat' => 'required|string|max:500',
            'kota' => 'required|string|max:100',
            'email' => 'nullable|email|max:255',
            'web' => 'nullable|url|max:255',
            'hp_ketua' => 'nullable|string|max:20',
            'telepon' => 'nullable|string|max:20',
        ];

        $validated = $request->validate($rules);

        try {
            \DB::beginTransaction();
            
            foreach($validated as $key => $value) {
                $setting = identitas_koperasi::where('opsi_key', $key)->first();
                if($setting) {
                    $setting->opsi_val = $value;
                    $setting->save();
                } else {
                    identitas_koperasi::create([
                        'opsi_key' => $key,
                        'opsi_val' => $value,
                        'id_cabang' => 1
                    ]);
                }
            }
            
            \DB::commit();
            return redirect()->route('settings.identitas_koperasi')->with('success', 'Data identitas koperasi berhasil diperbarui!');
            
        } catch (\Exception $e) {
            \DB::rollback();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }
}