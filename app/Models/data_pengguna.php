<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class data_pengguna extends Model
{
    protected $table = 'tbl_user';
    protected $fillable = ['u_name', 'pass_word', 'id_cabang', 'aktif', 'level'];
    public $timestamps = false;
}