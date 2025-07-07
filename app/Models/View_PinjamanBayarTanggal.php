<?php

namespace App\Models;

class View_PinjamanBayarTanggal extends View_Base
{
    protected $table = 'v_pinjaman_bayar_tanggal';
    
    protected $casts = [
        'tgl_bayar' => 'date',
        'jumlah_bayar' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
} 