<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pekerjaan extends Model
{
    use HasFactory;

    protected $table = 'pekerjaan';
    protected $primaryKey = 'id';
    public $timestamps = true;

    protected $fillable = [
        'nama_pekerjaan',
        'keterangan',
        'status'
    ];

    /**
     * Get the anggota for this pekerjaan
     */
    public function anggota()
    {
        return $this->hasMany(data_anggota::class, 'pekerjaan_id', 'id');
    }
} 