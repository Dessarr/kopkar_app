@extends('layouts.app')

@section('title', 'Detail Jenis Akun')
@section('sub-title', 'Detail Data Jenis Akun')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Jenis Akun</h1>
            <p class="text-gray-600 mt-1">Informasi lengkap jenis akun {{ $akun->kd_aktiva }}</p>
        </div>
        <div class="flex space-x-2">
            <a href="{{ route('master-data.jns_akun.edit', $akun->id) }}" 
               class="inline-flex items-center gap-2 bg-green-100 hover:bg-green-200 text-green-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-edit"></i>
                Edit
            </a>
            <form action="{{ route('master-data.jns_akun.destroy', $akun->id) }}" method="POST" 
                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus data jenis akun \'{{ $akun->kd_aktiva }}\'? Tindakan ini tidak dapat dibatalkan!');" 
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="inline-flex items-center gap-2 bg-red-100 hover:bg-red-200 text-red-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-trash"></i>
                    Hapus
                </button>
            </form>
            <a href="{{ route('master-data.jns_akun.index') }}" 
               class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Informasi Jenis Akun</h2>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Kode Aktiva -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-code mr-2"></i>Kode Aktiva
                    </label>
                    <p class="text-lg font-mono text-gray-900">{{ $akun->kd_aktiva }}</p>
                </div>

                <!-- Jenis Transaksi -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-exchange-alt mr-2"></i>Jenis Transaksi
                    </label>
                    <p class="text-lg text-gray-900">{{ $akun->jns_trans }}</p>
                </div>

                <!-- Akun -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag mr-2"></i>Tipe Akun
                    </label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        {{ $akun->akun }}
                    </span>
                </div>

                <!-- Laba Rugi -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-chart-line mr-2"></i>Laba Rugi
                    </label>
                    <p class="text-lg text-gray-900">{{ $akun->laba_rugi ?? '-' }}</p>
                </div>

                <!-- Pemasukan -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-arrow-up mr-2"></i>Pemasukan
                    </label>
                    @if($akun->pemasukan)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check mr-1"></i>Ya
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <i class="fas fa-times mr-1"></i>Tidak
                        </span>
                    @endif
                </div>

                <!-- Pengeluaran -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-arrow-down mr-2"></i>Pengeluaran
                    </label>
                    @if($akun->pengeluaran)
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check mr-1"></i>Ya
                        </span>
                    @else
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                            <i class="fas fa-times mr-1"></i>Tidak
                        </span>
                    @endif
                </div>

                <!-- Status -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-toggle-on mr-2"></i>Status
                    </label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $akun->aktif ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        <i class="fas {{ $akun->aktif ? 'fa-check-circle' : 'fa-times-circle' }} mr-1"></i>
                        {{ $akun->status_text }}
                    </span>
                </div>
            </div>

            <!-- Summary Card -->
            <div class="mt-8 bg-gradient-to-r from-[#14AE5C] to-[#11994F] rounded-lg p-6 text-white">
                <h3 class="text-lg font-semibold mb-4">
                    <i class="fas fa-info-circle mr-2"></i>Ringkasan
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $akun->kd_aktiva }}</div>
                        <div class="text-sm opacity-90">Kode Aktiva</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold">{{ $akun->akun }}</div>
                        <div class="text-sm opacity-90">Tipe Akun</div>
                    </div>
                    <div class="text-center">
                        <div class="text-2xl font-bold">
                            @if($akun->pemasukan && $akun->pengeluaran)
                                Pemasukan & Pengeluaran
                            @elseif($akun->pemasukan)
                                Pemasukan
                            @elseif($akun->pengeluaran)
                                Pengeluaran
                            @else
                                Tidak Ada
                            @endif
                        </div>
                        <div class="text-sm opacity-90">Jenis Transaksi</div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 pt-6 border-t mt-6">
                <a href="{{ route('master-data.jns_akun.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Daftar
                </a>
                <a href="{{ route('master-data.jns_akun.edit', $akun->id) }}" 
                   class="px-6 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Data
                </a>
                <a href="{{ route('master-data.jns_akun.print') }}" 
                   class="px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                    <i class="fas fa-print mr-2"></i>
                    Cetak
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
