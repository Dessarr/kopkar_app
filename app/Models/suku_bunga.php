<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class suku_bunga extends Model
{
    protected $table = 'suku_bunga';
    protected $fillable = ['id', 'opsi_key', 'opsi_val', 'id_cabang'];
    public $timestamps = false;
}
