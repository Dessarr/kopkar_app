<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pengajuan extends Model
{
    protected $table = 'pengajuan';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'anggota_id', 'jenis_pengajuan', 'nominal', 'status', 'tanggal_pengajuan', 'tanggal_approval', 'tanggal_cair', 'alasan', 'keterangan', 'approved_by', 'created_at', 'updated_at', 'is_deleted', 'deleted_at'
    ];

    // Relasi ke Anggota
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'anggota_id');
    }

    // Relasi ke Akun (approved_by)
    public function approvedBy()
    {
        return $this->belongsTo(Akun::class, 'approved_by');
    }
} 