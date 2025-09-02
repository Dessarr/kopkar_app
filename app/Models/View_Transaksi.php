<?php

namespace App\Models;

use App\Models\NamaKasTbl;
use App\Models\jns_akun;

class View_Transaksi extends View_Base
{
    protected $table = 'v_transaksi';
    
    protected $casts = [
        'tgl' => 'datetime',
        'kredit' => 'decimal:2',
        'debet' => 'decimal:2'
    ];

    public function kasAsal()
    {
        return $this->belongsTo(NamaKasTbl::class, 'dari_kas', 'id');
    }

    public function kasTujuan()
    {
        return $this->belongsTo(NamaKasTbl::class, 'untuk_kas', 'id');
    }

    public function jenisAkun()
    {
        return $this->belongsTo(jns_akun::class, 'transaksi', 'id');
    }
} 