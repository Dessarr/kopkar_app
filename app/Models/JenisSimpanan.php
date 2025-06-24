<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JenisSimpanan extends Model
{
    protected $table = 'jenis_simpanan';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'kode', 'nama', 'deskripsi', 'minimal_setoran', 'maksimal_setoran', 'bunga_persen', 'is_wajib', 'is_active', 'created_at', 'updated_at'
    ];

    // Relasi ke Simpanan
    public function simpanan()
    {
        return $this->hasMany(Simpanan::class, 'jenis_simpanan_id');
    }
} 