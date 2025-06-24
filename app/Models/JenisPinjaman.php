<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisPinjaman extends Model
{
    protected $table = 'jenis_pinjaman';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'kode', 'nama', 'deskripsi', 'bunga_persen', 'minimal_pinjaman', 'maksimal_pinjaman', 'jangka_waktu_max', 'require_guarantor', 'is_active', 'created_at', 'updated_at'
    ];

    // Relasi ke Pinjaman
    public function pinjaman()
    {
        return $this->hasMany(Pinjaman::class, 'jenis_pinjaman_id');
    }
} 