<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class NamaKasTbl extends Model
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'nama_kas_tbl';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'aktif',
        'tmpl_simpan',
        'tmpl_penarikan',
        'tmpl_pinjaman',
        'tmpl_bayar',
        'tmpl_pemasukan',
        'tmpl_pengeluaran',
        'tmpl_transfer',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'aktif' => 'string',
        'tmpl_simpan' => 'string',
        'tmpl_penarikan' => 'string',
        'tmpl_pinjaman' => 'string',
        'tmpl_bayar' => 'string',
        'tmpl_pemasukan' => 'string',
        'tmpl_pengeluaran' => 'string',
        'tmpl_transfer' => 'string',
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    // Accessors
    public function getStatusAktifTextAttribute()
    {
        return $this->aktif === 'Y' ? 'Aktif' : 'Tidak Aktif';
    }

    public function getStatusAktifBadgeAttribute()
    {
        return $this->aktif === 'Y' 
            ? '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Aktif</span>'
            : '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Tidak Aktif</span>';
    }

    public function getTampilSimpananTextAttribute()
    {
        return $this->tmpl_simpan === 'Y' ? 'Ya' : 'Tidak';
    }

    public function getTampilPenarikanTextAttribute()
    {
        return $this->tmpl_penarikan === 'Y' ? 'Ya' : 'Tidak';
    }

    public function getTampilPinjamanTextAttribute()
    {
        return $this->tmpl_pinjaman === 'Y' ? 'Ya' : 'Tidak';
    }

    public function getTampilBayarTextAttribute()
    {
        return $this->tmpl_bayar === 'Y' ? 'Ya' : 'Tidak';
    }

    public function getTampilPemasukanTextAttribute()
    {
        return $this->tmpl_pemasukan === 'Y' ? 'Ya' : 'Tidak';
    }

    public function getTampilPengeluaranTextAttribute()
    {
        return $this->tmpl_pengeluaran === 'Y' ? 'Ya' : 'Tidak';
    }

    public function getTampilTransferTextAttribute()
    {
        return $this->tmpl_transfer === 'Y' ? 'Ya' : 'Tidak';
    }

    public function getTampilSimpananBadgeAttribute()
    {
        return $this->tmpl_simpan === 'Y' 
            ? '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Ya</span>'
            : '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Tidak</span>';
    }

    public function getTampilPenarikanBadgeAttribute()
    {
        return $this->tmpl_penarikan === 'Y' 
            ? '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Ya</span>'
            : '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Tidak</span>';
    }

    public function getTampilPinjamanBadgeAttribute()
    {
        return $this->tmpl_pinjaman === 'Y' 
            ? '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Ya</span>'
            : '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Tidak</span>';
    }

    public function getTampilBayarBadgeAttribute()
    {
        return $this->tmpl_bayar === 'Y' 
            ? '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Ya</span>'
            : '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Tidak</span>';
    }

    public function getTampilPemasukanBadgeAttribute()
    {
        return $this->tmpl_pemasukan === 'Y' 
            ? '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Ya</span>'
            : '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Tidak</span>';
    }

    public function getTampilPengeluaranBadgeAttribute()
    {
        return $this->tmpl_pengeluaran === 'Y' 
            ? '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Ya</span>'
            : '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Tidak</span>';
    }

    public function getTampilTransferBadgeAttribute()
    {
        return $this->tmpl_transfer === 'Y' 
            ? '<span class="px-2 py-1 text-xs font-semibold text-green-800 bg-green-100 rounded-full">Ya</span>'
            : '<span class="px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">Tidak</span>';
    }

    public function getTotalFiturAktifAttribute()
    {
        $fitur = [
            $this->tmpl_simpan,
            $this->tmpl_penarikan,
            $this->tmpl_pinjaman,
            $this->tmpl_bayar,
            $this->tmpl_pemasukan,
            $this->tmpl_pengeluaran,
            $this->tmpl_transfer
        ];
        
        return array_sum(array_map(function($item) {
            return $item === 'Y' ? 1 : 0;
        }, $fitur));
    }

    public function getKategoriKasAttribute()
    {
        $totalAktif = $this->total_fitur_aktif;
        
        if ($totalAktif >= 6) {
            return 'Komprehensif';
        } elseif ($totalAktif >= 4) {
            return 'Menengah';
        } elseif ($totalAktif >= 2) {
            return 'Dasar';
        } else {
            return 'Minimal';
        }
    }

    public function getKategoriKasBadgeAttribute()
    {
        $kategori = $this->kategori_kas;
        
        $badges = [
            'Komprehensif' => '<span class="px-2 py-1 text-xs font-semibold text-purple-800 bg-purple-100 rounded-full">Komprehensif</span>',
            'Menengah' => '<span class="px-2 py-1 text-xs font-semibold text-blue-800 bg-blue-100 rounded-full">Menengah</span>',
            'Dasar' => '<span class="px-2 py-1 text-xs font-semibold text-yellow-800 bg-yellow-100 rounded-full">Dasar</span>',
            'Minimal' => '<span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">Minimal</span>'
        ];
        
        return $badges[$kategori] ?? '<span class="px-2 py-1 text-xs font-semibold text-gray-800 bg-gray-100 rounded-full">-</span>';
    }

    // Scopes
    public function scopeSearch($query, $search)
    {
        return $query->where('nama', 'like', "%{$search}%");
    }

    public function scopeByStatusAktif($query, $status)
    {
        if ($status === 'aktif') {
            return $query->where('aktif', 'Y');
        } elseif ($status === 'tidak_aktif') {
            return $query->where('aktif', 'T');
        }
        return $query;
    }

    public function scopeByKategori($query, $kategori)
    {
        // This would need a more complex query to filter by calculated category
        // For now, we'll filter by total active features
        switch ($kategori) {
            case 'komprehensif':
                return $query->where(function($q) {
                    $q->where('tmpl_simpan', 'Y')
                      ->where('tmpl_penarikan', 'Y')
                      ->where('tmpl_pinjaman', 'Y')
                      ->where('tmpl_bayar', 'Y')
                      ->where('tmpl_pemasukan', 'Y')
                      ->where('tmpl_pengeluaran', 'Y')
                      ->where('tmpl_transfer', 'Y');
                });
            case 'menengah':
                return $query->where(function($q) {
                    $q->whereRaw('(
                        (tmpl_simpan = "Y" ? 1 : 0) + 
                        (tmpl_penarikan = "Y" ? 1 : 0) + 
                        (tmpl_pinjaman = "Y" ? 1 : 0) + 
                        (tmpl_bayar = "Y" ? 1 : 0) + 
                        (tmpl_pemasukan = "Y" ? 1 : 0) + 
                        (tmpl_pengeluaran = "Y" ? 1 : 0) + 
                        (tmpl_transfer = "Y" ? 1 : 0)
                    ) >= 4');
                })->where(function($q) {
                    $q->whereRaw('(
                        (tmpl_simpan = "Y" ? 1 : 0) + 
                        (tmpl_penarikan = "Y" ? 1 : 0) + 
                        (tmpl_pinjaman = "Y" ? 1 : 0) + 
                        (tmpl_bayar = "Y" ? 1 : 0) + 
                        (tmpl_pemasukan = "Y" ? 1 : 0) + 
                        (tmpl_pengeluaran = "Y" ? 1 : 0) + 
                        (tmpl_transfer = "Y" ? 1 : 0)
                    ) < 6');
                });
            case 'dasar':
                return $query->where(function($q) {
                    $q->whereRaw('(
                        (tmpl_simpan = "Y" ? 1 : 0) + 
                        (tmpl_penarikan = "Y" ? 1 : 0) + 
                        (tmpl_pinjaman = "Y" ? 1 : 0) + 
                        (tmpl_bayar = "Y" ? 1 : 0) + 
                        (tmpl_pemasukan = "Y" ? 1 : 0) + 
                        (tmpl_pengeluaran = "Y" ? 1 : 0) + 
                        (tmpl_transfer = "Y" ? 1 : 0)
                    ) >= 2');
                })->where(function($q) {
                    $q->whereRaw('(
                        (tmpl_simpan = "Y" ? 1 : 0) + 
                        (tmpl_penarikan = "Y" ? 1 : 0) + 
                        (tmpl_pinjaman = "Y" ? 1 : 0) + 
                        (tmpl_bayar = "Y" ? 1 : 0) + 
                        (tmpl_pemasukan = "Y" ? 1 : 0) + 
                        (tmpl_pengeluaran = "Y" ? 1 : 0) + 
                        (tmpl_transfer = "Y" ? 1 : 0)
                    ) < 4');
                });
            case 'minimal':
                return $query->where(function($q) {
                    $q->whereRaw('(
                        (tmpl_simpan = "Y" ? 1 : 0) + 
                        (tmpl_penarikan = "Y" ? 1 : 0) + 
                        (tmpl_pinjaman = "Y" ? 1 : 0) + 
                        (tmpl_bayar = "Y" ? 1 : 0) + 
                        (tmpl_pemasukan = "Y" ? 1 : 0) + 
                        (tmpl_pengeluaran = "Y" ? 1 : 0) + 
                        (tmpl_transfer = "Y" ? 1 : 0)
                    ) < 2');
                });
        }
        return $query;
    }

    public function scopeByFitur($query, $fitur)
    {
        if ($fitur) {
            return $query->where($fitur, 'Y');
        }
        return $query;
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('nama', 'asc');
    }
} 