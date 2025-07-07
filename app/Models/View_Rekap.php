<?php

namespace App\Models;

class View_Rekap extends View_Base
{
    protected $table = 'v_rekap';
    
    protected $casts = [
        'tgl_bayar' => 'date',
        'tagihan_hari_ini' => 'integer',
        'target_pokok' => 'decimal:2',
        'target_bunga' => 'decimal:2',
        'tagihan_masuk' => 'integer',
        'realisasi_pokok' => 'decimal:2',
        'realisasi_bunga' => 'decimal:2',
        'tagihan_bermasalah' => 'integer',
        'tidak_bayar_pokok' => 'decimal:2',
        'tidak_bayar_bunga' => 'decimal:2'
    ];
} 