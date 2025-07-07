<?php

namespace App\Models;

class View_HitungPinjaman3 extends View_Base
{
    protected $table = 'v_hitung_pinjaman_3';
    
    protected $casts = [
        'tgl_pinjam' => 'date',
        'jumlah' => 'decimal:2',
        'jumlah_angsuran' => 'decimal:2',
        'bunga_rp' => 'decimal:2',
        'bunga' => 'decimal:2',
        'biaya_adm' => 'decimal:2',
        'denda_rp' => 'decimal:2',
        'pokok_angsuran' => 'decimal:2',
        'pokok_bunga' => 'decimal:2',
        'bunga_pinjaman' => 'decimal:2',
        'ags_per_bulan' => 'decimal:2',
        'total_bayar' => 'decimal:2',
        'sisa_pokok' => 'decimal:2',
        'tempo' => 'date',
        'tagihan' => 'decimal:2',
        'bunga_ags' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }

    public function kas()
    {
        return $this->belongsTo(DataKas::class, 'kas_id', 'id');
    }
} 