<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class View_Toserda extends Model
{
    protected $table = 'v_toserda';
    
    protected $casts = [
        'tgl_transaksi' => 'datetime',
        'jumlah' => 'decimal:2',
        'update_data' => 'datetime'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }

    public function kas()
    {
        return $this->belongsTo(DataKas::class, 'kas_id', 'id');
    }

    public function jenisAkun()
    {
        return $this->belongsTo(jns_akun::class, 'id_akun', 'id');
    }

    public function barang()
    {
        return $this->belongsTo(data_barang::class, 'jenis_id', 'id');
    }
} 