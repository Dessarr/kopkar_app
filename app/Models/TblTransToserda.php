<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class TblTransToserda extends Model
{
    use HasFactory;

    protected $table = 'tbl_trans_toserda';
    protected $guarded = ['id'];
    public $timestamps = false;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'tgl_transaksi' => 'datetime',
    ];

    /**
     * Get the anggota associated with the transaction.
     */
    public function anggota()
    {
        return $this->belongsTo(data_anggota::class, 'anggota_id', 'id');
    }

    /**
     * Get the kas associated with the transaction.
     * First tries nama_kas_tbl, then falls back to data_kas.
     */
    public function kas()
    {
        $namaKasRelation = $this->belongsTo(NamaKasTbl::class, 'kas_id', 'id');
        
        if ($namaKasRelation->first()) {
            return $namaKasRelation;
        }
        
        return $this->belongsTo(DataKas::class, 'kas_id', 'id');
    }

    /**
     * Get the barang associated with the transaction.
     */
    public function barang()
    {
        return $this->belongsTo(data_barang::class, 'jenis_id', 'id');
    }
    
    /**
     * Get the billing entries associated with this transaction.
     */
    public function billing()
    {
        return $this->hasMany(billing::class, 'id_transaksi', 'id')
            ->where('jns_transaksi', 'toserda');
    }
    
    /**
     * Set the tgl_transaksi attribute.
     *
     * @param  mixed  $value
     * @return void
     */
    public function setTglTransaksiAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['tgl_transaksi'] = Carbon::parse($value);
        } else {
            $this->attributes['tgl_transaksi'] = $value;
        }
    }
} 