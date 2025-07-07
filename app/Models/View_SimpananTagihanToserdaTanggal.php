<?php

namespace App\Models;

class View_SimpananTagihanToserdaTanggal extends View_Base
{
    protected $table = 'v_simpanan_tagihan_toserda_tanggal';
    
    protected $casts = [
        'tgl_transaksi' => 'date',
        'jumlah_bayar' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
} 