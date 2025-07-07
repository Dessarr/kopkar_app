<?php

namespace App\Models;

class View_Swajib extends View_Base
{
    protected $table = 'v_swajib';
    
    protected $casts = [
        'tgl_transaksi' => 'date',
        'jumlah' => 'decimal:2',
        'update_data' => 'datetime'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }

    public function jenisSimpanan()
    {
        return $this->belongsTo(jns_simpan::class, 'jenis_id', 'id');
    }

    public function kas()
    {
        return $this->belongsTo(DataKas::class, 'kas_id', 'id');
    }
} 