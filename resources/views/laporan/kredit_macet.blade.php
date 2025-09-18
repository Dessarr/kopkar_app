@extends('layouts.app')

@section('title', 'Laporan Kredit Macet')
@section('sub-title', 'Laporan')

@section('content')
<!-- Header Panel -->
<div class="bg-gradient-to-r from-red-600 to-red-700 text-white rounded-lg shadow-lg p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold flex items-center">
                <i class="fas fa-exclamation-triangle mr-3 text-yellow-300"></i>
                Cetak Laporan Kredit Macet
            </h1>
            <p class="text-red-100 mt-1">Laporan pinjaman yang telah melewati jatuh tempo pembayaran</p>
        </div>
        <button onclick="toggleCollapse()"
            class="bg-white bg-opacity-20 hover:bg-opacity-30 px-4 py-2 rounded-lg transition-all duration-200">
            <i class="fas fa-chevron-up mr-2"></i>Collapse
        </button>
    </div>
</div>

<!-- Control Panel -->
<div id="control-panel" class="bg-white rounded-lg shadow-md p-6 mb-6">
    <!-- Filter Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
        <div>
            <label for="periode" class="block text-sm font-medium text-gray-700 mb-2">
                <i class="fas fa-calendar-alt mr-2 text-blue-500"></i>Periode
            </label>
            <input type="month" id="periode" name="periode" value="{{ $periode }}"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent">
        </div>
        <div class="flex items-end">
            <button type="button" onclick="submitFilter()"
                class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                <i class="fas fa-search mr-2"></i>Cari
            </button>
        </div>
        <div class="flex items-end">
            <a href="{{ route('laporan.kredit.macet') }}"
                class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                <i class="fas fa-refresh mr-2"></i>Hapus Filter
            </a>
        </div>
    </div>

    <!-- Export Buttons -->
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('laporan.kredit.macet.export.pdf') }}?periode={{ $periode }}"
            class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200">
            <i class="fas fa-file-pdf mr-2"></i>Cetak Laporan
        </a>
    </div>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
    <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg p-6 shadow-lg"
        title="Jumlah total pinjaman yang telah melewati jatuh tempo">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-white bg-opacity-20">
                <i class="fas fa-exclamation-triangle text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm opacity-90">Total Kredit Macet</p>
                <p class="text-2xl font-bold">{{ $dataPinjaman->total() }}</p>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg p-6 shadow-lg"
        title="Total nilai tagihan dari semua kredit macet">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-white bg-opacity-20">
                <i class="fas fa-money-bill-wave text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm opacity-90">Total Tagihan</p>
                <p class="text-2xl font-bold">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6 shadow-lg"
        title="Total pembayaran yang telah diterima">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-white bg-opacity-20">
                <i class="fas fa-check-circle text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm opacity-90">Total Dibayar</p>
                <p class="text-2xl font-bold">Rp {{ number_format($totalDibayar, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <div class="bg-gradient-to-r from-purple-500 to-purple-600 text-white rounded-lg p-6 shadow-lg"
        title="Sisa tagihan yang belum dibayar">
        <div class="flex items-center">
            <div class="p-3 rounded-full bg-white bg-opacity-20">
                <i class="fas fa-balance-scale text-2xl"></i>
            </div>
            <div class="ml-4">
                <p class="text-sm opacity-90">Sisa Tagihan</p>
                <p class="text-2xl font-bold">Rp {{ number_format($totalSisa, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>
</div>

<!-- Main Content -->
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Title -->
    <div class="text-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800 flex items-center justify-center">
            <i class="fas fa-chart-line mr-3 text-red-500"></i>
            Laporan Kredit Macet
        </h2>
        <p class="text-gray-600 mt-2">
            <i class="fas fa-calendar mr-2"></i>
            Periode: {{ \Carbon\Carbon::parse($periode . '-01')->format('F Y') }}
        </p>
    </div>

    <!-- Data Table -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg overflow-hidden shadow-sm">
            <thead class="bg-gradient-to-r from-red-50 to-red-100">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                        <i class="fas fa-hashtag mr-1"></i>No.
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                        <i class="fas fa-barcode mr-1"></i>Kode Pinjam
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                        <i class="fas fa-user mr-1"></i>Nama Anggota
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                        <i class="fas fa-calendar-plus mr-1"></i>Tanggal Pinjam
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-red-700 uppercase tracking-wider">
                        <i class="fas fa-calendar-times mr-1"></i>Tanggal Tempo
                    </th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-red-700 uppercase tracking-wider">
                        <i class="fas fa-clock mr-1"></i>Lama Pinjam
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-red-700 uppercase tracking-wider">
                        <i class="fas fa-money-bill mr-1"></i>Jumlah Tagihan
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-red-700 uppercase tracking-wider">
                        <i class="fas fa-check-circle mr-1"></i>Dibayar
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-red-700 uppercase tracking-wider">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Sisa Tagihan
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($processedData as $index => $pinjaman)
                <tr class="hover:bg-red-50 transition-colors duration-200">
                    <td class="px-4 py-3 text-sm text-gray-900 font-medium">{{ $dataPinjaman->firstItem() + $index }}
                    </td>
                    <td class="px-4 py-3 text-sm font-medium text-blue-600 bg-blue-50 rounded px-2 py-1">
                        {{ $pinjaman->kode_pinjam }}</td>
                    <td class="px-4 py-3 text-sm text-gray-900">
                        <div class="flex items-center">
                            <div class="w-8 h-8 bg-gray-200 rounded-full flex items-center justify-center mr-3">
                                <i class="fas fa-user text-gray-500 text-xs"></i>
                            </div>
                            <div>
                                <div class="font-medium">{{ $pinjaman->nama_anggota }}</div>
                                <div class="text-xs text-gray-500">{{ $pinjaman->no_ktp }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded-full text-xs">
                            {{ \Carbon\Carbon::parse($pinjaman->tgl_pinjam)->format('d/m/Y') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-900">
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                            {{ \Carbon\Carbon::parse($pinjaman->tempo)->format('d/m/Y') }}
                        </span>
                        @if($pinjaman->days_overdue > 0)
                        <div class="text-xs text-red-600 mt-1">
                            <i class="fas fa-exclamation-triangle mr-1"></i>
                            {{ $pinjaman->days_overdue }} hari terlambat
                        </div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-center text-gray-900">
                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-xs">
                            {{ $pinjaman->lama_angsuran }} bulan
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">
                        <span class="bg-orange-100 text-orange-800 px-2 py-1 rounded text-xs">
                            Rp {{ number_format($pinjaman->total_tagihan, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-semibold text-gray-900 text-right">
                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                            Rp {{ number_format($pinjaman->total_bayar, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-bold text-red-600 text-right">
                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">
                            Rp {{ number_format($pinjaman->sisa_tagihan, 0, ',', '.') }}
                        </span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <i class="fas fa-check-circle text-6xl text-green-400 mb-4"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada Kredit Macet</h3>
                            <p class="text-gray-500">Semua pinjaman dalam periode ini telah dibayar tepat waktu</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
            <tfoot class="bg-gradient-to-r from-gray-50 to-gray-100">
                <tr>
                    <td colspan="6" class="px-4 py-3 text-sm font-bold text-gray-900 text-right">
                        <i class="fas fa-calculator mr-2"></i>Jumlah Total:
                    </td>
                    <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">
                        <span class="bg-orange-200 text-orange-900 px-3 py-1 rounded whitespace-nowrap">
                            Rp {{ number_format($totalTagihan, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-bold text-gray-900 text-right">
                        <span class="bg-green-200 text-green-900 px-3 py-1 rounded whitespace-nowrap">
                            Rp {{ number_format($totalDibayar, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm font-bold text-red-600 text-right">
                        <span class="bg-red-200 text-red-900 px-3 py-1 rounded whitespace-nowrap">
                            Rp {{ number_format($totalSisa, 0, ',', '.') }}
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Pagination -->
    @if($dataPinjaman->hasPages())
    <div class="mt-6">
        {{ $dataPinjaman->appends(request()->query())->links() }}
    </div>
    @endif
</div>

<!-- Information Panel -->
<div class="mt-6 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-lg p-6">
    <div class="flex items-start">
        <div class="flex-shrink-0">
            <i class="fas fa-info-circle text-2xl text-yellow-600"></i>
        </div>
        <div class="ml-4">
            <h3 class="text-lg font-medium text-yellow-800 mb-2">Informasi Penting</h3>
            <div class="text-sm text-yellow-700 space-y-2">
                <p><strong>Definisi Kredit Macet:</strong> Pinjaman yang telah melewati tanggal jatuh tempo pembayaran
                    dan belum dilunasi.</p>
                <p><strong>Dampak:</strong> Kredit macet dapat mempengaruhi kesehatan keuangan koperasi dan memerlukan
                    tindakan penanganan khusus.</p>
                <p><strong>Rekomendasi:</strong> Segera lakukan follow-up kepada anggota yang memiliki kredit macet
                    untuk melakukan pembayaran.</p>
            </div>
        </div>
    </div>
</div>

<!-- JavaScript -->
<script>
function toggleCollapse() {
    const panel = document.getElementById('control-panel');
    const button = document.querySelector('button[onclick="toggleCollapse()"]');
    const icon = button.querySelector('i');

    if (panel.style.display === 'none') {
        panel.style.display = 'block';
        icon.className = 'fas fa-chevron-up mr-2';
        button.innerHTML = '<i class="fas fa-chevron-up mr-2"></i>Collapse';
    } else {
        panel.style.display = 'none';
        icon.className = 'fas fa-chevron-down mr-2';
        button.innerHTML = '<i class="fas fa-chevron-down mr-2"></i>Expand';
    }
}

function submitFilter() {
    // Get current values from the input fields
    const periode = document.getElementById('periode').value;

    // Add loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
    button.disabled = true;

    // Build the URL with parameters
    let url = '{{ route("laporan.kredit.macet") }}';
    const params = new URLSearchParams();

    if (periode) {
        params.append('periode', periode);
    }

    if (params.toString()) {
        url += '?' + params.toString();
    }

    // Redirect to the filtered URL with delay for better UX
    setTimeout(() => {
        window.location.href = url;
    }, 500);
}

// Update form values when inputs change (for backup)
document.getElementById('periode').addEventListener('change', function() {
    document.querySelector('input[name="periode"]').value = this.value;
});

// Allow Enter key to submit filter
document.getElementById('periode').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        submitFilter();
    }
});

// Add smooth scroll to table when data loads
document.addEventListener('DOMContentLoaded', function() {
    const table = document.querySelector('table');
    if (table && table.rows.length > 1) {
        table.scrollIntoView({
            behavior: 'smooth',
            block: 'start'
        });
    }

    // Add hover effects for better UX
    const rows = document.querySelectorAll('tbody tr');
    rows.forEach(row => {
        row.addEventListener('mouseenter', function() {
            this.style.transform = 'scale(1.01)';
            this.style.transition = 'transform 0.2s ease';
        });

        row.addEventListener('mouseleave', function() {
            this.style.transform = 'scale(1)';
        });
    });
});
</script>

<!-- CSS Animations -->
<style>
@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

@keyframes slideUp {
    from {
        opacity: 1;
        transform: translateY(0);
    }

    to {
        opacity: 0;
        transform: translateY(-20px);
    }
}

/* Custom scrollbar for table */
.overflow-x-auto::-webkit-scrollbar {
    height: 8px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}

/* Pulse animation for summary cards */
@keyframes pulse {
    0% {
        transform: scale(1);
    }

    50% {
        transform: scale(1.05);
    }

    100% {
        transform: scale(1);
    }
}

.bg-gradient-to-r:hover {
    animation: pulse 0.6s ease-in-out;
}

/* Fade in animation for table rows */
tbody tr {
    animation: fadeIn 0.5s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(10px);
    }

    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Stagger animation for table rows */
tbody tr:nth-child(1) {
    animation-delay: 0.1s;
}

tbody tr:nth-child(2) {
    animation-delay: 0.2s;
}

tbody tr:nth-child(3) {
    animation-delay: 0.3s;
}

tbody tr:nth-child(4) {
    animation-delay: 0.4s;
}

tbody tr:nth-child(5) {
    animation-delay: 0.5s;
}
</style>

<!-- Print Styles -->
<style>
@media print {

    .sidebar,
    .bg-green-500,
    button,
    a {
        display: none !important;
    }

    .bg-white {
        box-shadow: none !important;
    }

    table {
        page-break-inside: avoid;
    }

    .mb-6 {
        margin-bottom: 1.5rem !important;
    }
}
</style>
@endsection