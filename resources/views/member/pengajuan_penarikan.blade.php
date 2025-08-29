@extends('layouts.member')

@section('title', 'Data Pengajuan Penarikan Simpanan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Data Pengajuan Penarikan Simpanan</h1>
            <a href="{{ route('member.pengajuan.penarikan.form') }}"
                class="bg-[#14AE5C] hover:bg-[#14AE5C]/80 text-white px-4 py-2 rounded-lg">
                + Pengajuan Baru
            </a>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-blue-100 rounded-lg">
                        <i class="fas fa-clock text-blue-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-blue-600">Menunggu</p>
                        <p class="text-lg font-semibold text-blue-800">
                            {{ $dataPengajuan->where('status', 0)->count() }}
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-green-100 rounded-lg">
                        <i class="fas fa-check text-green-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-green-600">Disetujui</p>
                        <p class="text-lg font-semibold text-green-800">
                            {{ $dataPengajuan->where('status', 1)->count() }}
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-red-100 rounded-lg">
                        <i class="fas fa-times text-red-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-600">Ditolak</p>
                        <p class="text-lg font-semibold text-red-800">
                            {{ $dataPengajuan->where('status', 2)->count() }}
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="bg-purple-50 border border-purple-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-purple-100 rounded-lg">
                        <i class="fas fa-check-double text-purple-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-purple-600">Terlaksana</p>
                        <p class="text-lg font-semibold text-purple-800">
                            {{ $dataPengajuan->where('status', 3)->count() }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            @if($dataPengajuan->count() > 0)
                <table class="w-full border border-gray-300">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                No
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Ajuan ID
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Tanggal
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Jenis Simpanan
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Nominal
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Status
                            </th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                                Aksi
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($dataPengajuan as $index => $pengajuan)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{ ($dataPengajuan->currentPage() - 1) * $dataPengajuan->perPage() + $index + 1 }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900 border-b">
                                    {{ $pengajuan->ajuan_id }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{ $pengajuan->tgl_input_formatted }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b">
                                    {{ $pengajuan->jenisSimpanan->jns_simpan ?? 'N/A' }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b">
                                    Rp {{ $pengajuan->nominal_formatted }}
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap border-b">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $pengajuan->status_badge }}">
                                        {{ $pengajuan->status_text }}
                                    </span>
                                    @if($pengajuan->status == 3 && $pengajuan->tgl_cair)
                                        <br><span class="text-xs text-gray-500">Cair: {{ $pengajuan->tgl_cair_formatted }}</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900 border-b">
                                    <div class="flex space-x-2">
                                        <!-- Detail Button -->
                                        <button onclick="showDetail({{ $pengajuan->id }})"
                                            class="text-blue-600 hover:text-blue-900">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        
                                        <!-- Cancel Button (only for pending) -->
                                        @if($pengajuan->status == 0)
                                            <form action="{{ route('member.pengajuan.penarikan.cancel', $pengajuan->id) }}" 
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pengajuan ini?')">
                                                @csrf
                                                <button type="submit" class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $dataPengajuan->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-8">
                    <div class="text-gray-400 mb-4">
                        <i class="fas fa-inbox text-6xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada pengajuan</h3>
                    <p class="text-gray-500 mb-4">Anda belum memiliki pengajuan penarikan simpanan</p>
                    <a href="{{ route('member.pengajuan.penarikan.form') }}"
                        class="bg-[#14AE5C] hover:bg-[#14AE5C]/80 text-white px-4 py-2 rounded-lg">
                        Buat Pengajuan Pertama
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Detail Modal -->
<div id="detailModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-screen overflow-y-auto">
            <div class="p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Detail Pengajuan</h3>
                    <button onclick="closeDetail()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="detailContent">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showDetail(id) {
    // Show loading
    document.getElementById('detailContent').innerHTML = `
        <div class="flex justify-center items-center py-8">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-green-600"></div>
        </div>
    `;
    
    // Show modal
    document.getElementById('detailModal').classList.remove('hidden');
    
    // Load detail content
    fetch(`/member/pengajuan/penarikan/${id}/detail`)
        .then(response => response.text())
        .then(html => {
            document.getElementById('detailContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('detailContent').innerHTML = `
                <div class="text-center py-8">
                    <p class="text-red-600">Gagal memuat detail pengajuan</p>
                </div>
            `;
        });
}

function closeDetail() {
    document.getElementById('detailModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('detailModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDetail();
    }
});
</script>
@endpush
@endsection
