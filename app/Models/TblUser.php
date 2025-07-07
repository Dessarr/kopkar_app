<?php

namespace App\Models;

class TblUser extends Table_Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_user';

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
        'u_name',
        'pass_word',
        'id_cabang',
        'aktif',
        'level',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'pass_word',
    ];

    /**
     * Get the related cabang.
     */
    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }
} 