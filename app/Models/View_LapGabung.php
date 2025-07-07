<?php

namespace App\Models;

class View_LapGabung extends View_Base
{
    protected $table = 'v_lap_gabung';
    
    protected $casts = [
        'tanggal' => 'date',
        'simpanan_pokok' => 'decimal:2',
        'simpanan_wajib' => 'decimal:2',
        'simpanan_sukarela' => 'decimal:2',
        'simpanan_khusus_2' => 'decimal:2',
        'jumlah' => 'decimal:2',
        'jasa' => 'decimal:2',
        'toserda' => 'decimal:2',
        'tagihan_bulan_ini' => 'decimal:2'
    ];

    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }
} 