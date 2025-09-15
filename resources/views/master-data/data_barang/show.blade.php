@extends('layouts.app')

@section('title', 'Detail Data Barang')
@section('sub-title', 'Master Data Barang')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Data Barang</h1>
        <div class="flex gap-2">
            <a href="{{ route('master-data.data_barang.edit', $barang->id) }}" 
               class="inline-flex items-center gap-2 bg-blue-100 hover:bg-blue-200 text-blue-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-edit"></i>
                Edit
            </a>
            <a href="{{ route('master-data.data_barang.index') }}" 
               class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <!-- Header Info -->
            <div class="flex items-center gap-4 mb-6 pb-4 border-b">
                <div class="w-16 h-16 bg-[#14AE5C] rounded-lg flex items-center justify-center">
                    <i class="fas fa-box text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $barang->nm_barang }}</h2>
                    <p class="text-sm text-gray-500">ID: {{ $barang->id }}</p>
                </div>
            </div>

            <!-- Detail Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-info-circle text-[#14AE5C] mr-2"></i>
                        Informasi Dasar
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Nama Barang:</span>
                            <span class="text-sm text-gray-900 font-medium">{{ $barang->nm_barang }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Type:</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $barang->type }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Merk:</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                {{ $barang->merk }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">ID Cabang:</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                {{ $barang->id_cabang ?? '-' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Pricing & Stock Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-chart-line text-[#14AE5C] mr-2"></i>
                        Harga & Stok
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Harga:</span>
                            <span class="text-sm text-gray-900 font-mono font-semibold">
                                {{ $barang->harga_formatted }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Jumlah Barang:</span>
                            <span class="text-sm text-gray-900 font-mono font-semibold">
                                {{ number_format($barang->jml_brg, 0, ',', '.') }} unit
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Status Stok:</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $barang->status_stok_badge }}-100 text-{{ $barang->status_stok_badge }}-800">
                                {{ $barang->status_stok }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Total Nilai:</span>
                            <span class="text-sm text-gray-900 font-mono font-semibold">
                                {{ 'Rp ' . number_format($barang->harga * $barang->jml_brg, 0, ',', '.') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Keterangan -->
            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-comment text-[#14AE5C] mr-2"></i>
                    Keterangan
                </h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-700">{{ $barang->ket }}</p>
                </div>
            </div>

            <!-- System Information -->
            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-cog text-[#14AE5C] mr-2"></i>
                    Informasi Sistem
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Dibuat:</p>
                        <p class="text-sm text-gray-900">{{ $barang->created_at ? $barang->created_at->format('d M Y H:i') : '-' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Diupdate:</p>
                        <p class="text-sm text-gray-900">{{ $barang->updated_at ? $barang->updated_at->format('d M Y H:i') : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="px-6 py-4 bg-gray-50 border-t flex justify-between items-center">
            <div class="flex gap-2">
                <a href="{{ route('master-data.data_barang.index') }}" 
                   class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('master-data.data_barang.edit', $barang->id) }}" 
                   class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors duration-200">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('master-data.data_barang.print') }}" 
                   class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-print mr-2"></i>Cetak
                </a>
                <form action="{{ route('master-data.data_barang.destroy', $barang->id) }}" method="POST" 
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition-colors duration-200">
                        <i class="fas fa-trash mr-2"></i>Hapus
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
