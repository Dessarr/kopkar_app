<?php

namespace App\Models;

class View_Tersedia extends View_Base
{
    protected $table = 'v_tersedia';
    
    protected $casts = [
        'tgl_catat' => 'date',
        'tersedia' => 'decimal:2'
    ];
} 