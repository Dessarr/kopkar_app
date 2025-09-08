<?php

namespace App\Models;

class TblPinjamanH extends Table_Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_pinjaman_h';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'no_ktp',
        'tgl_pinjam',
        'anggota_id',
        'barang_id',
        'lama_angsuran',
        'jumlah_angsuran',
        'jumlah',
        'bunga',
        'bunga_rp',
        'biaya_adm',
        'lunas',
        'dk',
        'kas_id',
        'jns_trans',
        'status',
        'jenis_pinjaman',
        'update_data',
        'user_name',
        'keterangan',
        'id_cabang',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tgl_pinjam' => 'datetime',
        'jumlah_angsuran' => 'decimal:2',
        'jumlah' => 'decimal:2',
        'bunga' => 'decimal:2',
        'bunga_rp' => 'decimal:2',
        'update_data' => 'datetime',
    ];

    /**
     * Get the related anggota.
     */
    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'anggota_id');
    }

    /**
     * Get the related barang.
     */
    public function barang()
    {
        return $this->belongsTo(data_barang::class, 'barang_id');
    }

    /**
     * Get the related kas.
     */
    public function kas()
    {
        return $this->belongsTo(DataKas::class, 'kas_id');
    }

    /**
     * Get the related jenis transaksi.
     */
    public function jenis_transaksi()
    {
        return $this->belongsTo(jns_akun::class, 'jns_trans');
    }

    /**
     * Get the detail angsuran for this pinjaman.
     */
    public function detail_angsuran()
    {
        return $this->hasMany(TblPinjamanD::class, 'pinjam_id', 'id');
    }
    
    /**
     * Get the detail angsuran for this pinjaman (alias).
     */
    public function detailAngsuran()
    {
        return $this->hasMany(TblPinjamanD::class, 'pinjam_id', 'id');
    }
} 