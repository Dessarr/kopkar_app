<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class data_barang extends Model
{
    protected $table = 'tbl_barang';
    protected $fillable = ['nm_barang', 'type', 'merk', 'harga', 'jml_brg', 'id_cabang'];
    public $timestamps = false;
}

