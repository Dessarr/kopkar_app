<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class data_pengajuan_penarikan extends Model
{
    protected $table = 'tbl_pengajuan_penarikan';
    protected $fillable = [
        'no_ajuan',
        'ajuan_id',
        'anggota_id',
        'tgl_input',
        'jenis',
        'nominal',
        'lama_ags',
        'keterangan',
        'status',
        'alasan',
        'tgl_cair',
        'tgl_update',
        'id_cabang',
    ];
    public $timestamps = false;

    /**
     * Get the member that owns the withdrawal application
     */
    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'anggota_id', 'id');
    }

    /**
     * Get the jenis simpanan
     */
    public function jenisSimpanan()
    {
        return $this->belongsTo(jns_simpan::class, 'jenis', 'id');
    }

    /**
     * Get status text
     */
    public function getStatusTextAttribute()
    {
        return match($this->status) {
            0 => 'Menunggu Konfirmasi',
            2 => 'Ditolak',
            3 => 'Terlaksana',
            4 => 'Batal',
            default => 'Tidak Diketahui'
        };
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            0 => 'bg-primary',
            2 => 'bg-danger',
            3 => 'bg-info',
            4 => 'bg-warning',
            default => 'bg-secondary'
        };
    }

    /**
     * Get formatted nominal
     */
    public function getNominalFormattedAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    /**
     * Get formatted date
     */
    public function getTglInputFormattedAttribute()
    {
        return Carbon::parse($this->tgl_input)->format('d/m/Y H:i');
    }

    /**
     * Get formatted update date
     */
    public function getTglUpdateFormattedAttribute()
    {
        return Carbon::parse($this->tgl_update)->format('d/m/Y H:i');
    }

    /**
     * Get formatted cair date
     */
    public function getTglCairFormattedAttribute()
    {
        if ($this->tgl_cair) {
            return Carbon::parse($this->tgl_cair)->format('d/m/Y');
        }
        return '-';
    }

    /**
     * Scope for pending applications
     */
    public function scopePending($query)
    {
        return $query->where('status', 0);
    }

    /**
     * Scope for approved applications
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope for rejected applications
     */
    public function scopeRejected($query)
    {
        return $query->where('status', 2);
    }

    /**
     * Scope for completed applications
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 3);
    }

    /**
     * Scope for cancelled applications
     */
    public function scopeCancelled($query)
    {
        return $query->where('status', 4);
    }
}
