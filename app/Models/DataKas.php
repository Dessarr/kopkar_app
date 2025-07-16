<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DataKas extends Model
{
    use HasFactory;

    protected $table = 'data_kas';
    protected $guarded = ['id'];
    public $timestamps = false;

    /**
     * Get the nama attribute.
     *
     * @return string
     */
    public function getNamaAttribute()
    {
        return $this->attributes['nama'] ?? $this->attributes['nama_kas'] ?? null;
    }
}