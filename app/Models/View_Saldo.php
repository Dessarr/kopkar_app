<?php

namespace App\Models;

class View_Saldo extends View_Base
{
    protected $table = 'v_saldo';
    
    protected $casts = [
        'jumlah_saldo' => 'decimal:2',
        'jumlah_penarikan' => 'decimal:2',
        'jumlah' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }

    public function jenisSimpanan()
    {
        return $this->belongsTo(jns_simpan::class, 'jenis_id', 'id');
    }
} 