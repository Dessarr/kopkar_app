<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pinjaman extends Model
{
    protected $table = 'pinjaman';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'nomor_pinjaman', 'anggota_id', 'jenis_pinjaman_id', 'nominal_pengajuan', 'nominal_disetujui', 'jangka_waktu', 'bunga_persen', 'tanggal_pengajuan', 'tanggal_persetujuan', 'tanggal_pencairan', 'tanggal_jatuh_tempo', 'status', 'alasan_penolakan', 'tujuan_pinjaman', 'jaminan', 'catatan', 'admin_review_id', 'admin_approval_id', 'created_at', 'updated_at', 'created_by'
    ];

    // Relasi ke Anggota
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    // Relasi ke JenisPinjaman
    public function jenisPinjaman()
    {
        return $this->belongsTo(JenisPinjaman::class, 'jenis_pinjaman_id');
    }

    // Relasi ke JadwalAngsuran
    public function jadwalAngsuran()
    {
        return $this->hasMany(JadwalAngsuran::class, 'pinjaman_id');
    }

    // Relasi ke PembayaranAngsuran
    public function pembayaranAngsuran()
    {
        return $this->hasMany(PembayaranAngsuran::class, 'pinjaman_id');
    }

    // Relasi ke Akun (created_by)
    public function createdBy()
    {
        return $this->belongsTo(Akun::class, 'created_by');
    }
} 