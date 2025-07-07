<?php

namespace App\Models;

class View_Transaksi extends View_Base
{
    protected $table = 'v_transaksi';
    
    protected $casts = [
        'tgl' => 'date',
        'kredit' => 'decimal:2',
        'debet' => 'decimal:2'
    ];

    public function kasAsal()
    {
        return $this->belongsTo(DataKas::class, 'dari_kas', 'id');
    }

    public function kasTujuan()
    {
        return $this->belongsTo(DataKas::class, 'untuk_kas', 'id');
    }
} 