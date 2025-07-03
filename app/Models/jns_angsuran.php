<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class jns_angsuran extends Model
{
    protected $table = 'jns_angsuran';
    protected $fillable = ['id', 'ket', 'aktif'];
    public $timestamps = false;
}