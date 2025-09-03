<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    protected $table = 'tbl_anggota';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'nama',
        'nik',
        'alamat',
        'telepon',
        'email',
        'status',
        'tgl_bergabung'
    ];

    protected $casts = [
        'tgl_bergabung' => 'datetime',
        'status' => 'integer'
    ];

    /**
     * Get all loan applications for this member
     */
    public function pengajuanPinjaman()
    {
        return $this->hasMany(PengajuanPinjaman::class, 'id_anggota', 'id');
    }

    /**
     * Get all withdrawal requests for this member
     */
    public function pengajuanPenarikan()
    {
        return $this->hasMany(PengajuanPenarikan::class, 'id_anggota', 'id');
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        $statuses = [
            0 => 'Non-Aktif',
            1 => 'Aktif',
            2 => 'Suspended'
        ];

        return $statuses[$this->status] ?? 'Unknown';
    }
}
