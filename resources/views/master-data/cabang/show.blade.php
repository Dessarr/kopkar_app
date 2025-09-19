@extends('layouts.app')

@section('title', 'Detail Data Cabang')
@section('sub-title', 'Master Data Cabang')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Data Cabang</h1>
            <p class="text-sm text-gray-600 mt-1">Informasi lengkap data cabang koperasi</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('master-data.cabang.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ route('master-data.cabang.edit', $cabang->id_cabang) }}" 
               class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-edit"></i>
                <span>Edit</span>
            </a>
        </div>
    </div>

    <!-- Detail Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Information -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Informasi Cabang</h3>
                </div>
                <div class="p-6">
                    <dl class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID Cabang</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-semibold">{{ $cabang->id_cabang }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nama Cabang</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $cabang->nama }}</dd>
                        </div>
                        
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Alamat</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $cabang->alamat }}</dd>
                        </div>
                        
                        <div>
                            <dt class="text-sm font-medium text-gray-500">No. Telepon</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                <a href="tel:{{ $cabang->no_telp }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $cabang->no_telp }}
                                </a>
                            </dd>
                        </div>
                    </dl>
                </div>
            </div>
        </div>

        <!-- Actions & Quick Info -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Aksi Cepat</h3>
                </div>
                <div class="p-6 space-y-3">
                    <a href="{{ route('master-data.cabang.edit', $cabang->id_cabang) }}" 
                       class="w-full bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-md flex items-center justify-center space-x-2 transition-colors">
                        <i class="fas fa-edit"></i>
                        <span>Edit Data</span>
                    </a>
                    
                    <form action="{{ route('master-data.cabang.destroy', $cabang->id_cabang) }}" 
                          method="POST" 
                          onsubmit="return confirm('Apakah Anda yakin ingin menghapus data cabang ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-md flex items-center justify-center space-x-2 transition-colors">
                            <i class="fas fa-trash"></i>
                            <span>Hapus Data</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Kontak</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-phone text-gray-400"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Telepon</p>
                            <a href="tel:{{ $cabang->no_telp }}" 
                               class="text-sm text-blue-600 hover:text-blue-800">
                                {{ $cabang->no_telp }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Location Information -->
            <div class="bg-white rounded-lg shadow">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Lokasi</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-start space-x-3">
                        <div class="flex-shrink-0">
                            <i class="fas fa-map-marker-alt text-gray-400"></i>
                        </div>
                        <div>
                            <p class="text-sm font-medium text-gray-900">Alamat</p>
                            <p class="text-sm text-gray-600 mt-1">{{ $cabang->alamat }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Additional Information -->
    <div class="mt-6">
        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Informasi Tambahan</h3>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Status</h4>
                        <div class="flex items-center space-x-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                <i class="fas fa-check-circle mr-1"></i>
                                Aktif
                            </span>
                        </div>
                    </div>
                    
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-3">Terakhir Diperbarui</h4>
                        <p class="text-sm text-gray-600">{{ now()->format('d F Y, H:i') }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
