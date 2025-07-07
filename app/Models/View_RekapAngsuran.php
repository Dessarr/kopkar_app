<?php

namespace App\Models;

class View_RekapAngsuran extends View_Base
{
    protected $table = 'v_rekap_angsuran';
    
    protected $casts = [
        'tgl_bayar' => 'date',
        'jumlah_bayar' => 'decimal:2'
    ];

    public function pinjaman()
    {
        return $this->belongsTo(View_HitungPinjaman::class, 'pinjam_id', 'id');
    }
} 