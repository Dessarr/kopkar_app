<?php

namespace App\Models;

class View_SimpananTagihanTanggal extends View_Base
{
    protected $table = 'v_simpanan_tagihan_tanggal';
    
    protected $casts = [
        'tgl_transaksi' => 'date',
        'jumlah_tagihan' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
} 