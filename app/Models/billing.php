<?php

namespace App\Models;

use illuminate\database\Eloquent\Model;

class billing extends Model
{
    protected $table = 'tbl_anggota';
    protected $fillable = [
        'nama',
        'no_ktp',
        'id_tagihan',
        'simpanan_wajib',
        'simpanan_sukarela',
        'simpanan_khusus_2',
        'id_cabang'
    ];

    

    public $timestamps = false;

    // Buat kolom total billing
    public function getTotalBillingAttribute()
    {
        return
            ($this->simpanan_wajib ?? 0) +
            ($this->simpanan_sukarela ?? 0) +
            ($this->simpanan_khusus_2 ?? 0);
    }
}   