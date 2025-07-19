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
        'tgl_bayar' => 'datetime',
        'jumlah' => 'float',
        'simpanan_wajib' => 'float',
        'simpanan_sukarela' => 'float',
        'simpanan_khusus_2' => 'float',
        'simpanan_pokok' => 'float',
        'total_billing' => 'float',
        'total_tagihan' => 'float',
    ];

    /**
     * Get the anggota associated with the billing.
     */
    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
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
     * Format: BILL-YYYYMM-KTP-TYPE
     */
    public static function generateBillingCode($bulan, $tahun, $noKtp, $jnsTransaksi)
    {
        $prefix = 'BILL';
        $period = $tahun . str_pad($bulan, 2, '0', STR_PAD_LEFT);
        $type = strtoupper(substr($jnsTransaksi, 0, 4));
        
        return "{$prefix}-{$period}-{$noKtp}-{$type}";
    }
}