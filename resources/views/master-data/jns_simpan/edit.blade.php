@extends('layouts.app')

@section('title', 'Edit Jenis Simpanan')
@section('sub-title', 'Master Data Jenis Simpanan')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Jenis Simpanan</h1>
        <a href="{{ route('master-data.jns_simpan') }}" 
           class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <form action="{{ route('master-data.jns_simpan.update', $simpan->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Jenis Simpanan -->
                    <div>
                        <label for="jns_simpan" class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Simpanan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="jns_simpan" name="jns_simpan" 
                               value="{{ old('jns_simpan', $simpan->jns_simpan) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('jns_simpan') border-red-500 @enderror"
                               placeholder="Contoh: Simpanan Pokok" required>
                        @error('jns_simpan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jumlah Minimum -->
                    <div>
                        <label for="jumlah" class="block text-sm font-medium text-gray-700 mb-2">
                            Jumlah Minimum <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="number" id="jumlah" name="jumlah" 
                                   value="{{ old('jumlah', $simpan->jumlah) }}"
                                   step="0.01" min="0"
                                   class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('jumlah') border-red-500 @enderror"
                                   placeholder="0" required>
                        </div>
                        @error('jumlah')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Tampil -->
                    <div>
                        <label for="tampil" class="block text-sm font-medium text-gray-700 mb-2">
                            Status Tampil <span class="text-red-500">*</span>
                        </label>
                        <select id="tampil" name="tampil" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('tampil') border-red-500 @enderror" required>
                            <option value="">Pilih Status</option>
                            <option value="Y" {{ old('tampil', $simpan->tampil) == 'Y' ? 'selected' : '' }}>Tampil</option>
                            <option value="T" {{ old('tampil', $simpan->tampil) == 'T' ? 'selected' : '' }}>Tidak Tampil</option>
                        </select>
                        @error('tampil')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Urutan -->
                    <div>
                        <label for="urut" class="block text-sm font-medium text-gray-700 mb-2">
                            Urutan Tampil <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="urut" name="urut" 
                               value="{{ old('urut', $simpan->urut) }}"
                               min="1" max="99"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('urut') border-red-500 @enderror"
                               placeholder="1" required>
                        @error('urut')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Urutan tampil di form (1-99)</p>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t">
                    <a href="{{ route('master-data.jns_simpan') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors">
                        <i class="fas fa-save mr-2"></i>
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
