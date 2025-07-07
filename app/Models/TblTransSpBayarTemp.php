<?php

namespace App\Models;

class TblTransSpBayarTemp extends Table_Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_trans_sp_bayar_temp';

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
        'jumlah',
        'keterangan',
        'tagihan_simpanan_wajib',
        'tagihan_simpanan_sukarela',
        'tagihan_simpanan_khusus_2',
        'tagihan_pinjaman',
        'tagihan_pinjaman_jasa',
        'tagihan_toserda',
        'total_tagihan_simpanan',
        'selisih',
        'saldo_simpanan_sukarela',
        'saldo_akhir_simpanan_sukarela',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tgl_transaksi' => 'date',
        'jumlah' => 'double',
        'tagihan_simpanan_wajib' => 'decimal:2',
        'tagihan_simpanan_sukarela' => 'decimal:2',
        'tagihan_simpanan_khusus_2' => 'decimal:2',
        'tagihan_pinjaman' => 'decimal:2',
        'tagihan_pinjaman_jasa' => 'decimal:2',
        'tagihan_toserda' => 'decimal:2',
        'total_tagihan_simpanan' => 'decimal:2',
        'selisih' => 'decimal:2',
        'saldo_simpanan_sukarela' => 'decimal:2',
        'saldo_akhir_simpanan_sukarela' => 'decimal:2',
    ];

    /**
     * Get the related anggota.
     */
    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'anggota_id');
    }
} 