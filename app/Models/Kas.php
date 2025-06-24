<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kas extends Model
{
    protected $table = 'kas';
    protected $guarded = ['id'];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relasi dengan transaksi kas
    public function transaksi()
    {
        return $this->hasMany(TransaksiKas::class, 'kas_id');
    }

    // Relasi dengan transaksi kas sebagai kas sumber
    public function transaksiKeluar()
    {
        return $this->hasMany(TransaksiKas::class, 'dari_kas_id');
    }

    // Relasi dengan transaksi kas sebagai kas tujuan
    public function transaksiMasuk()
    {
        return $this->hasMany(TransaksiKas::class, 'untuk_kas_id');
    }

    // Method untuk mendapatkan saldo kas
    public function getSaldo()
    {
        $pemasukan = $this->transaksi()
            ->where('jenis_transaksi', 'pemasukan')
            ->sum('jumlah');

        $pengeluaran = $this->transaksi()
            ->where('jenis_transaksi', 'pengeluaran')
            ->sum('jumlah');

        $transferMasuk = $this->transaksiMasuk()->sum('jumlah');
        $transferKeluar = $this->transaksiKeluar()->sum('jumlah');

        return $pemasukan + $transferMasuk - $pengeluaran - $transferKeluar;
    }
} 