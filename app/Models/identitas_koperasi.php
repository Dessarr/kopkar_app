<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class identitas_koperasi extends Model
{
    protected $table = 'tbl_setting';
    protected $fillable = ['id', 'opsi_key', 'opsi_val', 'id_cabang'];
    public $timestamps = false;
}