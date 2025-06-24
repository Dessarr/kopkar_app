<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Simpanan extends Model
{
    protected $table = 'simpanan';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'anggota_id', 'jenis_simpanan_id', 'nomor_rekening', 'saldo_awal', 'saldo_akhir', 'tanggal_buka', 'tanggal_tutup', 'status', 'created_at', 'updated_at', 'created_by'
    ];

    // Relasi ke Anggota
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    // Relasi ke JenisSimpanan
    public function jenisSimpanan()
    {
        return $this->belongsTo(JenisSimpanan::class, 'jenis_simpanan_id');
    }

    // Relasi ke TransaksiSimpanan
    public function transaksiSimpanan()
    {
        return $this->hasMany(TransaksiSimpanan::class, 'simpanan_id');
    }

    // Relasi ke Akun (created_by)
    public function createdBy()
    {
        return $this->belongsTo(Akun::class, 'created_by');
    }
} 