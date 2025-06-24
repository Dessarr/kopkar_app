<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    protected $table = 'anggota';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'member_code', 'nama', 'alamat', 'no_ktp', 'tempat_lahir', 'tanggal_lahir', 'jenis_kelamin', 'phone', 'email', 'pekerjaan', 'tanggal_gabung', 'tanggal_keluar', 'status', 'alasan_keluar', 'gaji_pokok', 'created_at', 'updated_at', 'created_by', 'updated_by', 'pekerjaan_id', 'jabatan_id'
    ];

    // Relasi ke Simpanan
    public function simpanan()
    {
        return $this->hasMany(Simpanan::class, 'anggota_id');
    }

    // Relasi ke Pinjaman
    public function pinjaman()
    {
        return $this->hasMany(Pinjaman::class, 'anggota_id');
    }

    // Relasi ke Pengajuan
    public function pengajuan()
    {
        return $this->hasMany(Pengajuan::class, 'anggota_id');
    }

    // Relasi ke Akun (created_by)
    public function createdBy()
    {
        return $this->belongsTo(Akun::class, 'created_by');
    }

    // Relasi ke Akun (updated_by)
    public function updatedBy()
    {
        return $this->belongsTo(Akun::class, 'updated_by');
    }
} 