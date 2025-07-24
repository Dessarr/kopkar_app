<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BillingProcess extends Model
{
    use HasFactory;

    protected $table = 'billing_process';
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
     * Get the user who processed this billing.
     */
    public function processedBy()
    {
        return $this->belongsTo(Admin::class, 'processed_by', 'id');
    }

    /**
     * Convert this processed billing back to an active billing.
     */
    public function revertToBilling()
    {
        $billing = new billing();
        $billing->fill($this->getAttributes());
        $billing->status = 'N';
        $billing->status_bayar = 'Belum Lunas';
        $billing->save();

        return $billing;
    }
} 