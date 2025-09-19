<?php

namespace App\Models;

class Cabang extends Table_Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cabang';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_cabang';

    /**
     * The "type" of the primary key ID.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'id_cabang',
        'nama',
        'alamat',
        'no_telp',
    ];

    /**
     * Generate next ID cabang
     *
     * @return string
     */
    public static function generateNextId()
    {
        $lastCabang = self::orderBy('id_cabang', 'desc')->first();
        
        if (!$lastCabang) {
            return 'CB0001';
        }
        
        // Extract number from last ID (e.g., CB0005 -> 5)
        $lastNumber = (int) substr($lastCabang->id_cabang, 2);
        $nextNumber = $lastNumber + 1;
        
        return 'CB' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }
} 