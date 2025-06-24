<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiSimpanan extends Model
{
    protected $table = 'transaksi_simpanan';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'simpanan_id', 'jenis_transaksi', 'nominal', 'saldo_sebelum', 'saldo_sesudah', 'tanggal_transaksi', 'keterangan', 'referensi', 'bukti_transaksi', 'processed_by', 'created_at'
    ];

    // Relasi ke Simpanan
    public function simpanan()
    {
        return $this->belongsTo(Simpanan::class, 'simpanan_id');
    }

    // Relasi ke Akun (processed_by)
    public function processedBy()
    {
        return $this->belongsTo(Akun::class, 'processed_by');
    }
} 