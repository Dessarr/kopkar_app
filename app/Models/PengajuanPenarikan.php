<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PengajuanPenarikan extends Model
{
    protected $table = 'tbl_pengajuan_penarikan';
    protected $primaryKey = 'id';
    
    protected $fillable = [
        'id_anggota',
        'jenis_simpanan',
        'jumlah_penarikan',
        'alasan_penarikan',
        'status',
        'tgl_pengajuan',
        'tgl_update',
        'keterangan'
    ];

    protected $casts = [
        'tgl_pengajuan' => 'datetime',
        'tgl_update' => 'datetime',
        'jumlah_penarikan' => 'decimal:2',
        'status' => 'integer'
    ];

    /**
     * Get the member who submitted this withdrawal request
     */
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id');
    }

    /**
     * Scope for pending withdrawal requests
     */
    public function scopePending($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope for approved withdrawal requests
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for rejected withdrawal requests
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 2);
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        $statuses = [
            0 => 'Pending',
            1 => 'Disetujui',
            2 => 'Ditolak',
            3 => 'Terlaksana',
            4 => 'Dibatalkan'
        ];

        return $statuses[$this->status] ?? 'Unknown';
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeClassAttribute()
    {
        $classes = [
            0 => 'bg-yellow-100 text-yellow-800',
            1 => 'bg-green-100 text-green-800',
            2 => 'bg-red-100 text-red-800',
            3 => 'bg-blue-100 text-blue-800',
            4 => 'bg-gray-100 text-gray-800'
        ];

        return $classes[$this->status] ?? 'bg-gray-100 text-gray-800';
    }
}
