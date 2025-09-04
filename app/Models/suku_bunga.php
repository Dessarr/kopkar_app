<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class suku_bunga extends Model
{
    protected $table = 'suku_bunga';
    protected $fillable = ['id', 'opsi_key', 'opsi_val', 'id_cabang'];
    public $timestamps = false;
    
    protected $casts = [
        'id_cabang' => 'integer',
    ];

    // Accessor untuk format nilai berdasarkan tipe
    public function getFormattedValueAttribute()
    {
        $key = $this->opsi_key;
        $value = $this->opsi_val;
        
        // Format currency fields
        if (in_array($key, ['biaya_adm', 'denda', 'denda_hari'])) {
            return is_numeric($value) ? 'Rp ' . number_format($value, 0, ',', '.') : $value;
        }
        
        // Format percentage fields
        if (in_array($key, ['bg_tab', 'bg_pinjam', 'bunga_biasa', 'bunga_barang', 'dana_cadangan', 
                           'jasa_anggota', 'dana_pengurus', 'dana_karyawan', 'dana_pend', 'dana_sosial',
                           'jasa_usaha', 'jasa_modal', 'pjk_pph'])) {
            return is_numeric($value) ? number_format($value, 2, ',', '.') . '%' : $value;
        }
        
        return $value ?: '-';
    }

    // Scope untuk mencari berdasarkan key
    public function scopeByKey($query, $key)
    {
        return $query->where('opsi_key', $key);
    }

    // Scope untuk cabang tertentu
    public function scopeByCabang($query, $cabangId)
    {
        return $query->where('id_cabang', $cabangId);
    }

    // Method untuk mendapatkan nilai berdasarkan key
    public static function getValue($key, $default = null)
    {
        $setting = static::where('opsi_key', $key)->first();
        return $setting ? $setting->opsi_val : $default;
    }

    // Method untuk mengatur nilai berdasarkan key
    public static function setValue($key, $value, $cabangId = 1)
    {
        return static::updateOrCreate(
            ['opsi_key' => $key, 'id_cabang' => $cabangId],
            ['opsi_val' => $value]
        );
    }

    // Method untuk mendapatkan semua suku bunga sebagai array
    public static function getAllAsArray($cabangId = 1)
    {
        return static::where('id_cabang', $cabangId)
                    ->pluck('opsi_val', 'opsi_key')
                    ->toArray();
    }
}
