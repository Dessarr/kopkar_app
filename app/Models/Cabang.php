<?php

namespace App\Models;

class Cabang extends Table_Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cabang';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_cabang';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_cabang',
        'nama',
        'alamat',
        'no_telp',
    ];
} 