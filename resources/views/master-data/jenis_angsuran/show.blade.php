@extends('layouts.app')

@section('title', 'Detail Jenis Angsuran')
@section('sub-title', 'Master Data Jenis Angsuran')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Jenis Angsuran</h1>
        <div class="flex gap-2">
            <a href="{{ route('master-data.jenis_angsuran.edit', $angsuran->id) }}" 
               class="inline-flex items-center gap-2 bg-blue-100 hover:bg-blue-200 text-blue-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-edit"></i>
                Edit
            </a>
            <a href="{{ route('master-data.jenis_angsuran.index') }}" 
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
                    <i class="fas fa-calendar-alt text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-gray-900">{{ $angsuran->ket_formatted }}</h2>
                    <p class="text-sm text-gray-500">ID: {{ $angsuran->id }}</p>
                </div>
            </div>

            <!-- Detail Information -->
            <div class="max-w-2xl mx-auto">
                <div class="space-y-6">
                    <!-- Basic Information -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-info-circle text-[#14AE5C] mr-2"></i>
                            Informasi Dasar
                        </h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">Jumlah Bulan:</span>
                                    <span class="text-sm text-gray-900 font-mono font-semibold">
                                        {{ $angsuran->ket }} bulan
                                    </span>
                                </div>
                                
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">Kategori:</span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-{{ $angsuran->kategori_angsuran_badge }}-100 text-{{ $angsuran->kategori_angsuran_badge }}-800">
                                        {{ $angsuran->kategori_angsuran }}
                                    </span>
                                </div>
                            </div>
                            
                            <div class="space-y-3">
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">Status Aktif:</span>
                                    <span class="px-2 py-1 text-xs rounded-full bg-{{ $angsuran->status_aktif_badge }}-100 text-{{ $angsuran->status_aktif_badge }}-800">
                                        {{ $angsuran->status_aktif_text }}
                                    </span>
                                </div>
                                
                                <div class="flex justify-between items-center py-2 border-b border-gray-200">
                                    <span class="text-sm font-medium text-gray-600">Jangka Waktu:</span>
                                    <span class="text-sm text-gray-900">
                                        @if($angsuran->ket <= 6)
                                            ≤ 6 bulan (Pendek)
                                        @elseif($angsuran->ket <= 24)
                                            7-24 bulan (Menengah)
                                        @else
                                            > 24 bulan (Panjang)
                                        @endif
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Information -->
                    <div class="bg-blue-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-chart-line text-[#14AE5C] mr-2"></i>
                            Informasi Tambahan
                        </h3>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center py-2 border-b border-blue-200">
                                <span class="text-sm font-medium text-gray-600">Durasi dalam Hari:</span>
                                <span class="text-sm text-gray-900 font-mono">
                                    {{ number_format($angsuran->ket * 30, 0, ',', '.') }} hari
                                </span>
                            </div>
                            
                            <div class="flex justify-between items-center py-2 border-b border-blue-200">
                                <span class="text-sm font-medium text-gray-600">Durasi dalam Tahun:</span>
                                <span class="text-sm text-gray-900 font-mono">
                                    {{ number_format($angsuran->ket / 12, 1, ',', '.') }} tahun
                                </span>
                            </div>
                            
                            <div class="flex justify-between items-center py-2 border-b border-blue-200">
                                <span class="text-sm font-medium text-gray-600">Klasifikasi:</span>
                                <span class="text-sm text-gray-900">
                                    @if($angsuran->ket <= 6)
                                        Angsuran Jangka Pendek
                                    @elseif($angsuran->ket <= 24)
                                        Angsuran Jangka Menengah
                                    @else
                                        Angsuran Jangka Panjang
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- System Information -->
                    <div class="bg-gray-50 rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 flex items-center">
                            <i class="fas fa-cog text-[#14AE5C] mr-2"></i>
                            Informasi Sistem
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Dibuat:</p>
                                <p class="text-sm text-gray-900">{{ $angsuran->created_at ? $angsuran->created_at->format('d M Y H:i') : '-' }}</p>
                            </div>
                            <div>
                                <p class="text-xs text-gray-500 mb-1">Diupdate:</p>
                                <p class="text-sm text-gray-900">{{ $angsuran->updated_at ? $angsuran->updated_at->format('d M Y H:i') : '-' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="px-6 py-4 bg-gray-50 border-t flex justify-between items-center">
            <div class="flex gap-2">
                <a href="{{ route('master-data.jenis_angsuran.index') }}" 
                   class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('master-data.jenis_angsuran.edit', $angsuran->id) }}" 
                   class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition-colors duration-200">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <form action="{{ route('master-data.jenis_angsuran.destroy', $angsuran->id) }}" method="POST" 
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
