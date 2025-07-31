<?php

namespace App\Models;

class TblPinjamanD extends Table_Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_pinjaman_d';

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
        'tgl_bayar',
        'pinjam_id',
        'angsuran_ke',
        'jumlah_bayar',
        'bunga',
        'denda_rp',
        'biaya_adm',
        'terlambat',
        'ket_bayar',
        'dk',
        'kas_id',
        'jns_trans',
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
        'tgl_bayar' => 'datetime',
        'jumlah_bayar' => 'decimal:2',
        'bunga' => 'decimal:2',
        'denda_rp' => 'decimal:2',
        'biaya_adm' => 'decimal:2',
        'update_data' => 'datetime',
    ];

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
     * Get the related pinjaman.
     */
    public function pinjaman()
    {
        return $this->belongsTo(TblPinjamanH::class, 'pinjam_id', 'id');
    }
} 