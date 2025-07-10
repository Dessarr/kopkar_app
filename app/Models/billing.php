<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Billing extends Model
{
    protected $table = 'billing';
    protected $primaryKey = 'id_billing';
    public $incrementing = false; // karena id_billing bukan auto-increment
    protected $keyType = 'string';

    protected $fillable = [
        'id_billing',
        'bulan_tahun',
        'id_anggota',
        'total_tagihan',
        'status',

        'nama',
        'no_ktp',
        'id_tagihan',
        'simpanan_wajib',
        'simpanan_sukarela',
        'simpanan_khusus_2',
        'jns_trans',
        'id_cabang',
        'status_bayar'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'id_anggota', 'no_ktp');
    }
}