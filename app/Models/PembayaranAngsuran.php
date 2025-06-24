<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PembayaranAngsuran extends Model
{
    protected $table = 'pembayaran_angsuran';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'pinjaman_id', 'jadwal_angsuran_id', 'nominal_dibayar', 'nominal_pokok', 'nominal_bunga', 'denda', 'tanggal_bayar', 'metode_bayar', 'keterangan', 'bukti_bayar', 'processed_by', 'created_at'
    ];

    // Relasi ke Pinjaman
    public function pinjaman()
    {
        return $this->belongsTo(Pinjaman::class, 'pinjaman_id');
    }

    // Relasi ke JadwalAngsuran
    public function jadwalAngsuran()
    {
        return $this->belongsTo(JadwalAngsuran::class, 'jadwal_angsuran_id');
    }

    // Relasi ke Akun (processed_by)
    public function processedBy()
    {
        return $this->belongsTo(Akun::class, 'processed_by');
    }
} 