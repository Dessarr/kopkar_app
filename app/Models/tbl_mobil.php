<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class tbl_mobil extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_mobil';

    protected $fillable = [
        'nama',
        'jenis',
        'merek',
        'pabrikan',
        'warna',
        'tahun',
        'no_polisi',
        'no_rangka',
        'no_mesin',
        'no_bpkb',
        'tgl_berlaku_stnk',
        'file_pic',
        'aktif',
    ];

    protected $casts = [
        'tahun' => 'integer',
        'tgl_berlaku_stnk' => 'date',
        'aktif' => 'boolean',
    ];

    public $timestamps = false;

    // Accessor untuk status aktif
    public function getStatusAktifTextAttribute()
    {
        return $this->aktif ? 'Aktif' : 'Nonaktif';
    }

    // Accessor untuk badge status aktif
    public function getStatusAktifBadgeAttribute()
    {
        return $this->aktif ? 'success' : 'danger';
    }

    // Accessor untuk format tahun
    public function getTahunFormattedAttribute()
    {
        return $this->tahun ? $this->tahun : '-';
    }

    // Accessor untuk format tanggal STNK
    public function getTglBerlakuStnkFormattedAttribute()
    {
        return $this->tgl_berlaku_stnk ? $this->tgl_berlaku_stnk->format('d/m/Y') : '-';
    }

    // Accessor untuk status STNK
    public function getStatusStnkAttribute()
    {
        if (!$this->tgl_berlaku_stnk) {
            return 'Tidak Ada Data';
        }

        $today = now();
        $stnkDate = $this->tgl_berlaku_stnk;
        
        if ($stnkDate < $today) {
            return 'Kadaluarsa';
        } elseif ($stnkDate->diffInDays($today) <= 30) {
            return 'Akan Kadaluarsa';
        } else {
            return 'Masih Berlaku';
        }
    }

    // Accessor untuk badge status STNK
    public function getStatusStnkBadgeAttribute()
    {
        switch ($this->status_stnk) {
            case 'Kadaluarsa':
                return 'danger';
            case 'Akan Kadaluarsa':
                return 'warning';
            case 'Masih Berlaku':
                return 'success';
            default:
                return 'secondary';
        }
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama', 'like', '%' . $search . '%')
              ->orWhere('jenis', 'like', '%' . $search . '%')
              ->orWhere('merek', 'like', '%' . $search . '%')
              ->orWhere('pabrikan', 'like', '%' . $search . '%')
              ->orWhere('warna', 'like', '%' . $search . '%')
              ->orWhere('no_polisi', 'like', '%' . $search . '%')
              ->orWhere('no_rangka', 'like', '%' . $search . '%')
              ->orWhere('no_mesin', 'like', '%' . $search . '%')
              ->orWhere('no_bpkb', 'like', '%' . $search . '%');
        });
    }

    // Scope untuk filter berdasarkan jenis
    public function scopeByJenis($query, $jenis)
    {
        return $query->where('jenis', 'like', '%' . $jenis . '%');
    }

    // Scope untuk filter berdasarkan merek
    public function scopeByMerek($query, $merek)
    {
        return $query->where('merek', 'like', '%' . $merek . '%');
    }

    // Scope untuk filter berdasarkan pabrikan
    public function scopeByPabrikan($query, $pabrikan)
    {
        return $query->where('pabrikan', 'like', '%' . $pabrikan . '%');
    }

    // Scope untuk filter berdasarkan warna
    public function scopeByWarna($query, $warna)
    {
        return $query->where('warna', 'like', '%' . $warna . '%');
    }

    // Scope untuk filter berdasarkan tahun
    public function scopeByTahun($query, $tahun)
    {
        return $query->where('tahun', $tahun);
    }

    // Scope untuk filter berdasarkan status aktif
    public function scopeByStatusAktif($query, $status)
    {
        if ($status === 'aktif') {
            return $query->where('aktif', 'Y');
        } elseif ($status === 'nonaktif') {
            return $query->where('aktif', 'N');
        }
        return $query;
    }

    // Scope untuk filter berdasarkan status STNK
    public function scopeByStatusStnk($query, $status)
    {
        $today = now();
        
        switch ($status) {
            case 'kadaluarsa':
                return $query->where('tgl_berlaku_stnk', '<', $today);
            case 'akan_kadaluarsa':
                return $query->where('tgl_berlaku_stnk', '>=', $today)
                           ->where('tgl_berlaku_stnk', '<=', $today->addDays(30));
            case 'masih_berlaku':
                return $query->where('tgl_berlaku_stnk', '>', $today->addDays(30));
            default:
                return $query;
        }
    }

    // Scope untuk urutan
    public function scopeOrdered($query)
    {
        return $query->orderBy('nama');
    }

    public function kas()
    {
        return $this->belongsTo(NamaKasTbl::class, 'kas_id', 'id');
    }

    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'cabang_id', 'id');
    }
}
