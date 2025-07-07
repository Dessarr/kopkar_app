<?php

namespace App\Models;

class View_PembelianUse extends View_Base
{
    protected $table = 'v_pembelian_use';
    
    protected $casts = [
        'tgl_catat' => 'string',
        'Jan' => 'decimal:2',
        'Feb' => 'decimal:2',
        'Mar' => 'decimal:2',
        'Apr' => 'decimal:2',
        'May' => 'decimal:2',
        'Jun' => 'decimal:2',
        'Jul' => 'decimal:2',
        'Aug' => 'decimal:2',
        'Sep' => 'decimal:2',
        'Oct' => 'decimal:2',
        'Nov' => 'decimal:2',
        'Dec' => 'decimal:2',
        'TOTAL' => 'decimal:2'
    ];

    public function jenisAkun()
    {
        return $this->belongsTo(jns_akun::class, 'kode_trans', 'id');
    }
} 