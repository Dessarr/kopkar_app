<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class data_anggota extends Model
{
    protected $table = 'tbl_anggota';
    protected $fillable = [
        'id',
        'nama',
        'identitas',
        'jk',
        'tmp_lahir',
        'tgl_lahir',
        'status',
        'agama',
        'departement',
        'pekerjaan',
        'alamat',
        'kota',
        'notelp',
        'tgl_daftar',
        'jabatan_id',
        'aktif',
        'pass_word',
        'file_pic',
        'no_ktp',
        'bank',
        'nama_pemilik_rekening',
        'no_rekening',
        'id_tagihan',
        'simpanan_wajib',
        'simpanan_sukarela',
        'simpanan_khusus_2',
        'id_cabang'
    ];
    public $timestamps = false;
}   