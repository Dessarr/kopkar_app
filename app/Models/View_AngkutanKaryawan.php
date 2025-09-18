<?php

namespace App\Models;

class View_AngkutanKaryawan extends View_Base
{
    protected $table = 'v_angkutan_karyawan';
    
    protected $casts = [
        'tgl_catat' => 'date',
        'no_polisi' => 'string',
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
} 