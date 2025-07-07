<?php

namespace App\Models;

class View_RekapSimpanan extends View_Base
{
    protected $table = 'v_rekap_simpanan';
    
    protected $casts = [
        'tgl_transaksi' => 'date',
        'Debet' => 'decimal:2',
        'Kredit' => 'decimal:2'
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