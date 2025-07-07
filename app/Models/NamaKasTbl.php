<?php

namespace App\Models;

class NamaKasTbl extends Table_Base
{
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
} 