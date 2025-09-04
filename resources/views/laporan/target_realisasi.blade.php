@extends('layouts.app')

@section('title', 'Laporan Target & Realisasi')
@section('sub-title', 'Laporan')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <!-- Header Panel -->
    <div class="bg-gradient-to-r from-purple-600 to-purple-700 text-white rounded-lg p-4 mb-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center">
                <i class="fas fa-chart-line text-2xl mr-3"></i>
                <div>
                    <h2 class="text-xl font-bold">Laporan Target & Realisasi</h2>
                    <p class="text-purple-100 text-sm">Analisis perbandingan target vs realisasi pinjaman anggota</p>
                </div>
            </div>
            <button onclick="toggleFilter()" class="bg-white bg-opacity-20 hover:bg-opacity-30 rounded-lg p-2 transition-all duration-200">
                <i class="fas fa-chevron-down text-lg" id="filterIcon"></i>
            </button>
        </div>
    </div>

    <!-- Filter Section -->
    <div id="filterSection" class="mb-6 bg-gray-50 rounded-lg p-4 border border-gray-200">
        <form method="GET" action="{{ route('laporan.target_realisasi') }}" class="space-y-4">
            <!-- Filter Controls -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label for="tgl_dari" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Tanggal Dari
                    </label>
                    <input type="date" id="tgl_dari" name="tgl_dari" value="{{ $tgl_dari }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div>
                    <label for="tgl_samp" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-1"></i>Tanggal Sampai
                    </label>
                    <input type="date" id="tgl_samp" name="tgl_samp" value="{{ $tgl_samp }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                </div>
                <div class="flex items-end">
                    <div class="flex gap-2">
                        <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-md hover:bg-purple-700 transition-colors duration-200 flex items-center">
                            <i class="fas fa-search mr-2"></i>Filter
                        </button>
                        <a href="{{ route('laporan.target_realisasi') }}" class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200 flex items-center">
                            <i class="fas fa-refresh mr-2"></i>Reset
                        </a>
                    </div>
                </div>
            </div>
            
            <!-- Quick Filter Presets -->
            <div class="flex flex-wrap gap-2">
                <button type="button" onclick="setPreset('today')" class="px-3 py-1 bg-purple-100 text-purple-700 rounded-full text-sm hover:bg-purple-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Hari Ini
                </button>
                <button type="button" onclick="setPreset('yesterday')" class="px-3 py-1 bg-gray-100 text-gray-700 rounded-full text-sm hover:bg-gray-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Kemarin
                </button>
                <button type="button" onclick="setPreset('week')" class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm hover:bg-blue-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>7 Hari Lalu
                </button>
                <button type="button" onclick="setPreset('month')" class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm hover:bg-green-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>30 Hari Lalu
                </button>
                <button type="button" onclick="setPreset('this_month')" class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm hover:bg-orange-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Bulan Ini
                </button>
                <button type="button" onclick="setPreset('last_month')" class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-sm hover:bg-red-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Bulan Kemarin
                </button>
                <button type="button" onclick="setPreset('year')" class="px-3 py-1 bg-indigo-100 text-indigo-700 rounded-full text-sm hover:bg-indigo-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Tahun Ini
                </button>
                <button type="button" onclick="setPreset('last_year')" class="px-3 py-1 bg-pink-100 text-pink-700 rounded-full text-sm hover:bg-pink-200 transition-colors">
                    <i class="fas fa-calendar mr-1"></i>Tahun Kemarin
                </button>
            </div>
        </form>
    </div>

    <!-- Export Buttons -->
    <div class="mb-6 flex flex-wrap gap-2">
        <a href="{{ route('laporan.target_realisasi.export.pdf', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}" 
           class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors duration-200 flex items-center">
            <i class="fas fa-file-pdf mr-2"></i>Cetak Laporan (PDF)
        </a>
        <a href="{{ route('laporan.target_realisasi.export.excel', ['tgl_dari' => $tgl_dari, 'tgl_samp' => $tgl_samp]) }}" 
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
                    <i class="fas fa-hand-holding-usd text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Total Pinjaman</p>
                    <p class="text-2xl font-bold">{{ number_format($summary['total_pinjaman']) }}</p>
                    <p class="text-xs opacity-75">Rp {{ number_format($summary['total_nilai_pinjaman']) }}</p>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-target text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Target Angsuran</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['total_target_angsuran']) }}</p>
                    <p class="text-xs opacity-75">Total target</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-check-circle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Realisasi</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['total_realisasi']) }}</p>
                    <p class="text-xs opacity-75">{{ number_format($summary['persentase_realisasi_keseluruhan'], 1) }}% achieved</p>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg p-6 transform hover:scale-105 transition-all duration-200 shadow-lg">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-white bg-opacity-20">
                    <i class="fas fa-exclamation-triangle text-2xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm opacity-90">Sisa Tagihan</p>
                    <p class="text-2xl font-bold">Rp {{ number_format($summary['total_sisa_tagihan']) }}</p>
                    <p class="text-xs opacity-75">Outstanding</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Overview Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-r from-green-500 to-green-600 text-white rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Pinjaman Lunas</p>
                    <p class="text-2xl font-bold">{{ number_format($summary['pinjaman_lunas']) }}</p>
                    <p class="text-xs opacity-75">{{ number_format($summary['persentase_pelunasan'], 1) }}% completion</p>
                </div>
                <i class="fas fa-check-circle text-3xl opacity-50"></i>
            </div>
        </div>
        
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Berjalan</p>
                    <p class="text-2xl font-bold">{{ number_format($summary['pinjaman_berjalan']) }}</p>
                    <p class="text-xs opacity-75">In progress</p>
                </div>
                <i class="fas fa-clock text-3xl opacity-50"></i>
            </div>
        </div>

        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 text-white rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Jatuh Tempo</p>
                    <p class="text-2xl font-bold">{{ number_format($summary['pinjaman_jatuh_tempo']) }}</p>
                    <p class="text-xs opacity-75">Overdue</p>
                </div>
                <i class="fas fa-exclamation-triangle text-3xl opacity-50"></i>
            </div>
        </div>

        <div class="bg-gradient-to-r from-gray-500 to-gray-600 text-white rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm opacity-90">Belum Mulai</p>
                    <p class="text-2xl font-bold">{{ number_format($summary['pinjaman_belum_mulai']) }}</p>
                    <p class="text-xs opacity-75">Not started</p>
                </div>
                <i class="fas fa-pause-circle text-3xl opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="mb-6">
        <h3 class="text-2xl font-bold text-gray-800 mb-2 text-center">
            Laporan Target & Realisasi Pinjaman Anggota
        </h3>
        <p class="text-center text-gray-600 mb-6">
            <strong>Periode:</strong> {{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} s/d {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}
        </p>
        
        @if(count($data) > 0)
        <!-- Main Report Table -->
        <div class="overflow-x-auto bg-white rounded-lg shadow-lg">
            <table class="min-w-full border border-gray-200">
                <thead class="bg-gradient-to-r from-purple-600 to-purple-700 text-white">
                    <tr>
                        <!-- Basic Info -->
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Jabatan</th>
                        
                        <!-- Target (Rencana) Section -->
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider bg-purple-800" colspan="6">
                            <i class="fas fa-bullseye mr-1"></i>TARGET (RENCANA)
                        </th>
                        
                        <!-- Realisasi (Aktual) Section -->
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider bg-green-800" colspan="6">
                            <i class="fas fa-chart-line mr-1"></i>REALISASI (AKTUAL)
                        </th>
                        
                        <!-- Performance Section -->
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider bg-blue-800" colspan="3">
                            <i class="fas fa-tachometer-alt mr-1"></i>PERFORMANCE
                        </th>
                    </tr>
                    <tr class="bg-purple-700">
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">No</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Tanggal</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Nama</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">ID</th>
                        <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider">Jabatan</th>
                        
                        <!-- Target Columns -->
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Pinjaman</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Saldo</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">JW</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">%</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Pokok</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Bunga</th>
                        
                        <!-- Realisasi Columns -->
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Angsuran Ke</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Pokok Bayar</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Bunga Bayar</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Denda</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Total Bayar</th>
                        <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider">Sisa Tagihan</th>
                        
                        <!-- Performance Columns -->
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Status</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">% Realisasi</th>
                        <th class="px-4 py-3 text-center text-xs font-medium uppercase tracking-wider">Gap</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @foreach($data as $row)
                    <tr class="hover:bg-purple-50 transition-colors duration-200">
                        <!-- Basic Info -->
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['no'] }}</td>
                        <td class="px-4 py-3 text-sm text-gray-900">{{ $row['tgl_pinjam']->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ $row['nama'] }}</td>
                        <td class="px-4 py-3 text-sm font-mono text-blue-600">{{ $row['id'] }}</td>
                        <td class="px-4 py-3 text-sm">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $row['jabatan'] == 'Pengurus' ? 'bg-purple-100 text-purple-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $row['jabatan'] }}
                            </span>
                        </td>
                        
                        <!-- Target (Rencana) Data -->
                        <td class="px-4 py-3 text-sm text-right font-semibold text-gray-900">
                            Rp {{ number_format($row['jumlah']) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">
                            Rp {{ number_format($row['sisa_pokok']) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-600">
                            {{ $row['lama_angsuran'] }} bln
                        </td>
                        <td class="px-4 py-3 text-sm text-center text-gray-600">
                            {{ $row['bunga'] }}%
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">
                            Rp {{ number_format($row['pokok_angsuran']) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-gray-600">
                            Rp {{ number_format($row['pokok_bunga']) }}
                        </td>
                        
                        <!-- Realisasi (Aktual) Data -->
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $row['bln_sudah_angsur'] > 0 ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $row['bln_sudah_angsur'] }}/{{ $row['lama_angsuran'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-semibold text-green-600">
                            Rp {{ number_format($row['total_bayar']) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-orange-600">
                            Rp {{ number_format($row['bunga_ags']) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right text-red-600">
                            Rp {{ number_format($row['denda_rp']) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-green-700">
                            Rp {{ number_format($row['realisasi_pembayaran']) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-bold {{ $row['sisa_tagihan'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                            Rp {{ number_format($row['sisa_tagihan']) }}
                        </td>
                        
                        <!-- Performance Data -->
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $row['status_badge'] == 'success' ? 'bg-green-100 text-green-800' : ($row['status_badge'] == 'warning' ? 'bg-yellow-100 text-yellow-800' : ($row['status_badge'] == 'info' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800')) }}">
                                {{ $row['status'] }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <div class="flex items-center justify-center">
                                <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                    <div class="bg-gradient-to-r from-green-400 to-green-600 h-2 rounded-full" style="width: {{ min($row['persentase_realisasi'], 100) }}%"></div>
                                </div>
                                <span class="text-xs font-semibold {{ $row['persentase_realisasi'] >= 100 ? 'text-green-600' : ($row['persentase_realisasi'] >= 50 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ number_format($row['persentase_realisasi'], 1) }}%
                                </span>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-sm text-center">
                            <span class="text-xs font-semibold {{ $row['gap_target_realisasi'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                {{ $row['gap_target_realisasi'] > 0 ? '+' : '' }}Rp {{ number_format($row['gap_target_realisasi']) }}
                            </span>
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
                        <td class="px-4 py-3 text-sm text-right font-bold text-gray-900">
                            Rp {{ number_format($summary['total_nilai_pinjaman']) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-gray-600" colspan="5">
                            <!-- Empty cells for target columns -->
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-green-700" colspan="5">
                            Rp {{ number_format($summary['total_realisasi']) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-right font-bold text-red-600">
                            Rp {{ number_format($summary['total_sisa_tagihan']) }}
                        </td>
                        <td class="px-4 py-3 text-sm text-center font-bold text-gray-600" colspan="3">
                            {{ number_format($summary['persentase_realisasi_keseluruhan'], 1) }}%
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <div class="text-center py-12 bg-gray-50 rounded-lg">
            <i class="fas fa-inbox text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data pinjaman</h3>
            <p class="text-gray-500">Tidak ada pinjaman untuk periode <strong>{{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}</strong></p>
        </div>
        @endif
    </div>

    <!-- Recent Loans Section -->
    @if(count($recentLoans) > 0)
    <div class="mt-8">
        <h4 class="text-lg font-semibold text-gray-800 mb-4 flex items-center">
            <i class="fas fa-history text-purple-500 mr-2"></i>
            Pinjaman Terbaru
        </h4>
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Pinjaman</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Anggota</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Sisa Tagihan</th>
                            <th class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($recentLoans as $loan)
                        <tr class="hover:bg-gray-50 transition-colors duration-200">
                            <td class="px-6 py-4 text-sm font-mono text-purple-600">{{ $loan['id'] }}</td>
                            <td class="px-6 py-4 text-sm font-medium text-gray-900">{{ $loan['anggota'] }}</td>
                            <td class="px-6 py-4 text-sm text-right font-semibold text-gray-900">
                                Rp {{ number_format($loan['jumlah']) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center text-gray-500">{{ $loan['tgl_pinjam'] }}</td>
                            <td class="px-6 py-4 text-sm text-center">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $loan['status'] == 'Lunas' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                    {{ $loan['status'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-right font-semibold {{ $loan['sisa_tagihan'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                Rp {{ number_format($loan['sisa_tagihan']) }}
                            </td>
                            <td class="px-6 py-4 text-sm text-center">
                                <div class="flex items-center justify-center">
                                    <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                        <div class="bg-gradient-to-r from-purple-400 to-purple-600 h-2 rounded-full" style="width: {{ min($loan['persentase'], 100) }}%"></div>
                                    </div>
                                    <span class="text-xs font-semibold text-purple-600">
                                        {{ number_format($loan['persentase'], 1) }}%
                                    </span>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Summary Footer -->
    <div class="mt-6 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border border-gray-200">
        <div class="flex flex-wrap justify-between items-center text-sm text-gray-600">
            <div class="flex items-center">
                <i class="fas fa-calendar mr-2 text-purple-500"></i>
                <span class="font-medium">Periode:</span> {{ \Carbon\Carbon::parse($tgl_dari)->format('d F Y') }} - {{ \Carbon\Carbon::parse($tgl_samp)->format('d F Y') }}
            </div>
            <div class="flex items-center">
                <i class="fas fa-hand-holding-usd mr-2 text-blue-500"></i>
                <span class="font-medium">Total Pinjaman:</span> {{ number_format($summary['total_pinjaman']) }} pinjaman
            </div>
            <div class="flex items-center">
                <i class="fas fa-chart-line mr-2 text-green-500"></i>
                <span class="font-medium">Realisasi:</span> {{ number_format($summary['persentase_realisasi_keseluruhan'], 1) }}%
            </div>
        </div>
    </div>
</div>

<!-- JavaScript for Filter Toggle and Presets -->
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

    // Date preset functions
    window.setPreset = function(preset) {
        const today = new Date();
        const tglDariInput = document.getElementById('tgl_dari');
        const tglSampInput = document.getElementById('tgl_samp');
        
        let startDate, endDate;
        
        switch(preset) {
            case 'today':
                startDate = today;
                endDate = today;
                break;
            case 'yesterday':
                startDate = new Date(today.getTime() - 24 * 60 * 60 * 1000);
                endDate = new Date(today.getTime() - 24 * 60 * 60 * 1000);
                break;
            case 'week':
                startDate = new Date(today.getTime() - 7 * 24 * 60 * 60 * 1000);
                endDate = today;
                break;
            case 'month':
                startDate = new Date(today.getTime() - 30 * 24 * 60 * 60 * 1000);
                endDate = today;
                break;
            case 'this_month':
                startDate = new Date(today.getFullYear(), today.getMonth(), 1);
                endDate = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                break;
            case 'last_month':
                startDate = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                endDate = new Date(today.getFullYear(), today.getMonth(), 0);
                break;
            case 'year':
                startDate = new Date(today.getFullYear(), 0, 1);
                endDate = new Date(today.getFullYear(), 11, 31);
                break;
            case 'last_year':
                startDate = new Date(today.getFullYear() - 1, 0, 1);
                endDate = new Date(today.getFullYear() - 1, 11, 31);
                break;
        }
        
        tglDariInput.value = startDate.toISOString().split('T')[0];
        tglSampInput.value = endDate.toISOString().split('T')[0];
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
});
</script>

<!-- Print Styles -->
<style>
@media print {
    .sidebar, .bg-[#14AE5C], button, a {
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
}
</style>
@endsection 