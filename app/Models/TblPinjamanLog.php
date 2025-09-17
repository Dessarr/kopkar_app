<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TblPinjamanLog extends Model
{
    protected $table = 'tbl_pinjaman_log';
    public $timestamps = false;

    protected $fillable = [
        'pinjaman_id',
        'field_name',
        'old_value',
        'new_value',
        'action',
        'user_name',
        'created_at',
        'notes'
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    /**
     * Log perubahan data pinjaman
     */
    public static function logChange($pinjamanId, $fieldName, $oldValue, $newValue, $action = 'UPDATE', $userName = 'system', $notes = null)
    {
        return self::create([
            'pinjaman_id' => $pinjamanId,
            'field_name' => $fieldName,
            'old_value' => $oldValue,
            'new_value' => $newValue,
            'action' => $action,
            'user_name' => $userName,
            'created_at' => now(),
            'notes' => $notes
        ]);
    }

    /**
     * Get logs for specific pinjaman
     */
    public static function getLogsForPinjaman($pinjamanId)
    {
        return self::where('pinjaman_id', $pinjamanId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}