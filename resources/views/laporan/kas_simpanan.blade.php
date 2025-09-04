@extends('layouts.app')

@section('title', 'Laporan Kas Simpanan')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Header Panel -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-piggy-bank text-2xl mr-3"></i>
                <div>
                    <h2 class="text-xl font-bold">Laporan Kas Simpanan</h2>
                    <p class="text-green-100 text-sm">Laporan detail simpanan anggota per jenis simpanan</p>
                </div>
            </div>
            <button onclick="toggleFilter()" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-2 transition-all duration-200">
                <i class="fas fa-chevron-down text-lg" id="filterIcon"></i>
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div id="filterSection" class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
        <form method="GET" action="{{ route('laporan.kas_simpanan') }}" class="space-y-4">
            <!-- Filter Controls -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Periode (Bulan/Tahun)
                    </label>
                    <input type="month" id="periode" name="periode" value="{{ $periode }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                </div>
                <div class="flex items-end">
                    <div class="flex gap-2">
                        <button type="submit" class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200 flex items-center">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('laporan.kas_simpanan') }}" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200 flex items-center">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.kas_simpanan.export.pdf', ['periode' => $periode]) }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200 flex items-center">
            <i class="fas fa-file-pdf mr-2"></i>Cetak Laporan (PDF)
        </a>
        <a href="{{ route('laporan.kas_simpanan.export.excel', ['periode' => $periode]) }}" 
           class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200 flex items-center">
            <i class="fas fa-file-excel mr-2"></i>Export Excel
        </a>
        <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200 flex items-center">
            <i class="fas fa-print mr-2"></i>Print
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-users text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Anggota</p>
                    <p class="text-2xl font-bold">{{ $summary['total_anggota'] }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-arrow-up text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Setoran</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['total_simpanan']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-arrow-down text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Penarikan</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['total_penarikan']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-balance-scale text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Saldo Bersih</p>
                    <p class="text-2xl font-bold {{ $summary['saldo_bersih'] >= 0 ? 'text-green-200' : 'text-red-200' }}">
                        Rp {{ number_format($summary['saldo_bersih']) }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-gray-800 mb-2 text-center">
            Laporan Kas Simpanan Anggota
        </h3>
        <p class="text-center text-gray-600 mb-6">
            <strong>Periode:</strong> {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->format('F Y') }}
        </p>
        
        @if(count($data) > 0)
        <!-- Tabs for different savings types -->
        <div class="mb-6">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8 overflow-x-auto">
                    <button onclick="showTab('all')" id="tab-all" class="tab-button active whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-list mr-2"></i>Semua Jenis
                    </button>
                    @foreach($jenisSimpanan as $jenis)
                    <button onclick="showTab('{{ $jenis->id }}')" id="tab-{{ $jenis->id }}" class="tab-button whitespace-nowrap py-2 px-1 border-b-2 font-medium text-sm">
                        <i class="fas fa-piggy-bank mr-2"></i>{{ $jenis->jns_simpan }}
                    </button>
                    @endforeach
                </nav>
            </div>
        </div>

        <!-- Table Section -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gradient-to-r from-green-600 to-green-700 text-white">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">ID Anggota</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama Anggota</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Jabatan</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Departemen</th>
                        @foreach($jenisSimpanan as $jenis)
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider" data-jenis="{{ $jenis->id }}">
                            <div class="flex flex-col items-center">
                                <span class="font-bold">{{ $jenis->jns_simpan }}</span>
                                <div class="flex space-x-2 mt-1">
                                    <span class="text-xs bg-green-200 text-green-800 px-2 py-1 rounded">Setoran</span>
                                    <span class="text-xs bg-red-200 text-red-800 px-2 py-1 rounded">Penarikan</span>
                                </div>
                            </div>
                        </th>
                        @endforeach
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">
                            <div class="flex flex-col items-center">
                                <span class="font-bold">Total</span>
                                <div class="flex space-x-2 mt-1">
                                    <span class="text-xs bg-green-200 text-green-800 px-2 py-1 rounded">Setoran</span>
                                    <span class="text-xs bg-red-200 text-red-800 px-2 py-1 rounded">Penarikan</span>
                                </div>
                            </div>
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($data as $row)
                    <tr class="hover:bg-green-50 transition-colors duration-200">
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row['no'] }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-blue-600">{{ $row['id'] }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['nama'] }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $row['jabatan'] == 'Pengurus' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $row['jabatan'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-500">{{ $row['departemen'] }}</td>
                        @foreach($jenisSimpanan as $jenis)
                        <td class="px-4 py-3 text-center" data-jenis="{{ $jenis->id }}">
                            @if(isset($row[$jenis->id]))
                            <div class="flex flex-col space-y-1">
                                <div class="text-green-600 font-semibold text-sm">
                                    Rp {{ number_format($row[$jenis->id]['debet']) }}
                                </div>
                                <div class="text-red-600 font-semibold text-sm">
                                    Rp {{ number_format($row[$jenis->id]['kredit']) }}
                                </div>
                                <div class="text-xs text-gray-500">
                                    Saldo: Rp {{ number_format($row[$jenis->id]['saldo']) }}
                                </div>
                                @if($row[$jenis->id]['transaksi_count'] > 0)
                                <div class="text-xs text-blue-500">
                                    {{ $row[$jenis->id]['transaksi_count'] }} transaksi
                                </div>
                                @endif
                            </div>
                            @else
                            <div class="text-gray-400 text-sm">-</div>
                            @endif
                        </td>
                        @endforeach
                        <td class="px-4 py-3 text-center">
                            <div class="flex flex-col space-y-1">
                                <div class="text-green-600 font-semibold text-sm">
                                    Rp {{ number_format($row['total_simpanan']) }}
                                </div>
                                <div class="text-red-600 font-semibold text-sm">
                                    Rp {{ number_format($row['total_penarikan']) }}
                                </div>
                                <div class="text-xs font-bold {{ $row['saldo_bersih'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    Saldo: Rp {{ number_format($row['saldo_bersih']) }}
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-gradient-to-r from-gray-100 to-gray-200">
                    <tr>
                        <td class="px-4 py-3 text-sm font-bold text-gray-900" colspan="5">
                            <i class="fas fa-calculator mr-2 text-gray-600"></i>
                            <span class="font-bold text-gray-800">TOTAL</span>
                        </td>
                        @foreach($jenisSimpanan as $jenis)
                        <td class="px-4 py-3 text-center font-bold" data-jenis="{{ $jenis->id }}">
                            <div class="flex flex-col space-y-1">
                                <div class="text-green-600 text-sm">
                                    Rp {{ number_format($summary['per_jenis'][$jenis->id]['debet']) }}
                                </div>
                                <div class="text-red-600 text-sm">
                                    Rp {{ number_format($summary['per_jenis'][$jenis->id]['kredit']) }}
                                </div>
                                <div class="text-xs font-bold {{ $summary['per_jenis'][$jenis->id]['saldo'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    Rp {{ number_format($summary['per_jenis'][$jenis->id]['saldo']) }}
                                </div>
                            </div>
                        </td>
                        @endforeach
                        <td class="px-4 py-3 text-center font-bold">
                            <div class="flex flex-col space-y-1">
                                <div class="text-green-600 text-sm">
                                    Rp {{ number_format($summary['total_simpanan']) }}
                                </div>
                                <div class="text-red-600 text-sm">
                                    Rp {{ number_format($summary['total_penarikan']) }}
                                </div>
                                <div class="text-xs font-bold {{ $summary['saldo_bersih'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    Rp {{ number_format($summary['saldo_bersih']) }}
                                </div>
                            </div>
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data simpanan</h3>
            <p class="text-gray-500">Tidak ada transaksi simpanan untuk periode <strong>{{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->format('F Y') }}</strong></p>
        </div>
        @endif
    </div>

    <!-- Summary Footer -->
    @if(count($data) > 0)
    <div class="mt-6 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
        <div class="flex flex-wrap justify-between items-center text-sm text-gray-600">
            <div class="flex items-center">
                <i class="fas fa-users mr-2 text-blue-500"></i>
                <span class="font-medium">Total Anggota:</span> {{ $summary['total_anggota'] }} anggota
            </div>
            <div class="flex items-center">
                <i class="fas fa-calendar mr-2 text-purple-500"></i>
                <span class="font-medium">Periode:</span> {{ \Carbon\Carbon::createFromFormat('Y-m', $periode)->format('F Y') }}
            </div>
            <div class="flex items-center">
                <i class="fas fa-piggy-bank mr-2 text-green-500"></i>
                <span class="font-medium">Jenis Simpanan:</span> {{ count($jenisSimpanan) }} jenis
            </div>
        </div>
    </div>
    @endif
</div>

<!-- JavaScript for Filter Toggle and Tab Functionality -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize filter section as collapsed
    const filterSection = document.getElementById('filterSection');
    const filterIcon = document.getElementById('filterIcon');
    
    // Toggle filter section
    window.toggleFilter = function() {
        if (filterSection.style.display === 'none') {
            filterSection.style.display = 'block';
            filterIcon.className = 'fas fa-chevron-up text-lg';
        } else {
            filterSection.style.display = 'none';
            filterIcon.className = 'fas fa-chevron-down text-lg';
        }
    };

    // Tab functionality
    window.showTab = function(tabId) {
        // Remove active class from all tabs
        document.querySelectorAll('.tab-button').forEach(tab => {
            tab.classList.remove('active', 'border-green-500', 'text-green-600');
            tab.classList.add('border-transparent', 'text-gray-500');
        });
        
        // Add active class to clicked tab
        const activeTab = document.getElementById('tab-' + tabId);
        activeTab.classList.add('active', 'border-green-500', 'text-green-600');
        activeTab.classList.remove('border-transparent', 'text-gray-500');
        
        // Show/hide table columns based on tab
        const tableHeaders = document.querySelectorAll('th[data-jenis]');
        const tableCells = document.querySelectorAll('td[data-jenis]');
        
        if (tabId === 'all') {
            // Show all columns
            tableHeaders.forEach(header => {
                header.style.display = '';
            });
            tableCells.forEach(cell => {
                cell.style.display = '';
            });
        } else {
            // Hide all columns except selected type
            tableHeaders.forEach(header => {
                if (header.getAttribute('data-jenis') === tabId) {
                    header.style.display = '';
                } else {
                    header.style.display = 'none';
                }
            });
            tableCells.forEach(cell => {
                if (cell.getAttribute('data-jenis') === tabId) {
                    cell.style.display = '';
                } else {
                    cell.style.display = 'none';
                }
            });
        }
    };

    // Add smooth scrolling to table when filter is applied
    const form = document.querySelector('form');
    form.addEventListener('submit', function() {
        setTimeout(() => {
            const table = document.querySelector('.overflow-x-auto');
            if (table) {
                table.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }, 100);
    });

    // Add hover effects to table rows
    const tableRows = document.querySelectorAll('tbody tr');
    tableRows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'transform 0.2s ease';
        });
        
        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });

    // Initialize with 'all' tab active
    showTab('all');
});
</script>

<!-- Print Styles -->
<style>
@media print {
    .sidebar, .bg-[#14AE5C], button, a, .tab-button {
        display: none !important;
    }
    
    .bg-white {
        box-shadow: none !important;
    }
    
    table {
        page-break-inside: avoid;
    }
    
    .mb-8 {
        margin-bottom: 2rem !important;
    }
    
    th[data-jenis], td[data-jenis] {
        display: table-cell !important;
    }
}

.tab-button {
    border-color: transparent;
    color: #6b7280;
    transition: all 0.2s ease;
}

.tab-button.active {
    border-color: #10b981;
    color: #059669;
}

.tab-button:hover {
    color: #374151;
    border-color: #d1d5db;
}
</style>
@endsection