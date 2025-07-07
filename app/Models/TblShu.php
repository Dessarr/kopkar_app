<?php

namespace App\Models;

class TblShu extends Table_Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_shu';

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
        'tgl_transaksi',
        'no_ktp',
        'jumlah_bayar',
        'jns_trans',
        'dk',
        'kas_id',
        'update_data',
        'user_name',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tgl_transaksi' => 'datetime',
        'jumlah_bayar' => 'decimal:2',
        'update_data' => 'datetime',
    ];

    /**
     * Get the related anggota.
     */
    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }

    /**
     * Get the related kas.
     */
    public function kas()
    {
        return $this->belongsTo(NamaKasTbl::class, 'kas_id');
    }

    /**
     * Get the related jenis transaksi.
     */
    public function jenis_transaksi()
    {
        return $this->belongsTo(jns_akun::class, 'jns_trans');
    }
} 