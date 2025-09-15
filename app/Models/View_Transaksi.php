<?php

namespace App\Models;

use App\Models\NamaKasTbl;
use App\Models\jns_akun;

class View_Transaksi extends View_Base
{
    protected $table = 'v_transaksi';
    
    protected $casts = [
        'tgl' => 'datetime',
        'tgl_catat' => 'datetime',
        'kredit' => 'decimal:2',
        'debet' => 'decimal:2',
        'jumlah' => 'decimal:2'
    ];

    // Relasi untuk kas asal - menggunakan field yang benar
    public function kasAsal()
    {
        return $this->belongsTo(NamaKasTbl::class, 'dari_kas_id', 'id');
    }

    // Relasi untuk kas tujuan - menggunakan field yang benar
    public function kasTujuan()
    {
        return $this->belongsTo(NamaKasTbl::class, 'untuk_kas_id', 'id');
    }

    // Relasi untuk jenis akun - menggunakan field yang benar
    public function jenisAkun()
    {
        return $this->belongsTo(jns_akun::class, 'jns_trans', 'id');
    }

    // Accessor untuk mendapatkan nama kas asal
    public function getDariKasNamaAttribute()
    {
        return $this->kasAsal ? $this->kasAsal->nama : '-';
    }

    // Accessor untuk mendapatkan nama kas tujuan
    public function getUntukKasNamaAttribute()
    {
        return $this->kasTujuan ? $this->kasTujuan->nama : '-';
    }

    // Accessor untuk mendapatkan nama jenis akun
    public function getJenisAkunNamaAttribute()
    {
        return $this->jenisAkun ? $this->jenisAkun->jns_trans : '-';
    }

    // Scope untuk filter berdasarkan jenis transaksi
    public function scopePemasukan($query)
    {
        return $query->where('dk', 'D');
    }

    public function scopePengeluaran($query)
    {
        return $query->where('dk', 'K');
    }

    // Scope untuk filter berdasarkan kas
    public function scopeByKasAsal($query, $kasId)
    {
        return $query->where('dari_kas_id', $kasId);
    }

    public function scopeByKasTujuan($query, $kasId)
    {
        return $query->where('untuk_kas_id', $kasId);
    }
} 