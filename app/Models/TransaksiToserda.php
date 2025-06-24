<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiToserda extends Model
{
    protected $table = 'transaksi_toserda';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'barang_id', 'jenis_transaksi', 'jumlah', 'harga_satuan', 'tanggal_transaksi', 'keterangan', 'created_by'
    ];

    // Relasi ke Barang
    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    // Relasi ke Akun (created_by)
    public function createdBy()
    {
        return $this->belongsTo(Akun::class, 'created_by');
    }
} 