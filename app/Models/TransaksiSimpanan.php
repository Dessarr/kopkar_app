<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\data_anggota;
use App\Models\jns_simpan;
use App\Models\data_kas;

class TransaksiSimpanan extends Model
{
    use HasFactory;

    protected $table = 'tbl_trans_sp';
    protected $primaryKey = 'id';

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
        'alamat',
        'id_cabang'
    ];

    protected $casts = [
        'tgl_transaksi' => 'date',
        'update_data' => 'datetime',
        'jumlah' => 'decimal:2'
    ];

    // Relationships
    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'anggota_id', 'id');
    }

    public function jenisSimpanan()
    {
        return $this->belongsTo(jns_simpan::class, 'jenis_id', 'id');
    }

    public function kas()
    {
        return $this->belongsTo(data_kas::class, 'kas_id', 'id');
    }
} 