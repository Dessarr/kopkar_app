<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class identitas_koperasi extends Model
{
    protected $table = 'tbl_setting';
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
        if (in_array($key, ['modal_sendiri', 'modal_luar', 'jumlah_simpanan', 'jumlah_pinjaman'])) {
            return is_numeric($value) ? 'Rp ' . number_format($value, 0, ',', '.') : $value;
        }
        
        // Format date fields
        if (in_array($key, ['tgl_berdiri', 'tgl_pengesahan'])) {
            return $value ? \Carbon\Carbon::parse($value)->format('d/m/Y') : '-';
        }
        
        // Format number fields
        if (in_array($key, ['luas_tanah', 'luas_bangunan'])) {
            return is_numeric($value) ? number_format($value, 0, ',', '.') . ' m²' : $value;
        }
        
        // Format integer fields
        if (in_array($key, ['jumlah_anggota', 'jumlah_karyawan', 'jumlah_pengurus', 'jumlah_pengawas'])) {
            return is_numeric($value) ? number_format($value, 0, ',', '.') : $value;
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
}