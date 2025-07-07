<?php

namespace App\Models;

class TempoPinjaman extends Table_Base
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tempo_pinjaman';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'no_urut';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pinjam_id',
        'no_ktp',
        'tgl_pinjam',
        'tempo',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'tgl_pinjam' => 'date',
        'tempo' => 'date',
    ];

    /**
     * Get the related anggota.
     */
    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'no_ktp', 'no_ktp');
    }

    /**
     * Get the related pinjaman.
     */
    public function pinjaman()
    {
        return $this->belongsTo(TblPinjamanH::class, 'pinjam_id', 'id');
    }
} 