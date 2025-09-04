<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class TblUser extends Table_Base
{
    use SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tbl_user';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'u_name',
        'pass_word',
        'id_cabang',
        'aktif',
        'level',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'pass_word',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'aktif' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the related cabang.
     */
    public function cabang()
    {
        return $this->belongsTo(Cabang::class, 'id_cabang');
    }

    /**
     * Get status aktif text
     */
    public function getStatusAktifTextAttribute()
    {
        return $this->aktif == 'Y' ? 'Aktif' : 'Tidak Aktif';
    }

    /**
     * Get status aktif badge
     */
    public function getStatusAktifBadgeAttribute()
    {
        return $this->aktif == 'Y' 
            ? 'bg-green-100 text-green-800' 
            : 'bg-red-100 text-red-800';
    }

    /**
     * Get level text
     */
    public function getLevelTextAttribute()
    {
        $levels = [
            'admin' => 'Administrator',
            'pinjaman' => 'Staff Pinjaman',
            'simpanan' => 'Staff Simpanan',
            'kas' => 'Staff Kas',
            'laporan' => 'Staff Laporan',
            'supervisor' => 'Supervisor',
            'manager' => 'Manager'
        ];

        return $levels[$this->level] ?? ucfirst($this->level);
    }

    /**
     * Get level badge
     */
    public function getLevelBadgeAttribute()
    {
        $badges = [
            'admin' => 'bg-purple-100 text-purple-800',
            'pinjaman' => 'bg-blue-100 text-blue-800',
            'simpanan' => 'bg-green-100 text-green-800',
            'kas' => 'bg-yellow-100 text-yellow-800',
            'laporan' => 'bg-indigo-100 text-indigo-800',
            'supervisor' => 'bg-orange-100 text-orange-800',
            'manager' => 'bg-red-100 text-red-800'
        ];

        return $badges[$this->level] ?? 'bg-gray-100 text-gray-800';
    }

    /**
     * Get username formatted
     */
    public function getUsernameFormattedAttribute()
    {
        return strtoupper($this->u_name);
    }

    /**
     * Scope for active users
     */
    public function scopeAktif($query)
    {
        return $query->where('aktif', 'Y');
    }

    /**
     * Scope for inactive users
     */
    public function scopeTidakAktif($query)
    {
        return $query->where('aktif', 'N');
    }

    /**
     * Scope for specific level
     */
    public function scopeByLevel($query, $level)
    {
        return $query->where('level', $level);
    }

    /**
     * Scope for search
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('u_name', 'like', "%{$search}%")
              ->orWhere('level', 'like', "%{$search}%");
        });
    }

    /**
     * Scope for cabang
     */
    public function scopeByCabang($query, $cabang)
    {
        return $query->where('id_cabang', $cabang);
    }

    /**
     * Scope ordered
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('u_name', 'asc');
    }
} 