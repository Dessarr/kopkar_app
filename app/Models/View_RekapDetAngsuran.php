<?php

namespace App\Models;

class View_RekapDetAngsuran extends View_Base
{
    protected $table = 'v_rekap_det_angsuran';
    
    protected $casts = [
        'tgl_pinjam' => 'date',
        'tgl_bayar' => 'date',
        'jumlah' => 'decimal:2',
        'jumlah_bunga' => 'decimal:2',
        'jumlah_bayar' => 'decimal:2',
        'bunga' => 'decimal:2',
        'ags_per_bulan' => 'decimal:2',
        'denda_rp' => 'decimal:2',
        'biaya_adm' => 'decimal:2',
        'sisa_pokok' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
} 