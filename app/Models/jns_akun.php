<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class jns_akun extends Model
{
    use SoftDeletes;

    protected $table = 'jns_akun';

    protected $fillable = [
        'kd_aktiva',
        'jns_trans',
        'akun',
        'laba_rugi',
        'pemasukan',
        'pengeluaran',
        'aktif',
    ];

    protected $casts = [
        'pemasukan' => 'boolean',
        'pengeluaran' => 'boolean',
        'aktif' => 'boolean',
    ];

    public $timestamps = false;

    // Accessor untuk status aktif
    public function getStatusTextAttribute()
    {
        return $this->aktif ? 'Aktif' : 'Tidak Aktif';
    }

    // Accessor untuk badge status
    public function getStatusBadgeAttribute()
    {
        return $this->aktif ? 'success' : 'danger';
    }

    // Scope untuk akun aktif
    public function scopeAktif($query)
    {
        return $query->where('aktif', true);
    }

    // Scope untuk akun tidak aktif
    public function scopeTidakAktif($query)
    {
        return $query->where('aktif', false);
    }

    // Scope untuk filter berdasarkan tipe akun
    public function scopeByAkunType($query, $type)
    {
        return $query->where('akun', $type);
    }

    // Scope untuk filter berdasarkan pemasukan
    public function scopePemasukan($query)
    {
        return $query->where('pemasukan', true);
    }

    // Scope untuk filter berdasarkan pengeluaran
    public function scopePengeluaran($query)
    {
        return $query->where('pengeluaran', true);
    }
}