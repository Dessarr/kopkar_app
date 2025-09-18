@extends('layouts.app')

@section('title', 'Laporan Pengeluaran Pinjaman')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Header Section -->
    <div class="mb-6">
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-4 rounded-lg shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold">Laporan Pengeluaran Pinjaman</h1>
                    <p class="text-blue-100 mt-1">Monitoring dan analisis pengeluaran pinjaman anggota</p>
                </div>
                <div class="text-right">
                    <div class="text-sm text-blue-100">Periode Laporan</div>
                    <div class="text-lg font-semibold">
                        {{ \Carbon\Carbon::parse($tgl_dari)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d M Y') }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="mb-6">
        <div class="bg-gray-50 rounded-lg p-4">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">
                    <i class="fas fa-filter mr-2 text-blue-600"></i>Filter Laporan
                </h3>
                <button onclick="toggleFilter()" class="text-blue-600 hover:text-blue-800 transition-colors">
                    <i class="fas fa-chevron-down" id="filter-icon"></i>
                </button>
            </div>
            
            <div id="filter-content" class="hidden">
                <form method="GET" action="{{ route('laporan.pengeluaran_pinjaman') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        <div>
                            <label for="tgl_dari" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Dari</label>
                            <input type="date" id="tgl_dari" name="tgl_dari" value="{{ $tgl_dari }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label for="tgl_samp" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Sampai</label>
                            <input type="date" id="tgl_samp" name="tgl_samp" value="{{ $tgl_samp }}" 
                                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Preset Periode</label>
                            <select onchange="setPreset(this.value)" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                <option value="">Pilih Preset</option>
                                <option value="today">Hari ini</option>
                                <option value="yesterday">Kemarin</option>
                                <option value="7days">7 Hari yang lalu</option>
                                <option value="30days">30 Hari yang lalu</option>
                                <option value="this_month">Bulan ini</option>
                                <option value="last_month">Bulan kemarin</option>
                                <option value="this_year">Tahun ini</option>
                                <option value="last_year">Tahun kemarin</option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                                <i class="fas fa-search mr-2"></i>Filter
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mb-6 flex flex-wrap gap-3">
        <a href="{{ route('laporan.pengeluaran_pinjaman.export.pdf', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200 shadow-sm">
            <i class="fas fa-file-pdf mr-2"></i>Export PDF
        </a>
        <a href="{{ route('laporan.pengeluaran_pinjaman') }}" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200 shadow-sm">
            <i class="fas fa-refresh mr-2"></i>Reset Filter
        </a>
    </div>


    <!-- Main Report Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-table mr-2 text-blue-600"></i>Data Pinjaman Anggota
            </h3>
            <p class="text-sm text-gray-600 mt-1">Detail pengeluaran pinjaman periode {{ \Carbon\Carbon::parse($tgl_dari)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d M Y') }}</p>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Pinjam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Pinjaman</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jaminan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Alamat</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Telp</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($data as $row)
                    <tr class="hover:bg-gray-50 transition-colors duration-150">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $row['no'] }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['tgl_pinjam']->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{!! $row['nama'] !!}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">Rp {{ number_format($row['jumlah'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $row['jaminan'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $row['alamat'] }}</td>
                        <td class="px-6 py-4 text-sm text-gray-900">{{ $row['notelp'] }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-4"></i>
                            <div class="text-lg font-medium">Tidak ada data pinjaman</div>
                            <div class="text-sm">Tidak ada data pinjaman untuk periode yang dipilih</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
                @if(count($data) > 0)
                <tfoot class="bg-gray-50">
                    <tr class="font-semibold">
                        <td class="px-6 py-4 text-center" colspan="3">TOTAL</td>
                        <td class="px-6 py-4 text-right">Rp {{ number_format($total['total_pinjaman'], 0, ',', '.') }}</td>
                        <td class="px-6 py-4" colspan="3"></td>
                    </tr>
                </tfoot>
                @endif
            </table>
        </div>
    </div>

</div>

<script>
function toggleFilter() {
    const content = document.getElementById('filter-content');
    const icon = document.getElementById('filter-icon');
    
    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        icon.classList.remove('fa-chevron-down');
        icon.classList.add('fa-chevron-up');
    } else {
        content.classList.add('hidden');
        icon.classList.remove('fa-chevron-up');
        icon.classList.add('fa-chevron-down');
    }
}

function setPreset(value) {
    const today = new Date();
    const tglDari = document.getElementById('tgl_dari');
    const tglSamp = document.getElementById('tgl_samp');
    
    switch(value) {
        case 'today':
            tglDari.value = today.toISOString().split('T')[0];
            tglSamp.value = today.toISOString().split('T')[0];
            break;
        case 'yesterday':
            const yesterday = new Date(today);
            yesterday.setDate(yesterday.getDate() - 1);
            tglDari.value = yesterday.toISOString().split('T')[0];
            tglSamp.value = yesterday.toISOString().split('T')[0];
            break;
        case '7days':
            const weekAgo = new Date(today);
            weekAgo.setDate(weekAgo.getDate() - 7);
            tglDari.value = weekAgo.toISOString().split('T')[0];
            tglSamp.value = today.toISOString().split('T')[0];
            break;
        case '30days':
            const monthAgo = new Date(today);
            monthAgo.setDate(monthAgo.getDate() - 30);
            tglDari.value = monthAgo.toISOString().split('T')[0];
            tglSamp.value = today.toISOString().split('T')[0];
            break;
        case 'this_month':
            tglDari.value = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
            tglSamp.value = today.toISOString().split('T')[0];
            break;
        case 'last_month':
            tglDari.value = new Date(today.getFullYear(), today.getMonth() - 1, 1).toISOString().split('T')[0];
            tglSamp.value = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().split('T')[0];
            break;
        case 'this_year':
            tglDari.value = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
            tglSamp.value = today.toISOString().split('T')[0];
            break;
        case 'last_year':
            tglDari.value = new Date(today.getFullYear() - 1, 0, 1).toISOString().split('T')[0];
            tglSamp.value = new Date(today.getFullYear() - 1, 11, 31).toISOString().split('T')[0];
            break;
    }
}

// Smooth scrolling for better UX
document.addEventListener('DOMContentLoaded', function() {
    // Add smooth scrolling to all anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add hover effects to table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-1px)';
            this.style.boxShadow = '0 4px 8px rgba(0,0,0,0.1)';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = 'none';
        });
    });
});
</script>
@endsection