<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class ActivityLog extends Model
{
    protected $table = 'activity_logs';
    
    protected $fillable = [
        'user_id',
        'user_type', // 'admin' atau 'member'
        'user_name',
        'action',
        'module',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'status', // 'success', 'failed', 'pending'
        'error_message',
        'affected_record_id',
        'affected_record_type'
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Get the user that performed the action
     */
    public function user()
    {
        if ($this->user_type === 'admin') {
            return $this->belongsTo(Admin::class, 'user_id');
        }
        return $this->belongsTo(Member::class, 'user_id');
    }

    /**
     * Scope for successful activities
     */
    public function scopeSuccess($query)
    {
        return $query->where('status', 'success');
    }

    /**
     * Scope for failed activities
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope for specific module
     */
    public function scopeModule($query, $module)
    {
        return $query->where('module', $module);
    }

    /**
     * Scope for specific action
     */
    public function scopeAction($query, $action)
    {
        return $query->where('action', $action);
    }

    /**
     * Get formatted description
     */
    public function getFormattedDescriptionAttribute()
    {
        return $this->description;
    }

    /**
     * Get status badge class
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'success' => 'bg-green-100 text-green-800',
            'failed' => 'bg-red-100 text-red-800',
            'pending' => 'bg-yellow-100 text-yellow-800',
            default => 'bg-gray-100 text-gray-800'
        };
    }

    /**
     * Get action icon
     */
    public function getActionIconAttribute()
    {
        return match($this->action) {
            'create' => 'fas fa-plus',
            'update' => 'fas fa-edit',
            'delete' => 'fas fa-trash',
            'approve' => 'fas fa-check',
            'reject' => 'fas fa-times',
            'cancel' => 'fas fa-ban',
            'process' => 'fas fa-play',
            'view' => 'fas fa-eye',
            'export' => 'fas fa-download',
            'import' => 'fas fa-upload',
            default => 'fas fa-info-circle'
        };
    }
}
