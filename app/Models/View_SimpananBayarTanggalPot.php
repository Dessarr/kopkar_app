<?php

namespace App\Models;

class View_SimpananBayarTanggalPot extends View_Base
{
    protected $table = 'v_simpanan_bayar_tanggal_pot';
    
    protected $casts = [
        'tgl_transaksi' => 'date',
        'jumlah_bayar' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
} 