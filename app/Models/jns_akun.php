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
        // Tidak menggunakan boolean cast untuk enum values
    ];

    public $timestamps = false;

    // Accessor untuk status aktif
    public function getStatusTextAttribute()
    {
        return $this->aktif === 'Y' ? 'Aktif' : 'Tidak Aktif';
    }

    // Accessor untuk badge status
    public function getStatusBadgeAttribute()
    {
        return $this->aktif === 'Y' ? 'success' : 'danger';
    }

    // Scope untuk akun aktif
    public function scopeAktif($query)
    {
        return $query->where('aktif', 'Y');
    }

    // Scope untuk akun tidak aktif
    public function scopeTidakAktif($query)
    {
        return $query->where('aktif', 'N');
    }

    // Scope untuk filter berdasarkan tipe akun
    public function scopeByAkunType($query, $type)
    {
        return $query->where('akun', $type);
    }

    // Scope untuk filter berdasarkan pemasukan
    public function scopePemasukan($query)
    {
        return $query->where('pemasukan', 'Y');
    }

    // Scope untuk filter berdasarkan pengeluaran
    public function scopePengeluaran($query)
    {
        return $query->where('pengeluaran', 'Y');
    }

    // Accessor untuk pemasukan text
    public function getPemasukanTextAttribute()
    {
        return $this->pemasukan === 'Y' ? 'Ya' : 'Tidak';
    }

    // Accessor untuk pengeluaran text
    public function getPengeluaranTextAttribute()
    {
        return $this->pengeluaran === 'Y' ? 'Ya' : 'Tidak';
    }

    // Accessor untuk laba rugi text
    public function getLabaRugiTextAttribute()
    {
        return $this->laba_rugi ?? '-';
    }

    // Scope untuk search
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('kd_aktiva', 'like', '%' . $search . '%')
              ->orWhere('jns_trans', 'like', '%' . $search . '%')
              ->orWhere('akun', 'like', '%' . $search . '%');
        });
    }

    // Scope untuk ordered
    public function scopeOrdered($query)
    {
        return $query->orderBy('kd_aktiva');
    }
}