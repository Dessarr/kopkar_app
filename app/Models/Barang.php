<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $table = 'barang';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'nama', 'tipe', 'merk', 'harga', 'jumlah', 'keterangan', 'created_at', 'updated_at', 'is_deleted'
    ];

    // Relasi ke TransaksiToserda
    public function transaksiToserda()
    {
        return $this->hasMany(TransaksiToserda::class, 'barang_id');
    }
} 