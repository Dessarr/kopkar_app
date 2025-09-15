@extends('layouts.app')

@section('title', 'Detail Data Mobil')
@section('sub-title', 'Master Data Mobil')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Data Mobil</h1>
        <div class="flex gap-2">
            <a href="{{ route('master-data.data_mobil.edit', $mobil->id) }}" 
               class="inline-flex items-center gap-2 bg-blue-100 hover:bg-blue-200 text-blue-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-edit"></i>
                Edit
            </a>
            <a href="{{ route('master-data.data_mobil.index') }}" 
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
                    <i class="fas fa-car text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $mobil->nama }}</h2>
                    <p class="text-sm text-gray-500">ID: {{ $mobil->id }}</p>
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
                            <span class="text-sm font-medium text-gray-600">Nama Mobil:</span>
                            <span class="text-sm text-gray-900 font-medium">{{ $mobil->nama }}</span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Jenis:</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $mobil->jenis ?? '-' }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Merek:</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                {{ $mobil->merek ?? '-' }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Pabrikan:</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-gray-100 text-gray-800">
                                {{ $mobil->pabrikan ?? '-' }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Warna:</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-pink-100 text-pink-800">
                                {{ $mobil->warna ?? '-' }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Tahun:</span>
                            <span class="text-sm text-gray-900 font-mono">
                                {{ $mobil->tahun_formatted }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Document Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                        <i class="fas fa-file-alt text-[#14AE5C] mr-2"></i>
                        Dokumen & Status
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">No Polisi:</span>
                            <span class="text-sm text-gray-900 font-mono">
                                {{ $mobil->no_polisi ?? '-' }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">No Rangka:</span>
                            <span class="text-sm text-gray-900 font-mono text-xs">
                                {{ $mobil->no_rangka ?? '-' }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">No Mesin:</span>
                            <span class="text-sm text-gray-900 font-mono text-xs">
                                {{ $mobil->no_mesin ?? '-' }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">No BPKB:</span>
                            <span class="text-sm text-gray-900 font-mono text-xs">
                                {{ $mobil->no_bpkb ?? '-' }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Tanggal STNK:</span>
                            <span class="text-sm text-gray-900">
                                {{ $mobil->tgl_berlaku_stnk_formatted }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Status STNK:</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $mobil->status_stnk_badge }}-100 text-{{ $mobil->status_stnk_badge }}-800">
                                {{ $mobil->status_stnk }}
                            </span>
                        </div>
                        
                        <div class="flex justify-between items-center py-2 border-b border-gray-100">
                            <span class="text-sm font-medium text-gray-600">Status Aktif:</span>
                            <span class="px-2 py-1 text-xs rounded-full bg-{{ $mobil->status_aktif_badge }}-100 text-{{ $mobil->status_aktif_badge }}-800">
                                {{ $mobil->status_aktif_text }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- File PIC -->
            @if($mobil->file_pic)
            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-image text-[#14AE5C] mr-2"></i>
                    File Gambar
                </h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-700">{{ $mobil->file_pic }}</p>
                </div>
            </div>
            @endif

            <!-- System Information -->
            <div class="mt-6 pt-6 border-t">
                <h3 class="text-lg font-semibold text-gray-900 mb-3 flex items-center">
                    <i class="fas fa-cog text-[#14AE5C] mr-2"></i>
                    Informasi Sistem
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Dibuat:</p>
                        <p class="text-sm text-gray-900">{{ $mobil->created_at ? $mobil->created_at->format('d M Y H:i') : '-' }}</p>
                    </div>
                    <div class="bg-gray-50 rounded-lg p-4">
                        <p class="text-xs text-gray-500 mb-1">Diupdate:</p>
                        <p class="text-sm text-gray-900">{{ $mobil->updated_at ? $mobil->updated_at->format('d M Y H:i') : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="px-6 py-4 bg-gray-50 border-t flex justify-between items-center">
            <div class="flex gap-2">
                <a href="{{ route('master-data.data_mobil.index') }}" 
                   class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('master-data.data_mobil.edit', $mobil->id) }}" 
                   class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors duration-200">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="{{ route('master-data.data_mobil.print') }}" 
                   class="px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors duration-200">
                    <i class="fas fa-print mr-2"></i>Cetak
                </a>
                <form action="{{ route('master-data.data_mobil.destroy', $mobil->id) }}" method="POST" 
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
