<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class billing extends Model
{
    protected $table = 'billing';
    protected $primaryKey = 'id_billing';
    public $incrementing = false;
    protected $keyType = 'string';
    
    protected $fillable = [
        'id_billing',
        'bulan_tahun',
        'id_anggota',
        'no_ktp',
        'nama',
        'bulan',
        'tahun',
        'simpanan_wajib',
        'simpanan_sukarela',
        'simpanan_khusus_2',
        'simpanan_pokok',
        'total_billing',
        'total_tagihan',
        'status',
        'status_bayar',
        'id_akun',
        'jns_trans',
        'created_at',
        'updated_at'
    ];

    protected $attributes = [
        'simpanan_wajib' => 0,
        'simpanan_sukarela' => 0,
        'simpanan_khusus_2' => 0,
        'simpanan_pokok' => 0,
        'total_billing' => 0,
        'total_tagihan' => 0,
        'status' => 'N',
        'status_bayar' => 'Belum Lunas'
    ];
    
    // Relasi dengan anggota
    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
    
    // Relasi dengan jenis akun
    public function jenisAkun()
    {
        return $this->belongsTo(jns_akun::class, 'id_akun', 'id');
    }
}