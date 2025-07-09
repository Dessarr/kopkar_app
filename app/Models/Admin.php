<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Admin extends Authenticatable
{
    use Notifiable;

    protected $table = 'tbl_user';
    protected $primaryKey = 'id';

    protected $fillable = [
        'u_name',
        'pass_word',
        'id_cabang',
        'aktif',
        'level',
    ];

    protected $hidden = [
        'pass_word',
    ];

    /**
     * Get the password for the user.
     *
     * @return string
     */
    public function getAuthPassword()
    {
        return $this->pass_word;
    }

    /**
     * Get the username field name.
     *
     * @return string
     */
    public function username()
    {
        return 'u_name';
    }

    /**
     * Set the user's password.
     *
     * @param string $value
     * @return void
     */
    public function setPasswordAttribute($value)
    {
        if ($value) {
            $this->attributes['pass_word'] = Hash::make($value);
        }
    }
} 