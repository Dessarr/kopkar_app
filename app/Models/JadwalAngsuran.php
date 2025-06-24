<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JadwalAngsuran extends Model
{
    protected $table = 'jadwal_angsuran';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'pinjaman_id', 'angsuran_ke', 'tanggal_jatuh_tempo', 'nominal_pokok', 'nominal_bunga', 'saldo_pinjaman', 'status', 'tanggal_bayar', 'created_at'
    ];

    // Relasi ke Pinjaman
    public function pinjaman()
    {
        return $this->belongsTo(Pinjaman::class, 'pinjaman_id');
    }

    // Relasi ke PembayaranAngsuran
    public function pembayaranAngsuran()
    {
        return $this->hasMany(PembayaranAngsuran::class, 'jadwal_angsuran_id');
    }
} 