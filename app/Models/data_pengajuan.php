<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class data_pengajuan extends Model{
    protected $table ='tbl_pengajuan';

    protected $fillable = [
        'ajuan_id',
        'anggota_id',
        'tgl_input',
        'jenis',
        'jumlah',
        'lama_ags',
        'keterangan',
        'status'

    ];
}