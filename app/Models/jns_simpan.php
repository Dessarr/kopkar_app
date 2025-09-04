<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class jns_simpan extends Model
{
    use SoftDeletes;

    protected $table = 'jns_simpan';

    protected $fillable = [
        'jns_simpan',
        'jumlah',
        'tampil',
        'urut',
    ];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'urut' => 'integer',
    ];

    public $timestamps = false;

    // Accessor untuk status tampil
    public function getStatusTextAttribute()
    {
        return $this->tampil == 'Y' ? 'Tampil' : 'Tidak Tampil';
    }

    // Accessor untuk badge status
    public function getStatusBadgeAttribute()
    {
        return $this->tampil == 'Y' ? 'success' : 'danger';
    }

    // Accessor untuk format jumlah
    public function getJumlahFormattedAttribute()
    {
        return 'Rp ' . number_format($this->jumlah, 0, ',', '.');
    }

    // Scope untuk simpanan yang tampil
    public function scopeTampil($query)
    {
        return $query->where('tampil', 'Y');
    }

    // Scope untuk simpanan yang tidak tampil
    public function scopeTidakTampil($query)
    {
        return $query->where('tampil', 'T');
    }

    // Scope untuk filter berdasarkan tipe simpanan
    public function scopeByType($query, $type)
    {
        return $query->where('jns_simpan', 'like', '%' . $type . '%');
    }

    // Scope untuk filter berdasarkan range jumlah
    public function scopeByJumlahRange($query, $min, $max)
    {
        return $query->whereBetween('jumlah', [$min, $max]);
    }

    // Scope untuk urutan
    public function scopeOrdered($query)
    {
        return $query->orderBy('urut');
    }
}