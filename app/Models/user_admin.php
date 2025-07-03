<?php 

namespace App\Models;

use illuminate\Database\Eloquent\Model;

class user_admin extends Model{
    protected $table = 'tbl_user';

    protected $fillable = [
        'u_name',
        'pass_word'
    ];
    public $timestamps = false;
};