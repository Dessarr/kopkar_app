<?php

namespace App\Models;

class View_TransaksiSimpanan extends View_Base
{
    protected $table = 'v_trans_sp';
    
    protected $casts = [
        'tgl_transaksi' => 'datetime',
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