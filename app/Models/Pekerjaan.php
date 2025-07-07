<?php

namespace App\Models;

class Pekerjaan extends Table_Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pekerjaan';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_kerja';

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
        'id_kerja',
        'jenis_kerja',
    ];
} 