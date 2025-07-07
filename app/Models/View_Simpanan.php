<?php

namespace App\Models;

class View_Simpanan extends View_Base
{
    protected $table = 'v_simpanan';
    
    protected $casts = [
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