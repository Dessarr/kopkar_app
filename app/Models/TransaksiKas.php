<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TransaksiKas extends Model
{
    protected $table = 'transaksi_kas';
    protected $guarded = ['id'];

    protected $casts = [
        'jumlah' => 'decimal:2',
        'tanggal_transaksi' => 'datetime',
        'created_at' => 'datetime',
    ];

    // Relasi dengan kas
    public function kas()
    {
        return $this->belongsTo(Kas::class, 'kas_id');
    }

    // Relasi dengan kas sumber (untuk transfer)
    public function dariKas()
    {
        return $this->belongsTo(Kas::class, 'dari_kas_id');
    }

    // Relasi dengan kas tujuan (untuk transfer)
    public function untukKas()
    {
        return $this->belongsTo(Kas::class, 'untuk_kas_id');
    }

    // Relasi dengan user yang membuat transaksi
    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Accessor untuk format jumlah
    public function getFormattedJumlahAttribute()
    {
        return 'Rp ' . number_format($this->jumlah, 2, ',', '.');
    }

    // Accessor untuk keterangan transaksi
    public function getKeteranganTransaksiAttribute()
    {
        if ($this->jenis_transaksi === 'transfer') {
            return "Transfer dari {$this->dariKas->nama} ke {$this->untukKas->nama}";
        }
        return ucfirst($this->jenis_transaksi) . ' kas ' . $this->kas->nama;
    }
}
