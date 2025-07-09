<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;

class Member extends Authenticatable
{
    use Notifiable;

    protected $table = 'tbl_anggota';
    protected $primaryKey = 'id';

    protected $fillable = [
        'nama',
        'identitas',
        'jk',
        'tmp_lahir',
        'tgl_lahir',
        'status',
        'agama',
        'departement',
        'pekerjaan',
        'alamat',
        'kota',
        'notelp',
        'tgl_daftar',
        'jabatan_id',
        'aktif',
        'pass_word',
        'file_pic',
        'no_ktp',
        'bank',
        'nama_pemilik_rekening',
        'no_rekening',
        'id_tagihan',
        'simpanan_wajib',
        'simpanan_sukarela',
        'simpanan_khusus_2',
        'id_cabang'
    ];

    protected $hidden = [
        'pass_word'
    ];

    public $timestamps = false;

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
        return 'nama';
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