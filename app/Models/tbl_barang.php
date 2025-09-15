<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class tbl_barang extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_barang';

    protected $fillable = [
        'nm_barang',
        'type',
        'merk',
        'harga',
        'jml_brg',
        'ket',
        'id_cabang',
    ];

    protected $casts = [
        'harga' => 'decimal:2',
        'jml_brg' => 'integer',
    ];

    public $timestamps = false;

    // Accessor untuk format harga
    public function getHargaFormattedAttribute()
    {
        return 'Rp ' . number_format((float)$this->harga, 0, ',', '.');
    }

    // Accessor untuk status stok
    public function getStatusStokAttribute()
    {
        if ($this->jml_brg == 0) {
            return 'Habis';
        } elseif ($this->jml_brg <= 5) {
            return 'Kritis';
        } elseif ($this->jml_brg <= 20) {
            return 'Sedikit';
        } else {
            return 'Cukup';
        }
    }

    // Accessor untuk badge status stok
    public function getStatusStokBadgeAttribute()
    {
        switch ($this->status_stok) {
            case 'Habis':
                return 'danger';
            case 'Kritis':
                return 'warning';
            case 'Sedikit':
                return 'info';
            case 'Cukup':
                return 'success';
            default:
                return 'secondary';
        }
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nm_barang', 'like', '%' . $search . '%')
              ->orWhere('type', 'like', '%' . $search . '%')
              ->orWhere('merk', 'like', '%' . $search . '%')
              ->orWhere('ket', 'like', '%' . $search . '%')
              ->orWhere('id_cabang', 'like', '%' . $search . '%');
        });
    }

    // Scope untuk filter berdasarkan type
    public function scopeByType($query, $type)
    {
        return $query->where('type', 'like', '%' . $type . '%');
    }

    // Scope untuk filter berdasarkan merk
    public function scopeByMerk($query, $merk)
    {
        return $query->where('merk', 'like', '%' . $merk . '%');
    }

    // Scope untuk filter berdasarkan cabang
    public function scopeByCabang($query, $cabang)
    {
        return $query->where('id_cabang', 'like', '%' . $cabang . '%');
    }

    // Scope untuk filter berdasarkan range harga
    public function scopeByHargaRange($query, $min, $max)
    {
        return $query->whereBetween('harga', [$min, $max]);
    }

    // Scope untuk filter berdasarkan status stok
    public function scopeByStatusStok($query, $status)
    {
        switch ($status) {
            case 'habis':
                return $query->where('jml_brg', 0);
            case 'kritis':
                return $query->where('jml_brg', '>', 0)->where('jml_brg', '<=', 5);
            case 'sedikit':
                return $query->where('jml_brg', '>', 5)->where('jml_brg', '<=', 20);
            case 'cukup':
                return $query->where('jml_brg', '>', 20);
            default:
                return $query;
        }
    }

    // Scope untuk urutan
    public function scopeOrdered($query)
    {
        return $query->orderBy('nm_barang');
    }
}
