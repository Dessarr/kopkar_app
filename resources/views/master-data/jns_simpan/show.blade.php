@extends('layouts.app')

@section('title', 'Detail Jenis Simpanan')
@section('sub-title', 'Master Data Jenis Simpanan')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Jenis Simpanan</h1>
        <div class="flex space-x-2">
            <a href="{{ route('master-data.jns_simpan.edit', $simpan->id) }}" 
               class="inline-flex items-center gap-2 bg-green-100 hover:bg-green-200 text-green-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-edit"></i>
                Edit
            </a>
            <form action="{{ route('master-data.jns_simpan.destroy', $simpan->id) }}" method="POST" 
                  onsubmit="return confirm('Apakah Anda yakin ingin menghapus data jenis simpanan \'{{ $simpan->jns_simpan }}\'? Tindakan ini tidak dapat dibatalkan!');" 
                  class="inline">
                @csrf
                @method('DELETE')
                <button type="submit" 
                        class="inline-flex items-center gap-2 bg-red-100 hover:bg-red-200 text-red-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                    <i class="fas fa-trash"></i>
                    Hapus
                </button>
            </form>
            <a href="{{ route('master-data.jns_simpan.index') }}" 
               class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- ID -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">ID</label>
                    <p class="text-lg font-mono text-gray-900">{{ $simpan->id }}</p>
                </div>

                <!-- Jenis Simpanan -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Simpanan</label>
                    <p class="text-lg text-gray-900">{{ $simpan->jns_simpan }}</p>
                </div>

                <!-- Jumlah Minimum -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Minimum</label>
                    <p class="text-lg font-mono text-gray-900">{{ $simpan->jumlah_formatted }}</p>
                </div>

                <!-- Status Tampil -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Status Tampil</label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $simpan->tampil == 'Y' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                        <i class="fas {{ $simpan->tampil == 'Y' ? 'fa-eye' : 'fa-eye-slash' }} mr-2"></i>
                        {{ $simpan->status_text }}
                    </span>
                </div>

                <!-- Urutan -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Urutan Tampil</label>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        <i class="fas fa-sort-numeric-up mr-2"></i>
                        {{ $simpan->urut }}
                    </span>
                </div>

                <!-- Created At (jika ada) -->
                @if($simpan->created_at)
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Dibuat Pada</label>
                    <p class="text-sm text-gray-900">{{ $simpan->created_at->format('d M Y H:i:s') }}</p>
                </div>
                @endif

                <!-- Updated At (jika ada) -->
                @if($simpan->updated_at)
                <div class="bg-gray-50 p-4 rounded-lg">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Diupdate Pada</label>
                    <p class="text-sm text-gray-900">{{ $simpan->updated_at->format('d M Y H:i:s') }}</p>
                </div>
                @endif
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 pt-6 border-t mt-6">
                <a href="{{ route('master-data.jns_simpan.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali ke Daftar
                </a>
                <a href="{{ route('master-data.jns_simpan.edit', $simpan->id) }}" 
                   class="px-6 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors">
                    <i class="fas fa-edit mr-2"></i>
                    Edit Data
                </a>
                <a href="{{ route('master-data.jns_simpan.print') }}" 
                   class="px-6 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700 transition-colors">
                    <i class="fas fa-print mr-2"></i>
                    Cetak
                </a>
                <form action="{{ route('master-data.jns_simpan.destroy', $simpan->id) }}" method="POST" 
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data jenis simpanan \'{{ $simpan->jns_simpan }}\'? Tindakan ini tidak dapat dibatalkan!');" 
                      class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-6 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                        <i class="fas fa-trash mr-2"></i>
                        Hapus Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
