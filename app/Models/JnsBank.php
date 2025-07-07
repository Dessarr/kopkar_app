<?php

namespace App\Models;

class JnsBank extends Table_Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'jns_bank';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'bank';

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
        'bank',
        'lastupdate',
        'user_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'lastupdate' => 'datetime',
    ];
} 