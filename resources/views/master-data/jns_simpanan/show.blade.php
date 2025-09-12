@extends('layouts.app')

@section('title', 'Detail Jenis Simpanan')
@section('sub-title', 'Master Data Jenis Simpanan')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Jenis Simpanan</h1>
        <div class="flex space-x-2">
            <a href="{{ route('master-data.jns_simpan.edit', $simpan->id) }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-edit mr-2"></i>Edit
            </a>
            <a href="{{ route('master-data.jns_simpan') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Kembali
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informasi Dasar -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Informasi Dasar</h3>
                    
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-tag text-green-600 w-5"></i>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Jenis Simpanan</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $simpan->jns_simpan }}</p>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <i class="fas fa-money-bill-wave text-green-600 w-5"></i>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Jumlah Minimum</label>
                            <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($simpan->jumlah, 0, ',', '.') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Status dan Urutan -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-800 border-b pb-2">Status & Urutan</h3>
                    
                    <div class="flex items-center space-x-3">
                        <i class="fas fa-eye text-green-600 w-5"></i>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Status Tampil</label>
                            <div class="mt-1">
                                <span class="px-3 py-1 text-sm rounded-full {{ $simpan->tampil == 'Y' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                    {{ $simpan->tampil == 'Y' ? 'Tampil' : 'Tidak Tampil' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center space-x-3">
                        <i class="fas fa-sort-numeric-up text-green-600 w-5"></i>
                        <div>
                            <label class="text-sm font-medium text-gray-500">Urutan</label>
                            <p class="text-lg font-semibold text-gray-900">{{ $simpan->urut }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 pt-6 mt-6 border-t">
                <a href="{{ route('master-data.jns_simpan') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Daftar
                </a>
                <a href="{{ route('master-data.jns_simpan.edit', $simpan->id) }}" 
                   class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors duration-200">
                    <i class="fas fa-edit mr-2"></i>Edit Data
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
