<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Notifikasi extends Model
{
    protected $table = 'notifikasi';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'user_id', 'judul', 'pesan', 'tipe', 'status', 'tanggal_kirim', 'tanggal_baca', 'ref_id', 'ref_tipe', 'sisa_nilai', 'tanggal_jatuh_tempo'
    ];

    // Relasi ke Akun
    public function user()
    {
        return $this->belongsTo(Akun::class, 'user_id');
    }
} 