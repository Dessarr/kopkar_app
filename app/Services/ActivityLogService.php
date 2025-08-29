<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class ActivityLogService
{
    /**
     * Log successful activity
     */
    public static function logSuccess(
        string $action,
        string $module,
        string $description,
        $oldValues = null,
        $newValues = null,
        $affectedRecordId = null,
        $affectedRecordType = null
    ): void {
        try {
            $request = request();
            $user = Auth::guard('admin')->user() ?? Auth::guard('member')->user();
            
            $logData = [
                'user_id' => $user ? $user->id : null,
                'user_type' => $user ? (Auth::guard('admin')->check() ? 'admin' : 'member') : 'guest',
                'user_name' => $user ? ($user->name ?? $user->nama ?? 'Unknown') : 'Guest',
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'success',
                'error_message' => null,
                'affected_record_id' => $affectedRecordId,
                'affected_record_type' => $affectedRecordType,
            ];

            ActivityLog::create($logData);

            // Also log to Laravel's default log for backup
            Log::info("Activity Log Success: {$action} - {$module}", $logData);
            
        } catch (\Exception $e) {
            // Fallback to Laravel's default log if our logging fails
            Log::error("Failed to create activity log: " . $e->getMessage(), [
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log failed activity
     */
    public static function logFailed(
        string $action,
        string $module,
        string $description,
        string $errorMessage,
        $oldValues = null,
        $newValues = null,
        $affectedRecordId = null,
        $affectedRecordType = null
    ): void {
        try {
            $request = request();
            $user = Auth::guard('admin')->user() ?? Auth::guard('member')->user();
            
            $logData = [
                'user_id' => $user ? $user->id : null,
                'user_type' => $user ? (Auth::guard('admin')->check() ? 'admin' : 'member') : 'guest',
                'user_name' => $user ? ($user->name ?? $user->nama ?? 'Unknown') : 'Guest',
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'failed',
                'error_message' => $errorMessage,
                'affected_record_id' => $affectedRecordId,
                'affected_record_type' => $affectedRecordType,
            ];

            ActivityLog::create($logData);

            // Also log to Laravel's default log for backup
            Log::error("Activity Log Failed: {$action} - {$module}", $logData);
            
        } catch (\Exception $e) {
            // Fallback to Laravel's default log if our logging fails
            Log::error("Failed to create failed activity log: " . $e->getMessage(), [
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Log pending activity
     */
    public static function logPending(
        string $action,
        string $module,
        string $description,
        $oldValues = null,
        $newValues = null,
        $affectedRecordId = null,
        $affectedRecordType = null
    ): void {
        try {
            $request = request();
            $user = Auth::guard('admin')->user() ?? Auth::guard('member')->user();
            
            $logData = [
                'user_id' => $user ? $user->id : null,
                'user_type' => $user ? (Auth::guard('admin')->check() ? 'admin' : 'member') : 'guest',
                'user_name' => $user ? ($user->name ?? $user->nama ?? 'Unknown') : 'Guest',
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'old_values' => $oldValues,
                'new_values' => $newValues,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'status' => 'pending',
                'error_message' => null,
                'affected_record_id' => $affectedRecordId,
                'affected_record_type' => $affectedRecordType,
            ];

            ActivityLog::create($logData);

            // Also log to Laravel's default log for backup
            Log::info("Activity Log Pending: {$action} - {$module}", $logData);
            
        } catch (\Exception $e) {
            // Fallback to Laravel's default log if our logging fails
            Log::error("Failed to create pending activity log: " . $e->getMessage(), [
                'action' => $action,
                'module' => $module,
                'description' => $description,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get activity logs with filters
     */
    public static function getLogs($filters = [])
    {
        $query = ActivityLog::query();

        // Filter by module
        if (isset($filters['module']) && $filters['module']) {
            $query->module($filters['module']);
        }

        // Filter by action
        if (isset($filters['action']) && $filters['action']) {
            $query->action($filters['action']);
        }

        // Filter by status
        if (isset($filters['status']) && $filters['status']) {
            $query->where('status', $filters['status']);
        }

        // Filter by user type
        if (isset($filters['user_type']) && $filters['user_type']) {
            $query->where('user_type', $filters['user_type']);
        }

        // Filter by date range
        if (isset($filters['date_from']) && $filters['date_from']) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to']) && $filters['date_to']) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        // Search by description or user name
        if (isset($filters['search']) && $filters['search']) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('action', 'like', "%{$search}%");
            });
        }

        return $query->orderBy('created_at', 'desc')->paginate(15);
    }

    /**
     * Get summary statistics
     */
    public static function getSummary()
    {
        return [
            'total' => ActivityLog::count(),
            'success' => ActivityLog::success()->count(),
            'failed' => ActivityLog::failed()->count(),
            'pending' => ActivityLog::where('status', 'pending')->count(),
            'today' => ActivityLog::whereDate('created_at', today())->count(),
            'this_week' => ActivityLog::whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'this_month' => ActivityLog::whereMonth('created_at', now()->month)->count(),
        ];
    }
}
