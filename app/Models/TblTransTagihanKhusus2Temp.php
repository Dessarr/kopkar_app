<?php

namespace App\Models;

class TblTransTagihanKhusus2Temp extends Table_Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_trans_tagihan_khusus2_temp';

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
        'anggota_id',
        'jenis_id',
        'jumlah',
        'keterangan',
        'akun',
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
        'tgl_transaksi' => 'date',
        'jumlah' => 'double',
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
     * Get the related jenis simpanan.
     */
    public function jenis_simpanan()
    {
        return $this->belongsTo(jns_simpan::class, 'jenis_id');
    }

    /**
     * Get the related kas.
     */
    public function kas()
    {
        return $this->belongsTo(DataKas::class, 'kas_id');
    }
} 