<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class data_mobil extends Model
{
    protected $table = 'tbl_mobil';
    protected $fillable = ['id', 'nama', 'jenis', 'merek', 'no_polisi', 'tgl_berlaku_stnk', 'aktif'];
    public $timestamps = false;
}