<?php

namespace App\Models;

class View_PengeluaranPinjaman extends View_Base
{
    protected $table = 'v_pengeluaran_pinjaman';
    
    protected $casts = [
        'tgl_pinjam' => 'date',
        'jumlah' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
} 