<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\ActivityLogService;
use Illuminate\Support\Facades\Log;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        try {
            $filters = $request->only([
                'module', 'action', 'status', 'user_type', 
                'date_from', 'date_to', 'search'
            ]);

            $logs = ActivityLogService::getLogs($filters);
            $summary = ActivityLogService::getSummary();

            // Log admin access to activity logs
            ActivityLogService::logSuccess(
                'view',
                'activity_logs',
                'Admin mengakses halaman activity logs',
                null,
                $filters,
                null,
                'ActivityLog'
            );

            return view('admin.activity_logs.index', compact('logs', 'summary', 'filters'));
        } catch (\Exception $e) {
            Log::error('Error accessing activity logs: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat activity logs: ' . $e->getMessage());
        }
    }

    public function show($id)
    {
        try {
            $log = \App\Models\ActivityLog::findOrFail($id);
            
            // Log admin viewing specific log
            ActivityLogService::logSuccess(
                'view',
                'activity_logs',
                "Admin melihat detail activity log ID: {$id}",
                null,
                $log->toArray(),
                $id,
                'ActivityLog'
            );

            return view('admin.activity_logs.show', compact('log'));
        } catch (\Exception $e) {
            Log::error('Error viewing activity log: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat detail log: ' . $e->getMessage());
        }
    }

    public function exportExcel(Request $request)
    {
        try {
            $filters = $request->only([
                'module', 'action', 'status', 'user_type', 
                'date_from', 'date_to', 'search'
            ]);

            // Log export attempt
            ActivityLogService::logSuccess(
                'export',
                'activity_logs',
                'Admin mengexport activity logs ke Excel',
                null,
                $filters,
                null,
                'ActivityLog'
            );

            // Implementation for Excel export
            // This would use Laravel Excel package
            return redirect()->back()->with('info', 'Fitur export Excel akan segera tersedia');
        } catch (\Exception $e) {
            ActivityLogService::logFailed(
                'export',
                'activity_logs',
                'Gagal export activity logs ke Excel',
                $e->getMessage(),
                null,
                $filters ?? [],
                null,
                'ActivityLog'
            );

            return redirect()->back()->with('error', 'Gagal export: ' . $e->getMessage());
        }
    }

    public function exportPdf(Request $request)
    {
        try {
            $filters = $request->only([
                'module', 'action', 'status', 'user_type', 
                'date_from', 'date_to', 'search'
            ]);

            // Log export attempt
            ActivityLogService::logSuccess(
                'export',
                'activity_logs',
                'Admin mengexport activity logs ke PDF',
                null,
                $filters,
                null,
                'ActivityLog'
            );

            // Implementation for PDF export
            // This would use DomPDF or similar package
            return redirect()->back()->with('info', 'Fitur export PDF akan segera tersedia');
        } catch (\Exception $e) {
            ActivityLogService::logFailed(
                'export',
                'activity_logs',
                'Gagal export activity logs ke PDF',
                $e->getMessage(),
                null,
                $filters ?? [],
                null,
                'ActivityLog'
            );

            return redirect()->back()->with('error', 'Gagal export: ' . $e->getMessage());
        }
    }

    public function clearOldLogs(Request $request)
    {
        try {
            $days = $request->input('days', 90);
            
            // Log before clearing
            ActivityLogService::logPending(
                'clear',
                'activity_logs',
                "Admin memulai proses pembersihan log lama (lebih dari {$days} hari)",
                null,
                ['days' => $days],
                null,
                'ActivityLog'
            );

            // Delete old logs
            $deletedCount = \App\Models\ActivityLog::where('created_at', '<', now()->subDays($days))->delete();

            // Log successful clearing
            ActivityLogService::logSuccess(
                'clear',
                'activity_logs',
                "Berhasil membersihkan {$deletedCount} log lama (lebih dari {$days} hari)",
                null,
                ['deleted_count' => $deletedCount, 'days' => $days],
                null,
                'ActivityLog'
            );

            return redirect()->back()->with('success', "Berhasil membersihkan {$deletedCount} log lama");
        } catch (\Exception $e) {
            ActivityLogService::logFailed(
                'clear',
                'activity_logs',
                'Gagal membersihkan log lama',
                $e->getMessage(),
                null,
                ['days' => $request->input('days', 90)],
                null,
                'ActivityLog'
            );

            return redirect()->back()->with('error', 'Gagal membersihkan log: ' . $e->getMessage());
        }
    }
}
