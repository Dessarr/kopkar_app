<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblTransToserda extends Model
{
    protected $table = 'tbl_trans_toserda';
    protected $primaryKey = 'id';

    protected $fillable = [
        'tgl_transaksi',
        'no_ktp',
        'anggota_id',
        'jenis_id',
        'jumlah',
        'keterangan',
        'dk',
        'kas_id',
        'jns_trans',
        'update_data',
        'user_name',
    ];

    protected $casts = [
        'tgl_transaksi' => 'datetime',
        'jumlah' => 'decimal:2',
        'update_data' => 'datetime',
    ];

    protected $attributes = [
        'dk' => 'D',
        'jns_trans' => 'Toserda'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'anggota_id', 'id');
    }

    public function barang()
    {
        return $this->belongsTo(data_barang::class, 'jenis_id', 'id');
    }

    public function kas()
    {
        return $this->belongsTo(DataKas::class, 'kas_id', 'id');
    }

    public function jenisAkun()
    {
        return $this->belongsTo(jns_akun::class, 'id_akun', 'id');
    }

    public function billing()
    {
        return $this->hasMany(billing::class, 'no_ktp', 'no_ktp');
    }

    public function scopeForPeriod($query, $bulan, $tahun)
    {
        return $query->whereMonth('tgl_transaksi', $bulan)
                    ->whereYear('tgl_transaksi', $tahun);
    }

    public function scopeDebit($query)
    {
        return $query->where('dk', 'D');
    }

    public function scopeKredit($query)
    {
        return $query->where('dk', 'K');
    }
} 