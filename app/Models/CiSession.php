<?php

namespace App\Models;

class CiSession extends Table_Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'ci_sessions';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'session_id';

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
        'session_id',
        'ip_address',
        'user_agent',
        'last_activity',
        'user_data',
    ];
} 