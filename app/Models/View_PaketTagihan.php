<?php

namespace App\Models;

class View_PaketTagihan extends View_Base
{
    protected $table = 'v_paket_tagihan';
    
    protected $casts = [
        'tgl_transaksi' => 'date',
        'tagihan_simpanan_wajib' => 'decimal:2',
        'tagihan_simpanan_sukarela' => 'decimal:2',
        'tagihan_simpanan_khusus_2' => 'decimal:2',
        'total_tagihan_simpanan' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
} 