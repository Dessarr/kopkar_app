@extends('layouts.app')

@section('title', 'Toserda Lain-lain')
@section('sub-title', 'Upload & Laporan Toserda')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Alert Messages -->
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg relative mb-6 shadow-sm" role="alert">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6 shadow-sm" role="alert">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    </div>
    @endif

    @if(isset($error))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg relative mb-6 shadow-sm" role="alert">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span class="block sm:inline">{{ $error }}</span>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-blue-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-upload text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Upload</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $transaksi->total() ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-green-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Sudah Billing</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $transaksi->where('status_billing', 'Y')->count() ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-yellow-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-clock text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Belum Billing</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $transaksi->where('status_billing', '!=', 'Y')->count() ?? 0 }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-white rounded-xl shadow-lg p-6 border-l-4 border-purple-500">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Nilai</p>
                    <p class="text-2xl font-semibold text-gray-900">Rp{{ number_format($transaksi->sum('jumlah') ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Action Cards -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
        <!-- Upload File Excel -->
        <div class="bg-gradient-to-br from-blue-50 to-blue-100 rounded-2xl shadow-xl p-10 border border-blue-200">
            <div class="flex items-center mb-8">
                <div class="p-4 rounded-full bg-blue-500 text-white">
                    <i class="fas fa-cloud-upload-alt text-4xl"></i>
                </div>
                <div class="ml-6">
                    <h2 class="text-3xl font-bold text-gray-800">Upload Data Toserda</h2>
                    <p class="text-lg text-gray-600">Upload file Excel untuk data transaksi</p>
                </div>
            </div>
            
            <form action="{{ route('toserda.upload.store') }}" method="POST" enctype="multipart/form-data" class="space-y-8">
                @csrf
                <div class="relative">
                    <label for="file" class="block text-lg font-semibold text-gray-700 mb-3">Pilih File Excel</label>
                    <div class="relative">
                        <input type="file" name="file" id="file" required accept=".xlsx,.xls"
                            class="block w-full text-lg text-gray-500 file:mr-4 file:py-3 file:px-6 file:rounded-full file:border-0 file:text-lg file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 rounded-lg border border-gray-300 p-4">
                    </div>
                    <p class="text-sm text-gray-500 mt-3 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Format yang didukung: .xlsx, .xls
                    </p>
                </div>
                
                <button type="submit" class="w-full bg-gradient-to-r from-blue-500 to-blue-600 text-white py-5 px-8 rounded-xl text-lg font-semibold hover:from-blue-600 hover:to-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                    <i class="fas fa-upload mr-3 text-xl"></i>
                    Upload Data
                </button>
            </form>
        </div>

        <!-- Proses Billing Bulanan -->
        <div class="bg-gradient-to-br from-orange-50 to-orange-100 rounded-2xl shadow-xl p-10 border border-orange-200">
            <div class="flex items-center mb-8">
                <div class="p-4 rounded-full bg-orange-500 text-white">
                    <i class="fas fa-calculator text-4xl"></i>
                </div>
                <div class="ml-6">
                    <h2 class="text-3xl font-bold text-gray-800">Proses Billing Bulanan</h2>
                    <p class="text-lg text-gray-600">Hitung total belanja per anggota</p>
                </div>
            </div>
            
            <form action="{{ route('toserda.billing.process') }}" method="POST" class="space-y-8">
                @csrf
                <div class="grid grid-cols-2 gap-6">
                    <div>
                        <label for="billing_bulan" class="block text-lg font-semibold text-gray-700 mb-3">Bulan</label>
                        <select name="bulan" id="billing_bulan" required
                            class="w-full rounded-lg border border-gray-300 px-6 py-4 text-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                            @foreach($bulanList as $key => $bulan)
                            <option value="{{ $key }}" {{ date('m') == $key ? 'selected' : '' }}>{{ $bulan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="billing_tahun" class="block text-lg font-semibold text-gray-700 mb-3">Tahun</label>
                        <input type="number" name="tahun" id="billing_tahun" value="{{ date('Y') }}" required
                            class="w-full rounded-lg border border-gray-300 px-6 py-4 text-lg focus:border-orange-500 focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                    </div>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-orange-500 to-orange-600 text-white py-5 px-8 rounded-xl text-lg font-semibold hover:from-orange-600 hover:to-orange-700 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                    <i class="fas fa-cogs mr-3 text-xl"></i>
                    Proses Billing
                </button>
                
                <div class="bg-orange-50 border border-orange-200 rounded-lg p-6">
                    <p class="text-base text-orange-700 flex items-center mb-2">
                        <i class="fas fa-lightbulb mr-3 text-lg"></i>
                        Proses ini akan menghitung total belanja per anggota dan memperbarui status billing menjadi "Sudah Billing"
                    </p>
                    <p class="text-sm text-orange-600 flex items-center">
                        <i class="fas fa-info-circle mr-2"></i>
                        Setelah proses selesai, data akan langsung masuk ke Billing Utama dan Anda akan diarahkan ke halaman tersebut
                    </p>
                </div>
            </form>
        </div>

    </div>

    <!-- Data Transaksi Section -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
        <!-- Header Section -->
        <div class="bg-gradient-to-r from-gray-800 to-gray-900 px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="p-3 rounded-full bg-white bg-opacity-20 text-white">
                        <i class="fas fa-table text-2xl"></i>
                    </div>
                    <div class="ml-4">
                        <h2 class="text-2xl font-bold text-white">Data Transaksi Toserda</h2>
                        <p class="text-gray-300">Kelola dan monitor data transaksi toserda</p>
                    </div>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-300">Total Data</p>
                    <p class="text-2xl font-bold text-white">{{ $transaksi->total() ?? 0 }}</p>
                </div>
            </div>
        </div>

        <!-- Filter Section -->
        <div class="bg-gray-50 px-8 py-6 border-b border-gray-200">
            <form action="{{ route('toserda.lain-lain') }}" method="GET" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div>
                    <label for="filter_bulan" class="block text-sm font-semibold text-gray-700 mb-2">Bulan</label>
                    <select name="bulan" id="filter_bulan"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <option value="">Semua</option>
                        @foreach($bulanList as $key => $bulan)
                        <option value="{{ $key }}" {{ request('bulan') == $key ? 'selected' : '' }}>{{ $bulan }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="filter_tahun" class="block text-sm font-semibold text-gray-700 mb-2">Tahun</label>
                    <input type="number" name="tahun" id="filter_tahun" value="{{ request('tahun', date('Y')) }}"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                </div>

                <div>
                    <label for="search" class="block text-sm font-semibold text-gray-700 mb-2">Cari Anggota</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}"
                        placeholder="Nama atau No KTP"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                </div>

                <div>
                    <label for="billing_status" class="block text-sm font-semibold text-gray-700 mb-2">Status Billing</label>
                    <select name="billing_status" id="billing_status"
                        class="w-full rounded-lg border border-gray-300 px-4 py-3 focus:border-green-500 focus:ring-2 focus:ring-green-500 focus:ring-offset-2">
                        <option value="">Semua</option>
                        <option value="billed" {{ request('billing_status') == 'billed' ? 'selected' : '' }}>Sudah Billing</option>
                        <option value="unbilled" {{ request('billing_status') == 'unbilled' ? 'selected' : '' }}>Belum Billing</option>
                    </select>
                </div>

                <div class="flex items-end space-x-2">
                    <button type="submit" class="flex-1 bg-gradient-to-r from-green-500 to-green-600 text-white py-3 px-6 rounded-lg font-semibold hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                        <i class="fas fa-search mr-2"></i>
                        Filter
                    </button>
                    <a href="{{ route('toserda.lain-lain') }}" class="flex-1 bg-gray-500 text-white py-3 px-6 rounded-lg font-semibold hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg text-center">
                        <i class="fas fa-refresh mr-2"></i>
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Data -->
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-calendar-alt mr-2"></i>Tanggal
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-id-card mr-2"></i>No KTP
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-money-bill-wave mr-2"></i>Jumlah
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-receipt mr-2"></i>Status Billing
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                            <i class="fas fa-credit-card mr-2"></i>Status Pembayaran
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transaksi as $tr)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                        <i class="fas fa-calendar text-blue-600 text-sm"></i>
                                    </div>
                                </div>
                                <div class="ml-3">
                                    <div class="text-sm font-medium text-gray-900">
                                        @if(isset($tr->tgl_transaksi) && $tr->tgl_transaksi)
                                            {{ is_object($tr->tgl_transaksi) ? $tr->tgl_transaksi->format('d/m/Y') : $tr->tgl_transaksi }}
                                        @else
                                            <span class="text-gray-400">N/A</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $tr->no_ktp ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-bold text-green-600">
                                Rp{{ number_format($tr->jumlah ?? 0, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(isset($tr->status_billing) && $tr->status_billing == 'Y')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Sudah Billing
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                <i class="fas fa-clock mr-1"></i>
                                Belum Billing
                            </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if(isset($tr->status_billing) && $tr->status_billing == 'Y' && isset($tr->tgl_bayar) && $tr->tgl_bayar)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Sudah Bayar
                            </span>
                            @elseif(isset($tr->status_billing) && $tr->status_billing == 'Y')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                                <i class="fas fa-exclamation-circle mr-1"></i>
                                Belum Bayar
                            </span>
                            @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-500">
                                <i class="fas fa-minus-circle mr-1"></i>
                                Belum Ditagih
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
                                    <i class="fas fa-inbox text-gray-400 text-2xl"></i>
                                </div>
                                <h3 class="text-lg font-medium text-gray-900 mb-2">Belum ada data transaksi</h3>
                                <p class="text-gray-500">Upload data Excel untuk melihat transaksi toserda</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="bg-gray-50 px-8 py-4 border-t border-gray-200">
            @if($transaksi && method_exists($transaksi, 'links'))
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-700">
                        Menampilkan {{ $transaksi->firstItem() ?? 0 }} sampai {{ $transaksi->lastItem() ?? 0 }} dari {{ $transaksi->total() ?? 0 }} data
                    </div>
                    <div class="flex space-x-2">
                        {{ $transaksi->links() }}
                    </div>
                </div>
            @else
                <div class="text-center text-gray-500">
                    <i class="fas fa-info-circle mr-2"></i>
                    Pagination tidak tersedia
                </div>
            @endif
        </div>
    </div>
</div>

<!-- Custom CSS untuk animasi -->
<style>
    .hover\:scale-105:hover {
        transform: scale(1.05);
    }
    
    .transition-all {
        transition: all 0.2s ease-in-out;
    }
    
    .duration-200 {
        transition-duration: 200ms;
    }
    
    .transform {
        transform: translateZ(0);
    }
</style>
@endsection