<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiAngkutan extends Model
{
    protected $table = 'transaksi_angkutan';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'mobil_id', 'jenis_transaksi', 'jumlah', 'tanggal_transaksi', 'keterangan', 'created_by'
    ];

    // Relasi ke Mobil
    public function mobil()
    {
        return $this->belongsTo(Mobil::class, 'mobil_id');
    }

    // Relasi ke Akun (created_by)
    public function createdBy()
    {
        return $this->belongsTo(Akun::class, 'created_by');
    }
} 