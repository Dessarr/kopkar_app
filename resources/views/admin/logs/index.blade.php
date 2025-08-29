@extends('layouts.app')

@section('title', 'Log Aktivitas Admin')
@section('sub-title', 'Riwayat Aktivitas Admin')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Log Aktivitas Admin</h1>
        <div class="flex space-x-2">
            <form action="{{ route('admin.logs.clear') }}" method="POST" 
                onsubmit="return confirm('Apakah Anda yakin ingin menghapus semua log?')">
                @csrf
                <button type="submit" 
                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg">
                    <i class="fas fa-trash mr-2"></i>Hapus Semua Log
                </button>
            </form>
        </div>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif

    <!-- Log Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
            @if($paginator->count() > 0)
                <table class="w-full border border-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Waktu
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Level
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Aktivitas
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Detail
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($paginator as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b">
                                    @if($log['timestamp'])
                                        {{ \Carbon\Carbon::parse($log['timestamp'])->format('d/m/Y H:i:s') }}
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap border-b">
                                    @if($log['level'])
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full 
                                            @if($log['level'] === 'ERROR') bg-red-100 text-red-800
                                            @elseif($log['level'] === 'WARNING') bg-yellow-100 text-yellow-800
                                            @elseif($log['level'] === 'INFO') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ $log['level'] }}
                                        </span>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 border-b">
                                    @if($log['message'])
                                        <div class="max-w-md truncate" title="{{ $log['message'] }}">
                                            {{ $log['message'] }}
                                        </div>
                                    @else
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-900 border-b">
                                    <button onclick="showLogDetail('{{ addslashes($log['raw']) }}')"
                                        class="text-blue-600 hover:text-blue-900">
                                        <i class="fas fa-eye"></i> Detail
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-6 px-4 py-3 border-t border-gray-200">
                    {{ $paginator->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-file-alt text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada log</h3>
                    <p class="text-gray-500">Belum ada aktivitas yang tercatat</p>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Log Detail Modal -->
<div id="logDetailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Log</h3>
                    <button onclick="closeLogDetail()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="logDetailContent" class="bg-gray-100 p-4 rounded-lg font-mono text-sm overflow-x-auto">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showLogDetail(logData) {
    document.getElementById('logDetailContent').textContent = logData;
    document.getElementById('logDetailModal').classList.remove('hidden');
}

function closeLogDetail() {
    document.getElementById('logDetailModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('logDetailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeLogDetail();
    }
});
</script>
@endpush
@endsection
