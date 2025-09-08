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

    .filter-actions>div {
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
                    <span
                        class="inline-flex px-3 py-1 text-sm font-semibold rounded-full {{ $pengajuan->status_badge }}">
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
                            <span
                                class="text-sm text-gray-900">{{ $pengajuan->jenisSimpanan->jns_simpan ?? 'N/A' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Nominal:</span>
                            <span class="text-sm font-semibold text-gray-900">Rp
                                {{ $pengajuan->nominal_formatted }}</span>
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
                            <span class="text-sm text-gray-900">Rp
                                {{ number_format($saldoSimpanan, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm font-medium text-gray-700">Total Penarikan:</span>
                            <span class="text-sm text-gray-900">Rp
                                {{ number_format($totalPenarikan, 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between border-t pt-2">
                            <span class="text-sm font-semibold text-gray-700">Saldo Tersedia:</span>
                            <span class="text-sm font-semibold text-gray-900">Rp
                                {{ number_format($saldoTersedia, 0, ',', '.') }}</span>
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

                <button onclick="showRejectModal()" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">
                    <i class="fas fa-times mr-2"></i>Tolak
                </button>

                <form action="{{ route('admin.pengajuan.penarikan.destroy', $pengajuan->id) }}" method="POST"
                    class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengajuan ini?')">
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span
                                    class="text-red-500">*</span></label>
                            <textarea name="alasan" rows="3" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="Alasan penolakan..."></textarea>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeRejectModal()"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                                Batal
                            </button>
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">
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
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Filter Data Pengajuan</h2>

        <form action="{{ route('admin.pengajuan.penarikan.index') }}" method="GET" id="filterForm">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-4">
                <!-- Filter Tanggal -->
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Tanggal</label>
                    <div class="relative">
                        <button type="button" id="tanggalBtn"
                            class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-md bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span id="tanggalText">Semua Tanggal</span>
                            <i class="fas fa-calendar text-gray-400"></i>
                        </button>
                        <div id="tanggalDropdown"
                            class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                            <div class="p-2 space-y-1">
                                <button type="button"
                                    class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                    data-value="today">Hari ini</button>
                                <button type="button"
                                    class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                    data-value="yesterday">Kemarin</button>
                                <button type="button"
                                    class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                    data-value="7days">7 Hari yang lalu</button>
                                <button type="button"
                                    class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                    data-value="30days">30 Hari yang lalu</button>
                                <button type="button"
                                    class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                    data-value="thisMonth">Bulan ini</button>
                                <button type="button"
                                    class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                    data-value="lastMonth">Bulan kemarin</button>
                                <button type="button"
                                    class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                    data-value="thisYear">Tahun ini</button>
                                <button type="button"
                                    class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                    data-value="lastYear">Tahun kemarin</button>
                                <hr class="my-2">
                                <div class="p-2">
                                    <div class="mb-2">
                                        <label class="block text-xs text-gray-600 mb-1">FROM:</label>
                                        <input type="date" id="dateFrom" name="date_from"
                                            value="{{ request('date_from') }}"
                                            class="w-full px-2 py-1 text-xs border border-gray-300 rounded">
                                    </div>
                                    <div class="mb-2">
                                        <label class="block text-xs text-gray-600 mb-1">TO:</label>
                                        <input type="date" id="dateTo" name="date_to" value="{{ request('date_to') }}"
                                            class="w-full px-2 py-1 text-xs border border-gray-300 rounded">
                                    </div>
                                    <div class="flex space-x-1">
                                        <button type="button" id="cancelDate"
                                            class="flex-1 px-2 py-1 text-xs bg-gray-300 text-gray-700 rounded hover:bg-gray-400">Cancel</button>
                                        <button type="button" id="applyDate"
                                            class="flex-1 px-2 py-1 text-xs bg-green-500 text-white rounded hover:bg-green-600">Apply</button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filter Jenis -->
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Jenis</label>
                    <div class="relative">
                        <button type="button" id="jenisBtn"
                            class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-md bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span id="jenisText">Semua Jenis</span>
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </button>
                        <div id="jenisDropdown"
                            class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                            <div class="p-2">
                                <input type="text" id="jenisSearch" placeholder="Cari jenis..."
                                    class="w-full px-2 py-1 text-sm border border-gray-300 rounded mb-2">
                                <div class="space-y-1 max-h-40 overflow-y-auto">
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="">Semua Jenis</button>
                                    @if(isset($jenisSimpanan) && $jenisSimpanan->count() > 0)
                                    @foreach($jenisSimpanan as $jenis)
                                    <button type="button"
                                        class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                        data-value="{{ $jenis->id }}">{{ $jenis->jns_simpan }}</button>
                                    @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="jenis" id="jenisHidden" value="{{ request('jenis') }}">
                </div>

                <!-- Filter Status -->
                <div class="relative">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Filter Status</label>
                    <div class="relative">
                        <button type="button" id="statusBtn"
                            class="w-full flex items-center justify-between px-3 py-2 border border-gray-300 rounded-md bg-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <span id="statusText">Semua Status</span>
                            <i class="fas fa-chevron-down text-gray-400"></i>
                        </button>
                        <div id="statusDropdown"
                            class="hidden absolute z-50 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg">
                            <div class="p-2 space-y-1">
                                <button type="button"
                                    class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                    data-value="">Semua Status</button>
                                <button type="button"
                                    class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                    data-value="0">Menunggu</button>
                                <button type="button"
                                    class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                    data-value="1">Disetujui</button>
                                <button type="button"
                                    class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                    data-value="2">Ditolak</button>
                                <button type="button"
                                    class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                    data-value="3">Terlaksana</button>
                                <button type="button"
                                    class="w-full text-left px-2 py-1 text-sm hover:bg-gray-100 rounded"
                                    data-value="4">Batal</button>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="status" id="statusHidden" value="{{ request('status') }}">
                </div>

                <!-- Pencarian Anggota -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Pencarian Anggota</label>
                    <input type="text" name="search" id="anggotaSearch" placeholder="Anggota"
                        value="{{ request('search') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-between items-center">
                <div class="flex space-x-2">
                    <button type="submit"
                        class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <i class="fas fa-filter mr-2"></i>FILTER
                    </button>
                    <button type="button" onclick="resetFilter()"
                        class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                        <i class="fas fa-refresh mr-2"></i>Reset
                    </button>
                </div>
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
                    <p class="text-lg font-semibold text-blue-800">
                        {{ \App\Models\data_pengajuan_penarikan::where('status', 0)->count() }}</p>
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
                        {{ \App\Models\data_pengajuan_penarikan::where('status', 2)->count() }}</p>
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
                        {{ \App\Models\data_pengajuan_penarikan::where('status', 3)->count() }}</p>
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
                    <p class="text-lg font-semibold text-yellow-800">
                        {{ \App\Models\data_pengajuan_penarikan::where('status', 4)->count() }}</p>
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
                            <span
                                class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $pengajuan->status_badge }}">
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

                                <!-- Approve Button (always active) -->
                                <button onclick="showApproveModal('{{ $pengajuan->id }}')"
                                    class="text-green-600 hover:text-green-900 p-1" title="Setujui">
                                    <i class="fas fa-check"></i>
                                </button>

                                <!-- Reject Button (always active) -->
                                <button onclick="showRejectModal('{{ $pengajuan->id }}')"
                                    class="text-red-600 hover:text-red-900 p-1" title="Tolak">
                                    <i class="fas fa-times"></i>
                                </button>

                                <!-- Delete Button (always active) -->
                                <form action="{{ route('admin.pengajuan.penarikan.destroy', $pengajuan->id) }}"
                                    method="POST" class="inline"
                                    onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengajuan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-gray-600 hover:text-gray-900 p-1" title="Hapus">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Pagination -->
            <div class="mt-5 w-full relative px-2 py-2">
                <div class="mx-auto w-fit">
                    <div
                        class="bg-white px-4 py-1 flex flex-row rounded-full justify-center items-center space-x-2 border border-gray-300 shadow-sm">
                        @for ($i = 1; $i <= $dataPengajuan->lastPage(); $i++)
                            @if ($i == 1 || $i == $dataPengajuan->lastPage() || ($i >= $dataPengajuan->currentPage() - 1
                            && $i <= $dataPengajuan->currentPage() + 1))
                                <a href="{{ $dataPengajuan->appends(request()->query())->url($i) }}">
                                    <div
                                        class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $dataPengajuan->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
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
                    Displaying {{ $dataPengajuan->firstItem() }} to {{ $dataPengajuan->lastItem() }} of
                    {{ $dataPengajuan->total() }} items
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
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alasan Penolakan <span
                                    class="text-red-500">*</span></label>
                            <textarea name="alasan" rows="3" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500"
                                placeholder="Alasan penolakan..."></textarea>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <button type="button" onclick="closeRejectModal()"
                                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">
                                Batal
                            </button>
                            <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">
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
// Modal functions - Define globally first
function showApproveModal(id) {
    const approveForm = document.getElementById('approveForm');
    const approveModal = document.getElementById('approveModal');

    if (approveForm && approveModal) {
        if (id) {
            approveForm.action = `{{ url('pengajuan-penarikan') }}/${id}/approve`;
        } else {
            approveForm.action =
                `{{ url('pengajuan-penarikan') }}/{{ isset($pengajuan) ? $pengajuan->id : '' }}/approve`;
        }
        approveModal.classList.remove('hidden');
    }
}

function closeApproveModal() {
    const approveModal = document.getElementById('approveModal');
    if (approveModal) {
        approveModal.classList.add('hidden');
    }
}

function showRejectModal(id) {
    const rejectForm = document.getElementById('rejectForm');
    const rejectModal = document.getElementById('rejectModal');

    if (rejectForm && rejectModal) {
        if (id) {
            rejectForm.action = `{{ url('pengajuan-penarikan') }}/${id}/reject`;
        } else {
            rejectForm.action = `{{ url('pengajuan-penarikan') }}/{{ isset($pengajuan) ? $pengajuan->id : '' }}/reject`;
        }
        rejectModal.classList.remove('hidden');
    }
}

function closeRejectModal() {
    const rejectModal = document.getElementById('rejectModal');
    if (rejectModal) {
        rejectModal.classList.add('hidden');
    }
}

// Filter functionality
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded - Initializing filters and modals');

    // Initialize current filter values
    initializeFilters();

    // Setup dropdown toggles
    setupDropdowns();

    // Setup date filter functionality
    setupDateFilter();

    // Setup jenis filter functionality
    setupJenisFilter();

    // Setup status filter functionality
    setupStatusFilter();

    // Setup search functionality
    setupSearchFilter();

    console.log('All functions initialized successfully');
});

function initializeFilters() {
    // Set current filter values from URL parameters
    const urlParams = new URLSearchParams(window.location.search);

    // Set tanggal filter
    const dateFrom = urlParams.get('date_from');
    const dateTo = urlParams.get('date_to');
    if (dateFrom && dateTo) {
        document.getElementById('tanggalText').textContent = `${dateFrom} - ${dateTo}`;
        document.getElementById('dateFrom').value = dateFrom;
        document.getElementById('dateTo').value = dateTo;
    }

    // Set jenis filter
    const jenis = urlParams.get('jenis');
    if (jenis) {
        const jenisText = document.querySelector(`button[data-value="${jenis}"]`);
        if (jenisText) {
            document.getElementById('jenisText').textContent = jenisText.textContent;
            document.getElementById('jenisHidden').value = jenis;
        }
    }

    // Set status filter
    const status = urlParams.get('status');
    if (status !== null && status !== '') {
        const statusMap = {
            '0': 'Menunggu',
            '1': 'Disetujui',
            '2': 'Ditolak',
            '3': 'Terlaksana',
            '4': 'Batal'
        };
        document.getElementById('statusText').textContent = statusMap[status] || 'Semua Status';
        document.getElementById('statusHidden').value = status;
    }
}

function setupDropdowns() {
    // Close dropdowns when clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.relative')) {
            closeAllDropdowns();
        }
    });
}

function setupDateFilter() {
    const tanggalBtn = document.getElementById('tanggalBtn');
    const tanggalDropdown = document.getElementById('tanggalDropdown');
    const dateFrom = document.getElementById('dateFrom');
    const dateTo = document.getElementById('dateTo');
    const applyDate = document.getElementById('applyDate');
    const cancelDate = document.getElementById('cancelDate');

    tanggalBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        tanggalDropdown.classList.toggle('hidden');
        closeOtherDropdowns('tanggalDropdown');
    });

    // Predefined date options
    tanggalDropdown.addEventListener('click', function(e) {
        if (e.target.tagName === 'BUTTON' && !e.target.id) {
            const value = e.target.dataset.value;
            const today = new Date();
            let fromDate, toDate;

            switch (value) {
                case 'today':
                    fromDate = toDate = today.toISOString().split('T')[0];
                    break;
                case 'yesterday':
                    const yesterday = new Date(today);
                    yesterday.setDate(yesterday.getDate() - 1);
                    fromDate = toDate = yesterday.toISOString().split('T')[0];
                    break;
                case '7days':
                    const weekAgo = new Date(today);
                    weekAgo.setDate(weekAgo.getDate() - 7);
                    fromDate = weekAgo.toISOString().split('T')[0];
                    toDate = today.toISOString().split('T')[0];
                    break;
                case '30days':
                    const monthAgo = new Date(today);
                    monthAgo.setDate(monthAgo.getDate() - 30);
                    fromDate = monthAgo.toISOString().split('T')[0];
                    toDate = today.toISOString().split('T')[0];
                    break;
                case 'thisMonth':
                    fromDate = new Date(today.getFullYear(), today.getMonth(), 1).toISOString().split('T')[0];
                    toDate = today.toISOString().split('T')[0];
                    break;
                case 'lastMonth':
                    const lastMonth = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                    fromDate = lastMonth.toISOString().split('T')[0];
                    toDate = new Date(today.getFullYear(), today.getMonth(), 0).toISOString().split('T')[0];
                    break;
                case 'thisYear':
                    fromDate = new Date(today.getFullYear(), 0, 1).toISOString().split('T')[0];
                    toDate = today.toISOString().split('T')[0];
                    break;
                case 'lastYear':
                    fromDate = new Date(today.getFullYear() - 1, 0, 1).toISOString().split('T')[0];
                    toDate = new Date(today.getFullYear() - 1, 11, 31).toISOString().split('T')[0];
                    break;
            }

            if (fromDate && toDate) {
                dateFrom.value = fromDate;
                dateTo.value = toDate;
                document.getElementById('tanggalText').textContent = `${fromDate} - ${toDate}`;
                tanggalDropdown.classList.add('hidden');
            }
        }
    });

    applyDate.addEventListener('click', function() {
        if (dateFrom.value && dateTo.value) {
            document.getElementById('tanggalText').textContent = `${dateFrom.value} - ${dateTo.value}`;
            tanggalDropdown.classList.add('hidden');
        }
    });

    cancelDate.addEventListener('click', function() {
        dateFrom.value = '';
        dateTo.value = '';
        document.getElementById('tanggalText').textContent = 'Semua Tanggal';
        tanggalDropdown.classList.add('hidden');
    });
}

function setupJenisFilter() {
    const jenisBtn = document.getElementById('jenisBtn');
    const jenisDropdown = document.getElementById('jenisDropdown');
    const jenisSearch = document.getElementById('jenisSearch');
    const jenisHidden = document.getElementById('jenisHidden');

    jenisBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        jenisDropdown.classList.toggle('hidden');
        closeOtherDropdowns('jenisDropdown');
    });

    jenisDropdown.addEventListener('click', function(e) {
        if (e.target.tagName === 'BUTTON' && !e.target.id) {
            const value = e.target.dataset.value;
            const text = e.target.textContent;

            jenisHidden.value = value;
            document.getElementById('jenisText').textContent = text;
            jenisDropdown.classList.add('hidden');
            // Auto submit form when jenis is selected
            document.getElementById('filterForm').submit();
        }
    });

    // Search functionality
    jenisSearch.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const buttons = jenisDropdown.querySelectorAll('button');

        buttons.forEach(button => {
            const text = button.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                button.style.display = 'block';
            } else {
                button.style.display = 'none';
            }
        });
    });
}

function setupStatusFilter() {
    const statusBtn = document.getElementById('statusBtn');
    const statusDropdown = document.getElementById('statusDropdown');
    const statusHidden = document.getElementById('statusHidden');

    statusBtn.addEventListener('click', function(e) {
        e.stopPropagation();
        statusDropdown.classList.toggle('hidden');
        closeOtherDropdowns('statusDropdown');
    });

    statusDropdown.addEventListener('click', function(e) {
        if (e.target.tagName === 'BUTTON' && !e.target.id) {
            const value = e.target.dataset.value;
            const text = e.target.textContent;

            statusHidden.value = value;
            document.getElementById('statusText').textContent = text;
            statusDropdown.classList.add('hidden');
            // Auto submit form when status is selected
            document.getElementById('filterForm').submit();
        }
    });
}

function closeAllDropdowns() {
    document.getElementById('tanggalDropdown').classList.add('hidden');
    document.getElementById('jenisDropdown').classList.add('hidden');
    document.getElementById('statusDropdown').classList.add('hidden');
}

function closeOtherDropdowns(currentDropdown) {
    const dropdowns = ['tanggalDropdown', 'jenisDropdown', 'statusDropdown'];
    dropdowns.forEach(dropdown => {
        if (dropdown !== currentDropdown) {
            document.getElementById(dropdown).classList.add('hidden');
        }
    });
}

function setupSearchFilter() {
    const searchInput = document.getElementById('anggotaSearch');
    let searchTimeout;

    searchInput.addEventListener('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            // Auto submit form after 500ms of no typing
            document.getElementById('filterForm').submit();
        }, 500);
    });
}

function resetFilter() {
    // Reset all filter values
    document.getElementById('tanggalText').textContent = 'Semua Tanggal';
    document.getElementById('jenisText').textContent = 'Semua Jenis';
    document.getElementById('statusText').textContent = 'Semua Status';
    document.getElementById('anggotaSearch').value = '';
    document.getElementById('dateFrom').value = '';
    document.getElementById('dateTo').value = '';
    document.getElementById('jenisHidden').value = '';
    document.getElementById('statusHidden').value = '';

    // Redirect to base URL
    window.location.href = '{{ route("admin.pengajuan.penarikan.index") }}';
}
</script>
@endpush
@endsection