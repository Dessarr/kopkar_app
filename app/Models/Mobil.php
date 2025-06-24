<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mobil extends Model
{
    protected $table = 'mobil';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'nama', 'jenis', 'merk', 'tahun', 'no_polisi', 'warna', 'no_rangka', 'no_mesin', 'no_bpkb', 'tgl_berlaku_stnk', 'keterangan', 'aktif', 'created_at', 'updated_at', 'is_deleted'
    ];

    // Relasi ke TransaksiAngkutan
    public function transaksiAngkutan()
    {
        return $this->hasMany(TransaksiAngkutan::class, 'mobil_id');
    }
} 