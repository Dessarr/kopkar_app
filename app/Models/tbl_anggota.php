<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class tbl_anggota extends Model
{
    use SoftDeletes;

    protected $table = 'tbl_anggota';

    protected $fillable = [
        'nama',
        'identitas',
        'jk',
        'tmp_lahir',
        'tgl_lahir',
        'status',
        'agama',
        'departement',
        'pekerjaan',
        'alamat',
        'kota',
        'notelp',
        'tgl_daftar',
        'jabatan_id',
        'aktif',
        'pass_word',
        'file_pic',
        'no_ktp',
        'bank',
        'nama_pemilik_rekening',
        'no_rekening',
        'id_tagihan',
        'simpanan_wajib',
        'simpanan_sukarela',
        'simpanan_khusus_2',
        'id_cabang',
    ];

    protected $casts = [
        'tgl_lahir' => 'date',
        'tgl_daftar' => 'date',
        'simpanan_wajib' => 'decimal:2',
        'simpanan_sukarela' => 'decimal:2',
        'simpanan_khusus_2' => 'decimal:2',
    ];

    public $timestamps = false;

    // Accessor untuk jenis kelamin
    public function getJenisKelaminTextAttribute()
    {
        return $this->jk == 'L' ? 'Laki-laki' : 'Perempuan';
    }

    // Accessor untuk status aktif
    public function getStatusAktifTextAttribute()
    {
        return $this->aktif == 'Y' ? 'Aktif' : 'Tidak Aktif';
    }

    // Accessor untuk badge status aktif
    public function getStatusAktifBadgeAttribute()
    {
        return $this->aktif == 'Y' ? 'success' : 'danger';
    }

    // Accessor untuk format simpanan wajib
    public function getSimpananWajibFormattedAttribute()
    {
        return 'Rp ' . number_format($this->simpanan_wajib ?? 0, 0, ',', '.');
    }

    // Accessor untuk format simpanan sukarela
    public function getSimpananSukarelaFormattedAttribute()
    {
        return 'Rp ' . number_format($this->simpanan_sukarela ?? 0, 0, ',', '.');
    }

    // Accessor untuk format simpanan khusus 2
    public function getSimpananKhusus2FormattedAttribute()
    {
        return 'Rp ' . number_format($this->simpanan_khusus_2 ?? 0, 0, ',', '.');
    }

    // Accessor untuk umur
    public function getUmurAttribute()
    {
        if (!$this->tgl_lahir) return null;
        return $this->tgl_lahir->diffInYears(now());
    }

    // Scope untuk anggota aktif
    public function scopeAktif($query)
    {
        return $query->where('aktif', 'Y');
    }

    // Scope untuk anggota tidak aktif
    public function scopeTidakAktif($query)
    {
        return $query->where('aktif', 'N');
    }

    // Scope untuk filter berdasarkan jenis kelamin
    public function scopeByJenisKelamin($query, $jk)
    {
        return $query->where('jk', $jk);
    }

    // Scope untuk filter berdasarkan departemen
    public function scopeByDepartemen($query, $departemen)
    {
        return $query->where('departement', 'like', '%' . $departemen . '%');
    }

    // Scope untuk filter berdasarkan kota
    public function scopeByKota($query, $kota)
    {
        return $query->where('kota', 'like', '%' . $kota . '%');
    }

    // Scope untuk pencarian
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('nama', 'like', '%' . $search . '%')
              ->orWhere('no_ktp', 'like', '%' . $search . '%')
              ->orWhere('identitas', 'like', '%' . $search . '%')
              ->orWhere('departement', 'like', '%' . $search . '%')
              ->orWhere('kota', 'like', '%' . $search . '%');
        });
    }
}
