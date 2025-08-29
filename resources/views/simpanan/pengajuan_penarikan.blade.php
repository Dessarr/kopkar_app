@extends('layouts.app')

@push('styles')
<style>
    /* Simplified Filter Styles */
    .filter-section {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border: 1px solid #e2e8f0;
        transition: all 0.3s ease;
    }
    
    .filter-section:hover {
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }
    
    .filter-input {
        transition: all 0.2s ease;
        border: 1px solid #d1d5db;
    }
    
    .filter-input:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        transform: translateY(-1px);
    }
    
    .filter-select {
        transition: all 0.2s ease;
    }
    
    .filter-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
    }
    
    .filter-button {
        transition: all 0.2s ease;
        position: relative;
        overflow: hidden;
    }
    
    .filter-button:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }
    
    .filter-button:active {
        transform: translateY(0);
    }
    
    .advanced-toggle {
        transition: all 0.2s ease;
        border-radius: 6px;
        padding: 4px 8px;
    }
    
    .advanced-toggle:hover {
        background-color: #f3f4f6;
        color: #374151;
    }
    
    .advanced-filters {
        animation: slideDown 0.3s ease-out;
    }
    
    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-10px);
            max-height: 0;
        }
        to {
            opacity: 1;
            transform: translateY(0);
            max-height: 500px;
        }
    }
    
    /* Responsive adjustments */
    @media (max-width: 768px) {
        .filter-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .filter-actions {
            flex-direction: column;
            gap: 0.5rem;
        }
        
        .filter-actions > div {
            width: 100%;
        }
    }
    
    /* Custom scrollbar for select elements */
    select::-webkit-scrollbar {
        width: 6px;
    }
    
    select::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 3px;
    }
    
    select::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    select::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
</style>
@endpush

@section('title', 'Pengajuan Penarikan Simpanan')
@section('sub-title', 'Kelola Pengajuan Penarikan')

@section('content')
<div class="px-1 justify-center flex flex-col">
    @if(isset($pengajuan))
        <!-- Detail View -->
    <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Detail Pengajuan Penarikan</h1>
            <a href="{{ route('admin.pengajuan.penarikan.index') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                ← Kembali
            </a>
        </div>

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
        @endif

        <div class="bg-white rounded-lg shadow-md p-6">
            <!-- Header Info -->
            <div class="border-b border-gray-200 pb-4 mb-6">
                <div class="flex justify-between items-start">
                    <div>
                        <h2 class="text-xl font-semibold text-gray-900">{{ $pengajuan->ajuan_id }}</h2>
                        <p class="text-sm text-gray-600">Pengajuan Penarikan Simpanan</p>
                    </div>
                    <div class="text-right">
                        <span class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $pengajuan->status_badge }}">
                            {{ $pengajuan->status_text }}
                        </span>
                        <p class="text-sm text-gray-600 mt-1">Tanggal: {{ $pengajuan->tgl_input_formatted }}</p>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- Informasi Pengajuan -->
                    <div class="bg-gray-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pengajuan</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">No. Ajuan:</span>
                                <span class="text-sm text-gray-900">{{ $pengajuan->no_ajuan }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Jenis Simpanan:</span>
                                <span class="text-sm text-gray-900">{{ $pengajuan->jenisSimpanan->jns_simpan ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Nominal:</span>
                                <span class="text-sm font-semibold text-gray-900">Rp {{ $pengajuan->nominal_formatted }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Keterangan:</span>
                                <span class="text-sm text-gray-900">{{ $pengajuan->keterangan ?: '-' }}</span>
                            </div>
                            @if($pengajuan->alasan)
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Alasan:</span>
                                <span class="text-sm text-gray-900">{{ $pengajuan->alasan }}</span>
                            </div>
                            @endif
                            @if($pengajuan->tgl_cair)
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Tanggal Cair:</span>
                                <span class="text-sm text-gray-900">{{ $pengajuan->tgl_cair_formatted }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <!-- Timeline Status -->
                    <div class="bg-white border border-gray-200 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Timeline Status</h3>
                        <div class="space-y-4">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-4 h-4 bg-green-400 rounded-full"></div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Pengajuan Dibuat</p>
                                    <p class="text-sm text-gray-600">{{ $pengajuan->tgl_input_formatted }}</p>
                                </div>
                            </div>
                            

                            
                            @if($pengajuan->status == 2)
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-4 h-4 bg-red-400 rounded-full"></div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Ditolak</p>
                                    <p class="text-sm text-gray-600">{{ $pengajuan->tgl_update_formatted }}</p>
                                    @if($pengajuan->alasan)
                                        <p class="text-sm text-gray-600">Alasan: {{ $pengajuan->alasan }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            @if($pengajuan->status == 3)
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-4 h-4 bg-purple-400 rounded-full"></div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Terlaksana</p>
                                    <p class="text-sm text-gray-600">{{ $pengajuan->tgl_update_formatted }}</p>
                                    @if($pengajuan->tgl_cair)
                                        <p class="text-sm text-gray-600">Tanggal Cair: {{ $pengajuan->tgl_cair_formatted }}</p>
                                    @endif
                                </div>
                            </div>
                            @endif
                            
                            @if($pengajuan->status == 4)
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-4 h-4 bg-yellow-400 rounded-full"></div>
                                </div>
                                <div class="ml-4">
                                    <p class="text-sm font-medium text-gray-900">Dibatalkan</p>
                                    <p class="text-sm text-gray-600">{{ $pengajuan->tgl_update_formatted }}</p>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-6">
                    <!-- Informasi Member -->
                    <div class="bg-blue-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Member</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Nama:</span>
                                <span class="text-sm text-gray-900">{{ $pengajuan->anggota->nama ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">No. KTP:</span>
                                <span class="text-sm text-gray-900">{{ $pengajuan->anggota->no_ktp ?? 'N/A' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Alamat:</span>
                                <span class="text-sm text-gray-900">{{ $pengajuan->anggota->alamat ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">No. Telepon:</span>
                                <span class="text-sm text-gray-900">{{ $pengajuan->anggota->notelp ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Departemen:</span>
                                <span class="text-sm text-gray-900">{{ $pengajuan->anggota->departement ?: '-' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Jabatan:</span>
                                <span class="text-sm text-gray-900">{{ $pengajuan->anggota->jabatan_id ?: '-' }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Saldo Simpanan -->
                    <div class="bg-green-50 rounded-lg p-4">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Saldo Simpanan</h3>
                        <div class="space-y-3">
                            @php
                                $saldoSimpanan = \App\Models\TblTransSp::where('no_ktp', $pengajuan->anggota->no_ktp)
                                    ->where('jenis_id', $pengajuan->jenis)
                                    ->where('akun', 'Setoran')
                                    ->where('dk', 'D')
                                    ->sum('jumlah');
                                
                                $totalPenarikan = \App\Models\TblTransSp::where('no_ktp', $pengajuan->anggota->no_ktp)
                                    ->where('jenis_id', $pengajuan->jenis)
                                    ->where('akun', 'Penarikan')
                                    ->where('dk', 'K')
                                    ->sum('jumlah');
                                
                                $saldoTersedia = $saldoSimpanan - $totalPenarikan;
                            @endphp
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Total Setoran:</span>
                                <span class="text-sm text-gray-900">Rp {{ number_format($saldoSimpanan, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-700">Total Penarikan:</span>
                                <span class="text-sm text-gray-900">Rp {{ number_format($totalPenarikan, 0, ',', '.') }}</span>
                            </div>
                            <div class="flex justify-between border-t pt-2">
                                <span class="text-sm font-semibold text-gray-700">Saldo Tersedia:</span>
                                <span class="text-sm font-semibold text-gray-900">Rp {{ number_format($saldoTersedia, 0, ',', '.') }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="flex justify-end space-x-3">
                    @if($pengajuan->status == 0)
                        <button onclick="showApproveModal()"
                            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">
                            <i class="fas fa-check mr-2"></i>Setujui
                        </button>
                        
                        <button onclick="showRejectModal()"
                            class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">
                            <i class="fas fa-times mr-2"></i>Tolak
                        </button>
                        
                        <form action="{{ route('admin.pengajuan.penarikan.destroy', $pengajuan->id) }}" 
                            method="POST" class="inline"
                            onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengajuan ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                                <i class="fas fa-trash mr-2"></i>Hapus
                            </button>
                        </form>
                    @endif
                    

                </div>
            </div>
        </div>

        <!-- Approve Modal -->
        <div id="approveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <form id="approveForm" method="POST">
                        @csrf
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Setujui Pengajuan</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Cair</label>
                                <input type="date" name="tgl_cair" value="{{ date('Y-m-d') }}" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan (Opsional)</label>
                                <textarea name="alasan" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                    placeholder="Alasan persetujuan..."></textarea>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeApproveModal()"
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">
                                    Setujui
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <form id="rejectForm" method="POST">
                        @csrf
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tolak Pengajuan</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label>
                                <textarea name="alasan" rows="3" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                                    placeholder="Alasan penolakan..."></textarea>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeRejectModal()"
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">
                                    Tolak
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
        </div>
    </div>
    @else
        <!-- List View -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Pengajuan Penarikan Simpanan</h1>
            <div class="flex place-content-around items-center space-x-2">
                <a href="{{ route('admin.activity_logs.index') }}" 
                    class="bg-indigo-100 p-2 rounded-lg border-2 border-indigo-400 space-x-2 flex justify-around">
                    <p class="text-sm">Activity Logs</p> 
                    <i class="fas fa-clipboard-list text-indigo-600"></i>
                </a>
                <a href="{{ route('admin.logs.index') }}" 
                    class="bg-blue-100 p-2 rounded-lg border-2 border-blue-400 space-x-2 flex justify-around">
                    <p class="text-sm">Log Aktivitas</p> 
                    <i class="fas fa-history text-blue-600"></i>
                </a>
                <a href="{{ route('admin.pengajuan.penarikan.export.excel') }}" 
                    class="bg-green-100 p-2 rounded-lg border-2 border-green-400 space-x-2 flex justify-around">
                    <p class="text-sm">Export Excel</p> 
                    <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}" class="h-auto w-[20px]">
                </a>
                <a href="{{ route('admin.pengajuan.penarikan.export.pdf') }}" 
                    class="bg-red-100 p-2 rounded-lg border-2 border-red-400 space-x-2 flex justify-around">
                    <p class="text-sm">Export PDF</p> 
                    <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}" class="h-auto w-[20px]">
                </a>
            </div>
        </div>

        <!-- Filter Section Baru -->
        <div class="filter-section bg-white rounded-lg shadow-md p-4 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800">Filter</h3>
                <button type="button" onclick="resetAllFilters()" class="text-red-600 hover:text-red-800 text-sm filter-button">
                    <i class="fas fa-times mr-1"></i>Reset
                </button>
            </div>

            <form method="GET" action="{{ route('admin.pengajuan.penarikan.index') }}" id="filterForm">
                <!-- Main Filter Row -->
                <div class="filter-grid grid grid-cols-1 md:grid-cols-6 gap-3 mb-4">
                    <!-- Search -->
                    <div class="md:col-span-2">
                        <input type="text" name="search" value="{{ request('search') }}" 
                            placeholder="Cari nama, ID, KTP..."
                            class="filter-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                    </div>

                    <!-- Status -->
                    <div>
                        <select name="status_filter[]" multiple class="filter-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm" size="3">
                            <option value="0" {{ in_array('0', request('status_filter', [])) ? 'selected' : '' }}>Menunggu</option>
                            <option value="1" {{ in_array('1', request('status_filter', [])) ? 'selected' : '' }}>Disetujui</option>
                            <option value="2" {{ in_array('2', request('status_filter', [])) ? 'selected' : '' }}>Ditolak</option>
                            <option value="3" {{ in_array('3', request('status_filter', [])) ? 'selected' : '' }}>Terlaksana</option>
                            <option value="4" {{ in_array('4', request('status_filter', [])) ? 'selected' : '' }}>Batal</option>
                        </select>
                    </div>

                    <!-- Jenis Simpanan -->
                    <div>
                        <select name="jenis_filter[]" multiple class="filter-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm" size="3">
                            @if(isset($jenisSimpanan) && $jenisSimpanan->count() > 0)
                                @foreach($jenisSimpanan as $jenis)
                                    <option value="{{ $jenis->id }}" {{ in_array($jenis->id, request('jenis_filter', [])) ? 'selected' : '' }}>
                                        {{ $jenis->jns_simpan }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <!-- Date Range -->
                    <div>
                        <div class="grid grid-cols-2 gap-1">
                            <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                class="filter-input w-full px-2 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-xs">
                            <input type="date" name="date_to" value="{{ request('date_to') }}" 
                                class="filter-input w-full px-2 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-xs">
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="filter-actions flex space-x-2">
                        <button type="button" onclick="clearFilters()" class="filter-button px-3 py-2 text-gray-600 border border-gray-300 rounded-lg hover:bg-gray-50 text-sm">
                            <i class="fas fa-times"></i>
                        </button>
                        <button type="submit" class="filter-button px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 text-sm">
                            <i class="fas fa-filter mr-1"></i>Filter
                        </button>
                    </div>
                </div>

                <!-- Advanced Filters (Collapsible) -->
                <div class="border-t border-gray-200 pt-3">
                    <button type="button" onclick="toggleAdvancedFilters()" class="advanced-toggle text-sm text-gray-600 hover:text-gray-800 mb-2">
                        <i class="fas fa-chevron-down mr-1" id="advancedIcon"></i>
                        Filter Lanjutan
                    </button>
                    
                    <div id="advancedFilters" class="advanced-filters hidden">
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
                            <!-- Periode Bulan -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Periode Bulan</label>
                                <input type="month" name="periode_bulan" value="{{ request('periode_bulan') }}" 
                                    class="filter-input w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                            </div>

                            <!-- Nominal Range -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Nominal</label>
                                <div class="grid grid-cols-2 gap-1">
                                    <input type="number" name="nominal_min" value="{{ request('nominal_min') }}" 
                                        placeholder="Min" min="0"
                                        class="filter-input w-full px-2 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-xs">
                                    <input type="number" name="nominal_max" value="{{ request('nominal_max') }}" 
                                        placeholder="Max" min="0"
                                        class="filter-input w-full px-2 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-xs">
                                </div>
                            </div>

                            <!-- Departemen -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Departemen</label>
                                <select name="departemen_filter[]" multiple class="filter-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm" size="3">
                                    @if(isset($departemen) && $departemen->count() > 0)
                                        @foreach($departemen as $dept)
                                            <option value="{{ $dept }}" {{ in_array($dept, request('departemen_filter', [])) ? 'selected' : '' }}>
                                                {{ $dept }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <!-- Cabang -->
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Cabang</label>
                                <select name="cabang_filter[]" multiple class="filter-select w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm" size="3">
                                    @if(isset($cabang) && $cabang->count() > 0)
                                        @foreach($cabang as $cab)
                                            <option value="{{ $cab }}" {{ in_array($cab, request('cabang_filter', [])) ? 'selected' : '' }}>
                                                Cabang {{ $cab }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Counter -->
                <div class="text-xs text-gray-500 mt-2">
                    <span id="filterCount">0</span> filter aktif
                </div>
            </form>
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
                        <p class="text-lg font-semibold text-blue-800">{{ \App\Models\data_pengajuan_penarikan::where('status', 0)->count() }}</p>
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
                        <p class="text-lg font-semibold text-red-800">{{ \App\Models\data_pengajuan_penarikan::where('status', 2)->count() }}</p>
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
                        <p class="text-lg font-semibold text-purple-800">{{ \App\Models\data_pengajuan_penarikan::where('status', 3)->count() }}</p>
            </div>
        </div>
    </div>

            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-center">
                    <div class="p-2 bg-yellow-100 rounded-lg">
                        <i class="fas fa-ban text-yellow-600"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-yellow-600">Batal</p>
                        <p class="text-lg font-semibold text-yellow-800">{{ \App\Models\data_pengajuan_penarikan::where('status', 4)->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="overflow-x-auto">
                @if($dataPengajuan->count() > 0)
            <table class="w-full border border-gray-300 text-center">
                <thead class="bg-gray-50">
                    <tr class="text-sm align-middle w-full">
                        <th class="py-2 px-5 border">No</th>
                        <th class="p-5 border whitespace-nowrap">Ajuan ID</th>
                                <th class="p-5 border whitespace-nowrap">Nama Member</th>
                        <th class="p-5 border whitespace-nowrap">Tgl Input</th>
                                <th class="p-5 border whitespace-nowrap">Jenis Simpanan</th>
                        <th class="p-5 border whitespace-nowrap">Nominal</th>
                        <th class="p-5 border whitespace-nowrap">Keterangan</th>
                        <th class="p-5 border whitespace-nowrap">Status</th>
                        <th class="p-5 border whitespace-nowrap">Tgl Update</th>
                                <th class="p-5 border whitespace-nowrap">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                            @foreach($dataPengajuan as $index => $pengajuan)
                            <tr class="text-sm align-middle hover:bg-gray-50">
                                <td class="py-2 border">
                                    {{ ($dataPengajuan->currentPage() - 1) * $dataPengajuan->perPage() + $index + 1 }}
                                </td>
                                <td class="py-2 border font-medium">{{ $pengajuan->ajuan_id }}</td>
                                <td class="py-2 border">{{ $pengajuan->anggota->nama ?? 'N/A' }}</td>
                                <td class="py-2 border">{{ $pengajuan->tgl_input_formatted }}</td>
                                <td class="py-2 border">{{ $pengajuan->jenisSimpanan->jns_simpan ?? 'N/A' }}</td>
                                <td class="py-2 border font-medium">Rp {{ $pengajuan->nominal_formatted }}</td>
                                <td class="py-2 border">{{ Str::limit($pengajuan->keterangan, 30) }}</td>
                                <td class="py-2 border">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $pengajuan->status_badge }}">
                                        {{ $pengajuan->status_text }}
                                    </span>
                                    @if($pengajuan->status == 3 && $pengajuan->tgl_cair)
                                        <br><span class="text-xs text-gray-500">Cair: {{ $pengajuan->tgl_cair_formatted }}</span>
                                    @endif
                                </td>
                                <td class="py-2 border">{{ $pengajuan->tgl_update_formatted }}</td>
                        <td class="py-2 border">
                                    <div class="flex justify-center space-x-1">
                                        <!-- Detail Button -->
                                        <a href="{{ route('admin.pengajuan.penarikan.show', $pengajuan->id) }}"
                                            class="text-blue-600 hover:text-blue-900 p-1" title="Detail">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        
                                        <!-- Approve Button (only for pending) -->
                                        @if($pengajuan->status == 0)
                                            <button onclick="showApproveModal({{ $pengajuan->id }})"
                                                class="text-green-600 hover:text-green-900 p-1" title="Setujui">
                                                <i class="fas fa-check"></i>
                                            </button>
                                            
                                            <button onclick="showRejectModal({{ $pengajuan->id }})"
                                                class="text-red-600 hover:text-red-900 p-1" title="Tolak">
                                                <i class="fas fa-times"></i>
                                            </button>
                                            
                                            <form action="{{ route('admin.pengajuan.penarikan.destroy', $pengajuan->id) }}" 
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengajuan ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-gray-600 hover:text-gray-900 p-1" title="Hapus">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                        
                                        <!-- Process Button (only for approved) -->
                                        @if($pengajuan->status == 1)
                                            <form action="{{ route('admin.pengajuan.penarikan.approve', $pengajuan->id) }}" 
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Apakah Anda yakin ingin memproses pengajuan ini?')">
                                                @csrf
                                                <input type="hidden" name="tgl_cair" value="{{ date('Y-m-d') }}">
                                                <button type="submit" class="text-purple-600 hover:text-purple-900 p-1" title="Proses">
                                                    <i class="fas fa-play"></i>
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
    <div class="mt-5 w-full relative px-2 py-2">
        <div class="mx-auto w-fit">
                            <div class="bg-white px-4 py-1 flex flex-row rounded-full justify-center items-center space-x-2 border border-gray-300 shadow-sm">
                @for ($i = 1; $i <= $dataPengajuan->lastPage(); $i++)
                                    @if ($i == 1 || $i == $dataPengajuan->lastPage() || ($i >= $dataPengajuan->currentPage() - 1 && $i <= $dataPengajuan->currentPage() + 1))
                        <a href="{{ $dataPengajuan->appends(request()->query())->url($i) }}">
                                            <div class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $dataPengajuan->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </a>
                        @elseif ($i == 2 || $i == $dataPengajuan->lastPage() - 1)
                        <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                        @endif
                        @endfor
            </div>
        </div>

        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
                            Displaying {{ $dataPengajuan->firstItem() }} to {{ $dataPengajuan->lastItem() }} of {{ $dataPengajuan->total() }} items
                        </div>
                    </div>
                @else
                    <div class="text-center py-8">
                        <div class="text-gray-400 mb-4">
                            <i class="fas fa-inbox text-6xl"></i>
                        </div>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak ada data</h3>
                        <p class="text-gray-500">Belum ada pengajuan penarikan simpanan</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Approve Modal -->
        <div id="approveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <form id="approveForm" method="POST">
                        @csrf
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Setujui Pengajuan</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Cair</label>
                                <input type="date" name="tgl_cair" value="{{ date('Y-m-d') }}" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            </div>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan (Opsional)</label>
                                <textarea name="alasan" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                    placeholder="Alasan persetujuan..."></textarea>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeApproveModal()"
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">
                                    Setujui
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Reject Modal -->
        <div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
            <div class="flex items-center justify-center min-h-screen p-4">
                <div class="bg-white rounded-lg shadow-xl max-w-md w-full">
                    <form id="rejectForm" method="POST">
                        @csrf
                        <div class="p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Tolak Pengajuan</h3>
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span class="text-red-500">*</span></label>
                                <textarea name="alasan" rows="3" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                                    placeholder="Alasan penolakan..."></textarea>
                            </div>
                            
                            <div class="flex justify-end space-x-3">
                                <button type="button" onclick="closeRejectModal()"
                                    class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                                    Batal
                                </button>
                                <button type="submit"
                                    class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">
                                    Tolak
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
// Test function for debugging
function testFilterSystem() {
    console.log('=== Testing Filter System ===');
    
    // Check filter elements
    const elements = {
        'Tanggal Filter Button': document.getElementById('tanggalFilter'),
        'Jenis Filter Button': document.getElementById('jenisFilter'),
        'Status Filter Button': document.getElementById('statusFilter'),
        'Tanggal Dropdown': document.getElementById('tanggalDropdown'),
        'Jenis Dropdown': document.getElementById('jenisDropdown'),
        'Status Dropdown': document.getElementById('statusDropdown'),
        'Date From Input': document.getElementById('dateFrom'),
        'Date To Input': document.getElementById('dateTo'),
        'Jenis Simpanan Input': document.getElementById('jenisSimpanan'),
        'Status Value Input': document.getElementById('statusValue'),
        'Tanggal Text': document.getElementById('tanggalText'),
        'Jenis Text': document.getElementById('jenisText'),
        'Status Text': document.getElementById('statusText')
    };
    
    console.log('Element Status:');
    Object.entries(elements).forEach(([name, element]) => {
        if (element) {
            console.log(`✅ ${name}: Found`);
        } else {
            console.error(`❌ ${name}: NOT FOUND`);
        }
    });
    
    // Test dropdown toggles
    console.log('Testing dropdown toggles...');
    
    if (elements['Tanggal Filter Button']) {
        console.log('Clicking tanggal filter...');
        elements['Tanggal Filter Button'].click();
        setTimeout(() => {
            const isVisible = !elements['Tanggal Dropdown'].classList.contains('hidden');
            console.log(`Tanggal dropdown visible: ${isVisible}`);
            if (isVisible) {
                elements['Tanggal Filter Button'].click(); // Close it
            }
        }, 100);
    }
    
    console.log('=== Test Complete ===');
}

function testDatabase() {
    console.log('=== Testing Database Connection ===');
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
    button.disabled = true;
    
    // Make AJAX call to test database route
    fetch('/pengajuan-penarikan/test-database')
        .then(response => response.json())
        .then(data => {
            console.log('Database test results:', data);
            
            if (data.error) {
                console.error('❌ Database test failed:', data.error);
                alert(`Database test failed: ${data.error}`);
            } else {
                console.log('✅ Database test successful');
                console.log('Table exists:', data.table_exists);
                console.log('Total count:', data.total_count);
                console.log('Sample data:', data.sample_data);
                console.log('Database name:', data.database_name);
                
                let message = `Database: ${data.database_name}\n`;
                message += `Table exists: ${data.table_exists ? 'Yes' : 'No'}\n`;
                message += `Total records: ${data.total_count}\n`;
                message += `Connection: ${data.connection_status}`;
                
                alert(message);
            }
        })
        .catch(error => {
            console.error('❌ Database test request failed:', error);
            alert('Database test request failed. Check console for details.');
        })
        .finally(() => {
            // Restore button state
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

function testSearch() {
    console.log('=== Testing Search Functionality ===');
    const searchInput = document.querySelector('input[name="search"]');
    
    if (!searchInput) {
        console.error('Search input not found.');
        return;
    }
    
    const searchValue = searchInput.value || 'test';
    console.log('Testing search with value:', searchValue);
    
    // Show loading state
    const button = event.target;
    const originalText = button.innerHTML;
    button.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Testing...';
    button.disabled = true;
    
    // Make AJAX call to test search
    fetch(`/pengajuan-penarikan/test-search?search=${encodeURIComponent(searchValue)}`)
        .then(response => response.json())
        .then(data => {
            console.log('Search test results:', data);
            
            if (data.error) {
                console.error('❌ Search test failed:', data.error);
                alert(`Search test failed: ${data.error}`);
            } else {
                console.log('✅ Search test successful');
                console.log('Search term:', data.search_term);
                console.log('Basic search count:', data.basic_search_count);
                console.log('Relationship search count:', data.relationship_search_count);
                
                let message = `Search Test Results:\n`;
                message += `Search term: ${data.search_term}\n`;
                message += `Basic search results: ${data.basic_search_count}\n`;
                message += `Relationship search results: ${data.relationship_search_count}\n`;
                message += `Sample data count: ${data.sample_data.length}`;
                
                alert(message);
            }
        })
        .catch(error => {
            console.error('❌ Search test request failed:', error);
            alert('Search test request failed. Check console for details.');
        })
        .finally(() => {
            // Restore button state
            button.innerHTML = originalText;
            button.disabled = false;
        });
}

@if(isset($pengajuan))
    // Detail view functions
    function showApproveModal() {
        document.getElementById('approveForm').action = `{{ url('pengajuan-penarikan') }}/{{ $pengajuan->id }}/approve`;
        document.getElementById('approveModal').classList.remove('hidden');
    }

    function closeApproveModal() {
        document.getElementById('approveModal').classList.add('hidden');
    }

    function showRejectModal() {
        document.getElementById('rejectForm').action = `{{ url('pengajuan-penarikan') }}/{{ $pengajuan->id }}/reject`;
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
@else
    // List view functions
    function showApproveModal(id) {
        document.getElementById('approveForm').action = `{{ url('pengajuan-penarikan') }}/${id}/approve`;
        document.getElementById('approveModal').classList.remove('hidden');
    }

    function closeApproveModal() {
        document.getElementById('approveModal').classList.add('hidden');
    }

    function showRejectModal(id) {
        document.getElementById('rejectForm').action = `{{ url('pengajuan-penarikan') }}/${id}/reject`;
        document.getElementById('rejectModal').classList.remove('hidden');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
    }
@endif

// Simple Filter Functions
function resetFilters() {
    try {
        console.log('Reset filters called');
        
        // Reset date to current year
        const today = new Date();
        const yearStart = new Date(today.getFullYear(), 0, 1);
        const yearEnd = new Date(today.getFullYear(), 11, 31);
        
        const dateFrom = document.getElementById('dateFrom');
        const dateTo = document.getElementById('dateTo');
        const tanggalText = document.getElementById('tanggalText');
        
        if (dateFrom && dateTo && tanggalText) {
            dateFrom.value = yearStart.toISOString().split('T')[0];
            dateTo.value = yearEnd.toISOString().split('T')[0];
            tanggalText.textContent = 'Tahun Ini';
        }
        
        // Reset jenis
        const jenisSimpanan = document.getElementById('jenisSimpanan');
        const jenisText = document.getElementById('jenisText');
        
        if (jenisSimpanan && jenisText) {
            jenisSimpanan.value = '';
            jenisText.textContent = 'Semua Jenis';
        }
        
        // Reset status
        const statusValue = document.getElementById('statusValue');
        const statusText = document.getElementById('statusText');
        
        if (statusValue && statusText) {
            statusValue.value = '';
            statusText.textContent = 'Semua Status';
        }
        
        // Reset search
        const searchInput = document.querySelector('input[name="search"]');
        if (searchInput) {
            searchInput.value = '';
        }
        
        // Submit form
        const filterForm = document.getElementById('filterForm');
        if (filterForm) {
            filterForm.submit();
        }
        
    } catch (error) {
        console.error('Error in resetFilters:', error);
    }
}

// Date Filter Functions
function toggleTanggalDropdown() {
    try {
        console.log('Toggle tanggal dropdown called');
        const dropdown = document.getElementById('tanggalDropdown');
        const jenisDropdown = document.getElementById('jenisDropdown');
        const statusDropdown = document.getElementById('statusDropdown');
        
        // Close other dropdowns
        if (jenisDropdown) jenisDropdown.classList.add('hidden');
        if (statusDropdown) statusDropdown.classList.add('hidden');
        
        // Toggle tanggal dropdown
        if (dropdown) {
            dropdown.classList.toggle('hidden');
            console.log('Tanggal dropdown toggled:', !dropdown.classList.contains('hidden'));
        }
        
    } catch (error) {
        console.error('Error in toggleTanggalDropdown:', error);
    }
}

function closeTanggalDropdown() {
    try {
        const dropdown = document.getElementById('tanggalDropdown');
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
    } catch (error) {
        console.error('Error in closeTanggalDropdown:', error);
    }
}

function selectDateRange(preset) {
    try {
        console.log('Select date range called with preset:', preset);
        
        let start, end, text;
        const today = new Date();
        
        switch(preset) {
            case 'today':
                start = new Date(today);
                end = new Date(today);
                text = 'Hari Ini';
                break;
            case 'yesterday':
                start = new Date(today);
                start.setDate(today.getDate() - 1);
                end = new Date(start);
                text = 'Kemarin';
                break;
            case 'last7days':
                start = new Date(today);
                start.setDate(today.getDate() - 7);
                end = new Date(today);
                text = '7 Hari yang lalu';
                break;
            case 'last30days':
                start = new Date(today);
                start.setDate(today.getDate() - 30);
                end = new Date(today);
                text = '30 Hari yang lalu';
                break;
            case 'thismonth':
                start = new Date(today.getFullYear(), today.getMonth(), 1);
                end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
                text = 'Bulan Ini';
                break;
            case 'lastmonth':
                start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                end = new Date(today.getFullYear(), today.getMonth(), 0);
                text = 'Bulan Kemarin';
                break;
            case 'thisyear':
                start = new Date(today.getFullYear(), 0, 1);
                end = new Date(today.getFullYear(), 11, 31);
                text = 'Tahun Ini';
                break;
            case 'lastyear':
                start = new Date(today.getFullYear() - 1, 0, 1);
                end = new Date(today.getFullYear() - 1, 11, 31);
                text = 'Tahun Kemarin';
                break;
            default:
                console.error('Invalid preset:', preset);
                return;
        }
        
        console.log('Date range selected:', { start: start.toISOString().split('T')[0], end: end.toISOString().split('T')[0], text });
        
        // Update hidden inputs
        const dateFrom = document.getElementById('dateFrom');
        const dateTo = document.getElementById('dateTo');
        const tanggalText = document.getElementById('tanggalText');
        
        if (dateFrom && dateTo && tanggalText) {
            dateFrom.value = start.toISOString().split('T')[0];
            dateTo.value = end.toISOString().split('T')[0];
            tanggalText.textContent = text;
            console.log('Hidden inputs updated');
        } else {
            console.error('Some date elements not found:', { dateFrom: !!dateFrom, dateTo: !!dateTo, tanggalText: !!tanggalText });
        }
        
        // Close dropdown
        closeTanggalDropdown();
        
    } catch (error) {
        console.error('Error in selectDateRange:', error);
    }
}

function applyCustomRange() {
    try {
        console.log('Apply custom range called');
        const fromDate = document.getElementById('customFrom').value;
        const toDate = document.getElementById('customTo').value;
        
        if (fromDate && toDate) {
            const dateFrom = document.getElementById('dateFrom');
            const dateTo = document.getElementById('dateTo');
            const tanggalText = document.getElementById('tanggalText');
            
            if (dateFrom && dateTo && tanggalText) {
                dateFrom.value = fromDate;
                dateTo.value = toDate;
                tanggalText.textContent = 'Custom Range';
                console.log('Custom range applied:', { fromDate, toDate });
            }
            closeTanggalDropdown();
        } else {
            alert('Please select both start and end dates');
        }
        
    } catch (error) {
        console.error('Error in applyCustomRange:', error);
    }
}

// Jenis Filter Functions
function toggleJenisDropdown() {
    try {
        console.log('Toggle jenis dropdown called');
        const dropdown = document.getElementById('jenisDropdown');
        const tanggalDropdown = document.getElementById('tanggalDropdown');
        const statusDropdown = document.getElementById('statusDropdown');
        
        // Close other dropdowns
        if (tanggalDropdown) tanggalDropdown.classList.add('hidden');
        if (statusDropdown) statusDropdown.classList.add('hidden');
        
        // Toggle jenis dropdown
        if (dropdown) {
            dropdown.classList.toggle('hidden');
            console.log('Jenis dropdown toggled:', !dropdown.classList.contains('hidden'));
        }
        
    } catch (error) {
        console.error('Error in toggleJenisDropdown:', error);
    }
}

function closeJenisDropdown() {
    try {
        const dropdown = document.getElementById('jenisDropdown');
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
    } catch (error) {
        console.error('Error in closeJenisDropdown:', error);
    }
}

function selectJenis(value) {
    try {
        console.log('Select jenis called with value:', value);
        const jenisText = document.getElementById('jenisText');
        const jenisSimpanan = document.getElementById('jenisSimpanan');
        
        if (value === '') {
            jenisText.textContent = 'Semua Jenis';
            jenisSimpanan.value = '';
        } else {
            // Get the text from the button
            const button = event.target;
            jenisText.textContent = button.textContent.trim();
            jenisSimpanan.value = value;
        }
        
        console.log('Jenis selected:', { text: jenisText.textContent, value: jenisSimpanan.value });
        closeJenisDropdown();
        
    } catch (error) {
        console.error('Error in selectJenis:', error);
    }
}

// Status Filter Functions
function toggleStatusDropdown() {
    try {
        console.log('Toggle status dropdown called');
        const dropdown = document.getElementById('statusDropdown');
        const tanggalDropdown = document.getElementById('tanggalDropdown');
        const jenisDropdown = document.getElementById('jenisDropdown');
        
        // Close other dropdowns
        if (tanggalDropdown) tanggalDropdown.classList.add('hidden');
        if (jenisDropdown) jenisDropdown.classList.add('hidden');
        
        // Toggle status dropdown
        if (dropdown) {
            dropdown.classList.toggle('hidden');
            console.log('Status dropdown toggled:', !dropdown.classList.contains('hidden'));
        }
        
    } catch (error) {
        console.error('Error in toggleStatusDropdown:', error);
    }
}

function closeStatusDropdown() {
    try {
        const dropdown = document.getElementById('statusDropdown');
        if (dropdown) {
            dropdown.classList.add('hidden');
        }
    } catch (error) {
        console.error('Error in closeStatusDropdown:', error);
    }
}

function selectStatus(value) {
    try {
        console.log('Select status called with value:', value);
        const statusText = document.getElementById('statusText');
        const statusValue = document.getElementById('statusValue');
        
        if (value === '') {
            statusText.textContent = 'Semua Status';
            statusValue.value = '';
        } else {
            // Get the text from the button
            const button = event.target;
            statusText.textContent = button.textContent.trim();
            statusValue.value = value;
        }
        
        console.log('Status selected:', { text: statusText.textContent, value: statusValue.value });
        closeStatusDropdown();
        
    } catch (error) {
        console.error('Error in selectStatus:', error);
    }
}

// ===== SISTEM FILTER BARU =====

// Fungsi untuk menghitung jumlah filter aktif
function updateFilterCount() {
    const form = document.getElementById('filterForm');
    const formData = new FormData(form);
    let activeFilters = 0;
    
    // Check each filter type
    const filters = [
        'search', 'status_filter', 'jenis_filter', 'date_from', 'date_to', 
        'periode_bulan', 'nominal_min', 'nominal_max', 'departemen_filter', 'cabang_filter'
    ];
    
    filters.forEach(filter => {
        const value = formData.get(filter);
        if (value && value !== '' && value !== '0') {
            activeFilters++;
        }
    });
    
    // Check multiple select filters
    const multipleFilters = ['status_filter', 'jenis_filter', 'departemen_filter', 'cabang_filter'];
    multipleFilters.forEach(filter => {
        const values = formData.getAll(filter);
        if (values.length > 0 && values.some(v => v !== '')) {
            activeFilters++;
        }
    });
    
    document.getElementById('filterCount').textContent = activeFilters;
}

// Fungsi untuk membersihkan semua filter
function clearFilters() {
    const form = document.getElementById('filterForm');
    const inputs = form.querySelectorAll('input, select');
    
    inputs.forEach(input => {
        if (input.type === 'text' || input.type === 'number' || input.type === 'date' || input.type === 'month') {
            input.value = '';
        } else if (input.type === 'select-multiple') {
            Array.from(input.options).forEach(option => option.selected = false);
        }
    });
    
    updateFilterCount();
}

// Fungsi untuk reset semua filter dan redirect
function resetAllFilters() {
    if (confirm('Apakah Anda yakin ingin mereset semua filter?')) {
        window.location.href = '{{ route("admin.pengajuan.penarikan.index") }}';
    }
}

// Fungsi untuk toggle advanced filters
function toggleAdvancedFilters() {
    const advancedFilters = document.getElementById('advancedFilters');
    const advancedIcon = document.getElementById('advancedIcon');
    
    if (advancedFilters.classList.contains('hidden')) {
        advancedFilters.classList.remove('hidden');
        advancedIcon.classList.remove('fa-chevron-down');
        advancedIcon.classList.add('fa-chevron-up');
    } else {
        advancedFilters.classList.add('hidden');
        advancedIcon.classList.remove('fa-chevron-up');
        advancedIcon.classList.add('fa-chevron-down');
    }
}

// Fungsi untuk validasi form sebelum submit
function validateFilterForm() {
    const form = document.getElementById('filterForm');
    const dateFrom = form.querySelector('input[name="date_from"]').value;
    const dateTo = form.querySelector('input[name="date_to"]').value;
    const nominalMin = form.querySelector('input[name="nominal_min"]').value;
    const nominalMax = form.querySelector('input[name="nominal_max"]').value;
    
    // Validasi tanggal
    if (dateFrom && dateTo && dateFrom > dateTo) {
        alert('Tanggal awal tidak boleh lebih besar dari tanggal akhir');
        return false;
    }
    
    // Validasi nominal
    if (nominalMin && nominalMax && parseInt(nominalMin) > parseInt(nominalMax)) {
        alert('Nominal minimum tidak boleh lebih besar dari nominal maksimum');
        return false;
    }
    
    return true;
}

// Event listeners untuk form
document.addEventListener('DOMContentLoaded', function() {
    console.log('Initializing new filter system...');
    
    const form = document.getElementById('filterForm');
    
    // Update filter count on any change
    form.addEventListener('change', updateFilterCount);
    form.addEventListener('input', updateFilterCount);
    
    // Form submit validation
    form.addEventListener('submit', function(e) {
        if (!validateFilterForm()) {
            e.preventDefault();
            return false;
        }
        
        // Show loading state
        const submitBtn = form.querySelector('button[type="submit"]');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Memproses...';
        submitBtn.disabled = true;
        
        // Re-enable after a delay (in case of error)
        setTimeout(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        }, 5000);
    });
    
    // Initialize filter count
    updateFilterCount();
    
    // Add tooltips for filter help
    const tooltips = {
        'periode_bulan': 'Periode dari tanggal 21 bulan sebelumnya sampai 20 bulan berjalan',
        'nominal_min': 'Nominal minimum penarikan',
        'nominal_max': 'Nominal maksimum penarikan',
        'search': 'Cari berdasarkan nama, Ajuan ID, KTP, atau No Ajuan'
    };
    
    Object.keys(tooltips).forEach(fieldName => {
        const field = form.querySelector(`[name="${fieldName}"]`);
        if (field) {
            field.title = tooltips[fieldName];
        }
    });
    
    console.log('New filter system initialized successfully');
});

// Keyboard shortcuts
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + Enter untuk submit form
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        const form = document.getElementById('filterForm');
        if (form) {
            form.submit();
        }
    }
    
    // Ctrl/Cmd + R untuk reset
    if ((e.ctrlKey || e.metaKey) && e.key === 'r') {
        e.preventDefault();
        resetAllFilters();
    }
    
    // Escape untuk clear filters
    if (e.key === 'Escape') {
        clearFilters();
    }
});
</script>
@endpush
@endsection
