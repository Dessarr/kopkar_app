<?php

namespace App\Models;

class View_Tagihan extends View_Base
{
    protected $table = 'v_tagihan';
    
    protected $casts = [
        'tgl_transaksi' => 'date',
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