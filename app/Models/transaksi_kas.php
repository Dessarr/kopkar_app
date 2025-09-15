<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\NamaKasTbl;
use App\Models\jns_akun;

class transaksi_kas extends Model
{
    protected $table = 'tbl_trans_kas';
    public $timestamps = false; // Disable timestamps karena table tidak memiliki created_at dan updated_at

    protected $fillable = [
        'tgl_catat',
        'jumlah',
        'keterangan',
        'akun',
        'dari_kas_id',
        'untuk_kas_id',
        'jns_trans',
        'dk',
        'no_polisi',
        'update_data',
        'id_cabang',
        'user_name',
    ];

    protected $casts = [
        'tgl_catat' => 'datetime',
        'update_data' => 'datetime',
        'jumlah' => 'decimal:2',
    ];

    public function dariKas()
    {
         return $this->belongsTo(NamaKasTbl::class, 'dari_kas_id', 'id');
    }

    public function untukKas()
    {
        return $this->belongsTo(NamaKasTbl::class, 'untuk_kas_id', 'id');
    }

    // Alias untuk kompatibilitas dengan view
    public function kasTujuan()
    {
        return $this->untukKas();
    }

    public function kasAsal()
    {
        return $this->dariKas();
    }

    public function jenisAkun()
    {
        return $this->belongsTo(jns_akun::class, 'jns_trans', 'id');
    }
}