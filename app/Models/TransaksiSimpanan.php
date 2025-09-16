<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\data_anggota;
use App\Models\jns_simpan;
use App\Models\DataKas;

class TransaksiSimpanan extends Model
{
    use HasFactory;

    protected $table = 'tbl_trans_sp';
    protected $primaryKey = 'id';
    public $timestamps = false; // Disable timestamps karena table tidak ada updated_at

    protected $fillable = [
        'tgl_transaksi',
        'no_ktp',
        'anggota_id',
        'jenis_id',
        'jumlah',
        'keterangan',
        'akun',
        'dk',
        'kas_id',
        'update_data',
        'user_name',
        'nama_penyetor',
        'no_identitas',
        'alamat'
    ];

    protected $casts = [
        'tgl_transaksi' => 'datetime',
        'update_data' => 'datetime',
        'jumlah' => 'decimal:2'
    ];

    // Relationships
    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'anggota_id', 'id');
    }

    // Accessor untuk mendapatkan anggota dengan fallback
    public function getAnggotaAttribute()
    {
        // Coba cari berdasarkan anggota_id dulu
        if ($this->anggota_id) {
            $anggota = data_anggota::find($this->anggota_id);
            if ($anggota) {
                return $anggota;
            }
        }
        
        // Fallback ke no_ktp
        if ($this->no_ktp) {
            return data_anggota::where('no_ktp', $this->no_ktp)->first();
        }
        
        return null;
    }

    public function jenisSimpanan()
    {
        return $this->belongsTo(jns_simpan::class, 'jenis_id', 'id');
    }

    public function kas()
    {
        return $this->belongsTo(DataKas::class, 'kas_id', 'id');
    }
} 