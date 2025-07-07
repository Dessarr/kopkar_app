<?php

namespace App\Models;

class TblTransAngkutan extends Table_Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_trans_angkutan';

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
        'id_mobil',
        'tgl_catat',
        'jumlah',
        'keterangan',
        'akun',
        'dari_kas_id',
        'untuk_kas_id',
        'jns_trans',
        'dk',
        'update_data',
        'user_name',
        'id_cabang',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tgl_catat' => 'datetime',
        'jumlah' => 'double',
        'update_data' => 'datetime',
    ];

    /**
     * Get the related mobil.
     */
    public function mobil()
    {
        return $this->belongsTo(data_mobil::class, 'id_mobil');
    }

    /**
     * Get the related dari kas.
     */
    public function dari_kas()
    {
        return $this->belongsTo(NamaKasTbl::class, 'dari_kas_id');
    }

    /**
     * Get the related untuk kas.
     */
    public function untuk_kas()
    {
        return $this->belongsTo(NamaKasTbl::class, 'untuk_kas_id');
    }

    /**
     * Get the related jenis transaksi.
     */
    public function jenis_transaksi()
    {
        return $this->belongsTo(jns_akun::class, 'jns_trans');
    }

    /**
     * Get the related cabang.
     */
    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }
} 