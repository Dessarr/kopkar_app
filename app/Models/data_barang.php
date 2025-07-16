<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class data_barang extends Model
{
    protected $table = 'tbl_barang';
    protected $fillable = ['nm_barang', 'type', 'merk', 'harga', 'jml_brg', 'id_cabang', 'stok', 'harga_jual', 'harga_beli'];
    public $timestamps = false;
    
    /**
     * Get the stock of the item.
     * 
     * @return int
     */
    public function getStokAttribute()
    {
        return $this->attributes['stok'] ?? $this->attributes['jml_brg'] ?? 0;
    }
    
    /**
     * Get the selling price of the item.
     * 
     * @return float
     */
    public function getHargaJualAttribute()
    {
        return $this->attributes['harga_jual'] ?? $this->attributes['harga'] ?? 0;
    }
    
    /**
     * Get the buying price of the item.
     * 
     * @return float
     */
    public function getHargaBeliAttribute()
    {
        return $this->attributes['harga_beli'] ?? $this->attributes['harga'] ?? 0;
    }
    
    /**
     * Get the name of the item.
     * 
     * @return string
     */
    public function getNamaBarangAttribute()
    {
        return $this->attributes['nm_barang'] ?? '';
    }
}

