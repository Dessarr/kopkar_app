<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Akun extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $table = 'akun';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'username',
        'password',
        'role',
        'is_active',
        'created_at',
        'updated_at',
        'remember_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed', // otomatis hash password
    ];

    public function getAuthPassword()
    {
        return $this->password;
    }

    // Tambahkan relasi yang memang ada di sistem kamu
    public function anggotaCreated()
    {
        return $this->hasMany(Anggota::class, 'created_by');
    }

    public function anggotaUpdated()
    {
        return $this->hasMany(Anggota::class, 'updated_by');
    }

    public function simpananCreated()
    {
        return $this->hasMany(Simpanan::class, 'created_by');
    }

    public function transaksiSimpananProcessed()
    {
        return $this->hasMany(TransaksiSimpanan::class, 'processed_by');
    }

    public function pinjamanCreated()
    {
        return $this->hasMany(Pinjaman::class, 'created_by');
    }

    public function pembayaranAngsuranProcessed()
    {
        return $this->hasMany(PembayaranAngsuran::class, 'processed_by');
    }

    public function transaksiToserdaCreated()
    {
        return $this->hasMany(TransaksiToserda::class, 'created_by');
    }

    public function transaksiAngkutanCreated()
    {
        return $this->hasMany(TransaksiAngkutan::class, 'created_by');
    }

    public function transaksiKasCreated()
    {
        return $this->hasMany(TransaksiKas::class, 'created_by');
    }

    public function pengajuanApproved()
    {
        return $this->hasMany(Pengajuan::class, 'approved_by');
    }

    public function notifikasi()
    {
        return $this->hasMany(Notifikasi::class, 'user_id');
    }
}
