<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class jns_angsuran extends Model
{
    use SoftDeletes;

    protected $table = 'jns_angsuran';

    protected $fillable = [
        'ket',
        'aktif',
    ];

    protected $casts = [
        'ket' => 'integer',
    ];

    public $timestamps = false;

    // Accessor untuk status aktif
    public function getStatusAktifTextAttribute()
    {
        return $this->aktif === 'Y' ? 'Aktif' : 'Tidak Aktif';
    }

    // Accessor untuk badge status aktif
    public function getStatusAktifBadgeAttribute()
    {
        return $this->aktif === 'Y' ? 'aktif' : 'tidak-aktif';
    }

    // Accessor untuk format keterangan
    public function getKetFormattedAttribute()
    {
        return $this->ket . ' Bulan';
    }

    // Accessor untuk kategori angsuran
    public function getKategoriAngsuranAttribute()
    {
        if ($this->ket <= 6) {
            return 'Jangka Pendek';
        } elseif ($this->ket <= 24) {
            return 'Jangka Menengah';
        } else {
            return 'Jangka Panjang';
        }
    }

    // Accessor untuk badge kategori angsuran
    public function getKategoriAngsuranBadgeAttribute()
    {
        if ($this->ket <= 6) {
            return 'pendek';
        } elseif ($this->ket <= 24) {
            return 'menengah';
        } else {
            return 'panjang';
        }
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('ket', 'like', '%' . $search . '%');
        });
    }

    // Scope untuk filter berdasarkan status aktif
    public function scopeByStatusAktif($query, $status)
    {
        if ($status === 'aktif') {
            return $query->where('aktif', 'Y');
        } elseif ($status === 'nonaktif') {
            return $query->where('aktif', 'T');
        }
        return $query;
    }

    // Scope untuk filter berdasarkan kategori angsuran
    public function scopeByKategori($query, $kategori)
    {
        switch ($kategori) {
            case 'pendek':
                return $query->where('ket', '<=', 6);
            case 'menengah':
                return $query->where('ket', '>', 6)->where('ket', '<=', 24);
            case 'panjang':
                return $query->where('ket', '>', 24);
            default:
                return $query;
        }
    }

    // Scope untuk filter berdasarkan range bulan
    public function scopeByRangeBulan($query, $min, $max)
    {
        return $query->whereBetween('ket', [$min, $max]);
    }

    // Scope untuk urutan
    public function scopeOrdered($query)
    {
        return $query->orderBy('ket');
    }
}