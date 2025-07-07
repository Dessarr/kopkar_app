<?php

namespace App\Models;

class View_PersediaanAwal extends View_Base
{
    protected $table = 'v_persediaan_awal';
    
    protected $casts = [
        'tgl_catat' => 'date',
        'barang_tersedia' => 'decimal:2',
        'pembelian' => 'decimal:2',
        'persediaan_awal_jan' => 'decimal:2',
        'persediaan_awal_feb' => 'decimal:2',
        'persediaan_awal_mar' => 'decimal:2',
        'persediaan_awal_apr' => 'decimal:2',
        'persediaan_awal_may' => 'decimal:2',
        'persediaan_awal_jun' => 'decimal:2',
        'persediaan_awal_jul' => 'decimal:2',
        'persediaan_awal_aug' => 'decimal:2',
        'persediaan_awal_sep' => 'decimal:2',
        'persediaan_awal_oct' => 'decimal:2',
        'persediaan_awal_nov' => 'decimal:2',
        'persediaan_awal_dec' => 'decimal:2'
    ];
} 