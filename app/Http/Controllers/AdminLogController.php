<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Carbon\Carbon;

class AdminLogController extends Controller
{
    public function index(Request $request)
    {
        $logFile = storage_path('logs/laravel.log');
        $logs = [];
        
        if (File::exists($logFile)) {
            $logContent = File::get($logFile);
            $logLines = explode("\n", $logContent);
            
            // Filter logs related to pengajuan penarikan
            $filteredLogs = [];
            foreach ($logLines as $line) {
                if (strpos($line, 'pengajuan penarikan') !== false || 
                    strpos($line, 'Pengajuan Penarikan') !== false ||
                    strpos($line, 'Admin') !== false) {
                    $filteredLogs[] = $line;
                }
            }
            
            // Parse logs and format them
            foreach (array_reverse($filteredLogs) as $line) {
                if (trim($line) !== '') {
                    $logs[] = $this->parseLogLine($line);
                }
            }
        }
        
        // Paginate logs
        $perPage = 50;
        $currentPage = $request->get('page', 1);
        $offset = ($currentPage - 1) * $perPage;
        $paginatedLogs = array_slice($logs, $offset, $perPage);
        
        // Create paginator manually
        $paginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $paginatedLogs,
            count($logs),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        return view('admin.logs.index', compact('paginator'));
    }
    
    private function parseLogLine($line)
    {
        // Basic log parsing - you can enhance this based on your log format
        $timestamp = '';
        $level = '';
        $message = '';
        
        // Extract timestamp
        if (preg_match('/\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]\]/', $line, $matches)) {
            $timestamp = $matches[1];
        }
        
        // Extract log level
        if (preg_match('/\.(\w+):/', $line, $matches)) {
            $level = strtoupper($matches[1]);
        }
        
        // Extract message
        if (preg_match('/\.\w+:\s*(.+)$/', $line, $matches)) {
            $message = $matches[1];
        }
        
        return [
            'timestamp' => $timestamp,
            'level' => $level,
            'message' => $message,
            'raw' => $line
        ];
    }
    
    public function clear()
    {
        $logFile = storage_path('logs/laravel.log');
        
        if (File::exists($logFile)) {
            File::put($logFile, '');
            
            Log::info('Admin menghapus semua log aktivitas', [
                'admin_id' => auth()->id(),
                'admin_name' => auth()->user()->name ?? 'admin',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'timestamp' => now()->toDateTimeString()
            ]);
        }
        
        return redirect()->route('admin.logs.index')
            ->with('success', 'Log aktivitas berhasil dihapus');
    }
}
