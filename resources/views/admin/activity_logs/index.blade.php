@extends('layouts.app')

@section('title', 'Activity Logs')
@section('sub-title', 'Riwayat Aktivitas Sistem')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Activity Logs</h1>
        <div class="flex space-x-2">
            <button onclick="showClearLogsModal()" 
                class="bg-orange-100 p-2 rounded-lg border-2 border-orange-400 space-x-2 flex justify-around">
                <p class="text-sm">Clear Old Logs</p> 
                <i class="fas fa-broom text-orange-600"></i>
            </button>
            <a href="{{ route('admin.activity_logs.export.excel') }}" 
                class="bg-green-100 p-2 rounded-lg border-2 border-green-400 space-x-2 flex justify-around">
                <p class="text-sm">Export Excel</p> 
                <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}" class="h-auto w-[20px]">
            </a>
            <a href="{{ route('admin.activity_logs.export.pdf') }}" 
                class="bg-red-100 p-2 rounded-lg border-2 border-red-400 space-x-2 flex justify-around">
                <p class="text-sm">Export PDF</p> 
                <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}" class="h-auto w-[20px]">
            </a>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-7 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-list text-blue-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-blue-600">Total</p>
                    <p class="text-lg font-semibold text-blue-800">{{ $summary['total'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-check text-green-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-600">Success</p>
                    <p class="text-lg font-semibold text-green-800">{{ $summary['success'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-red-50 border border-red-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i class="fas fa-times text-red-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-600">Failed</p>
                    <p class="text-lg font-semibold text-red-800">{{ $summary['failed'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-clock text-yellow-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-yellow-600">Pending</p>
                    <p class="text-lg font-semibold text-yellow-800">{{ $summary['pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-calendar-day text-purple-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-purple-600">Today</p>
                    <p class="text-lg font-semibold text-purple-800">{{ $summary['today'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-indigo-50 border border-indigo-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-indigo-100 rounded-lg">
                    <i class="fas fa-calendar-week text-indigo-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-indigo-600">This Week</p>
                    <p class="text-lg font-semibold text-indigo-800">{{ $summary['this_week'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-pink-50 border border-pink-200 rounded-lg p-4">
            <div class="flex items-center">
                <div class="p-2 bg-pink-100 rounded-lg">
                    <i class="fas fa-calendar-alt text-pink-600"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-pink-600">This Month</p>
                    <p class="text-lg font-semibold text-pink-800">{{ $summary['this_month'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-4 mb-6">
        <form method="GET" action="{{ route('admin.activity_logs.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Module</label>
                <select name="module" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Semua Module</option>
                    <option value="pengajuan_penarikan" {{ request('module') == 'pengajuan_penarikan' ? 'selected' : '' }}>Pengajuan Penarikan</option>
                    <option value="pengajuan_pinjaman" {{ request('module') == 'pengajuan_pinjaman' ? 'selected' : '' }}>Pengajuan Pinjaman</option>
                    <option value="simpanan" {{ request('module') == 'simpanan' ? 'selected' : '' }}>Simpanan</option>
                    <option value="pinjaman" {{ request('module') == 'pinjaman' ? 'selected' : '' }}>Pinjaman</option>
                    <option value="activity_logs" {{ request('module') == 'activity_logs' ? 'selected' : '' }}>Activity Logs</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Action</label>
                <select name="action" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Semua Action</option>
                    <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Create</option>
                    <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Update</option>
                    <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Delete</option>
                    <option value="approve" {{ request('action') == 'approve' ? 'selected' : '' }}>Approve</option>
                    <option value="reject" {{ request('action') == 'reject' ? 'selected' : '' }}>Reject</option>
                    <option value="cancel" {{ request('action') == 'cancel' ? 'selected' : '' }}>Cancel</option>
                    <option value="view" {{ request('action') == 'view' ? 'selected' : '' }}>View</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Semua Status</option>
                    <option value="success" {{ request('status') == 'success' ? 'selected' : '' }}>Success</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                </select>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">User Type</label>
                <select name="user_type" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    <option value="">Semua User</option>
                    <option value="admin" {{ request('user_type') == 'admin' ? 'selected' : '' }}>Admin</option>
                    <option value="member" {{ request('user_type') == 'member' ? 'selected' : '' }}>Member</option>
                </select>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Dari</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Sampai</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                <div class="flex">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Description/User Name"
                        class="flex-1 px-3 py-2 border border-gray-300 rounded-l-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    <button type="submit" class="bg-[#14AE5C] text-white px-4 py-2 rounded-r-md hover:bg-[#14AE5C]/80">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            @if($logs->count() > 0)
                <table class="w-full border border-gray-300 text-center">
                    <thead class="bg-gray-50">
                        <tr class="text-sm align-middle w-full">
                            <th class="py-2 px-3 border">No</th>
                            <th class="py-2 px-3 border">Timestamp</th>
                            <th class="py-2 px-3 border">User</th>
                            <th class="py-2 px-3 border">Action</th>
                            <th class="py-2 px-3 border">Module</th>
                            <th class="py-2 px-3 border">Description</th>
                            <th class="py-2 px-3 border">Status</th>
                            <th class="py-2 px-3 border">IP Address</th>
                            <th class="py-2 px-3 border">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $index => $log)
                            <tr class="text-sm align-middle hover:bg-gray-50">
                                <td class="py-2 border">
                                    {{ ($logs->currentPage() - 1) * $logs->perPage() + $index + 1 }}
                                </td>
                                <td class="py-2 border">
                                    <div class="text-xs">
                                        <div>{{ $log->created_at->format('d/m/Y') }}</div>
                                        <div class="text-gray-500">{{ $log->created_at->format('H:i:s') }}</div>
                                    </div>
                                </td>
                                <td class="py-2 border">
                                    <div class="text-xs">
                                        <div class="font-medium">{{ $log->user_name }}</div>
                                        <div class="text-gray-500">{{ ucfirst($log->user_type) }}</div>
                                    </div>
                                </td>
                                <td class="py-2 border">
                                    <div class="flex items-center justify-center">
                                        <i class="{{ $log->action_icon }} mr-2"></i>
                                        <span class="capitalize">{{ $log->action }}</span>
                                    </div>
                                </td>
                                <td class="py-2 border">
                                    <span class="px-2 py-1 bg-gray-100 rounded text-xs">{{ $log->module }}</span>
                                </td>
                                <td class="py-2 border text-left px-3">
                                    <div class="max-w-xs truncate" title="{{ $log->description }}">
                                        {{ Str::limit($log->description, 50) }}
                                    </div>
                                </td>
                                <td class="py-2 border">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $log->status_badge }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                </td>
                                <td class="py-2 border text-xs">
                                    {{ $log->ip_address }}
                                </td>
                                <td class="py-2 border">
                                    <a href="{{ route('admin.activity_logs.show', $log->id) }}"
                                        class="text-blue-600 hover:text-blue-900 p-1" title="Detail">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-5 w-full relative px-2 py-2">
                    <div class="mx-auto w-fit">
                        <div class="bg-white px-4 py-1 flex flex-row rounded-full justify-center items-center space-x-2 border border-gray-300 shadow-sm">
                            @for ($i = 1; $i <= $logs->lastPage(); $i++)
                                @if ($i == 1 || $i == $logs->lastPage() || ($i >= $logs->currentPage() - 1 && $i <= $logs->currentPage() + 1))
                                    <a href="{{ $logs->url($i) }}">
                                        <div class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $logs->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                            {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                                        </div>
                                    </a>
                                @elseif ($i == 2 || $i == $logs->lastPage() - 1)
                                    <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                                @endif
                            @endfor
                        </div>
                    </div>

                    <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
                        Displaying {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} items
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-inbox text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data</h3>
                    <p class="text-gray-500">Belum ada activity log yang tersedia</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Clear Logs Modal -->
<div id="clearLogsModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
            <form method="POST" action="{{ route('admin.activity_logs.clear') }}">
                @csrf
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Clear Old Logs</h3>
                    
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Hapus log lebih dari (hari)</label>
                        <select name="days" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="30">30 hari</option>
                            <option value="60">60 hari</option>
                            <option value="90" selected>90 hari</option>
                            <option value="180">180 hari</option>
                            <option value="365">1 tahun</option>
                        </select>
                    </div>
                    
                    <div class="mb-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                        <div class="flex">
                            <i class="fas fa-exclamation-triangle text-yellow-600 mr-2 mt-1"></i>
                            <div class="text-sm text-yellow-800">
                                <p class="font-medium">Peringatan!</p>
                                <p>Aksi ini akan menghapus log lama secara permanen dan tidak dapat dibatalkan.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeClearLogsModal()"
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                            Batal
                        </button>
                        <button type="submit" onclick="return confirm('Apakah Anda yakin ingin menghapus log lama?')"
                            class="bg-orange-500 hover:bg-orange-600 text-white px-4 py-2 rounded-md">
                            Clear Logs
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showClearLogsModal() {
    document.getElementById('clearLogsModal').classList.remove('hidden');
}

function closeClearLogsModal() {
    document.getElementById('clearLogsModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('clearLogsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeClearLogsModal();
    }
});
</script>
@endpush
@endsection
