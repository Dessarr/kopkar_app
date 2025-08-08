<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class data_pengajuan extends Model{
    protected $table ='tbl_pengajuan';

    protected $fillable = [
        'no_ajuan',
        'ajuan_id',
        'anggota_id',
        'tgl_input',
        'jenis',
        'nominal',
        'lama_ags',
        'keterangan',
        'status',
        'alasan',
        'tgl_cair',
        'tgl_update',
        'id_cabang',
    ];

    public $timestamps = false;
}