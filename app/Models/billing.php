<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class billing extends Model
{

    use HasFactory;

    protected $table = 'billing';
    protected $primaryKey = 'id';
    protected $guarded = ['id'];
    public $timestamps = true;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'simpanan_wajib' => 'float',
        'simpanan_khusus_1' => 'float',
        'simpanan_sukarela' => 'float',
        'simpanan_khusus_2' => 'float',
        'tab_perumahan' => 'float',
        'simpanan_pokok' => 'float',
        'total_tagihan' => 'float',
        'id_anggota' => 'integer',
    ];

    /**
     * Get the anggota associated with the billing.
     */
    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'id_anggota', 'id');
    }

    /**
     * Get the toserda transaction associated with the billing.
     */
    public function toserda()
    {
        return $this->belongsTo(TblTransToserda::class, 'id_transaksi', 'id')
            ->where('jns_transaksi', 'toserda');
    }
    
    /**
     * Generate a unique billing code.
     * Format: BILL + MMYY + ID_ANGGOTA (4 digit)
     * Example: BILL09250001 (September 2025, Anggota ID 1)
     */
    public static function generateBillingCode($bulan, $tahun, $idAnggota)
    {
        $prefix = 'BILL';
        $period = str_pad($bulan, 2, '0', STR_PAD_LEFT) . substr($tahun, -2);
        $anggotaId = str_pad($idAnggota, 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}{$period}{$anggotaId}";
    }
}