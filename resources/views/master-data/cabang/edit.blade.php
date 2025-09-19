@extends('layouts.app')

@section('title', 'Edit Data Cabang')
@section('sub-title', 'Master Data Cabang')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Data Cabang</h1>
            <p class="text-sm text-gray-600 mt-1">Ubah data cabang koperasi</p>
        </div>
        <a href="{{ route('master-data.cabang.index') }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <!-- Form Section -->
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('master-data.cabang.update', $cabang->id_cabang) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- ID Cabang -->
                <div>
                    <label for="id_cabang" class="block text-sm font-medium text-gray-700 mb-2">
                        ID Cabang <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="id_cabang" 
                           name="id_cabang" 
                           value="{{ old('id_cabang', $cabang->id_cabang) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 text-gray-600 cursor-not-allowed"
                           readonly
                           required>
                    @error('id_cabang')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">ID cabang tidak dapat diubah</p>
                </div>

                <!-- Nama Cabang -->
                <div>
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Cabang <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="nama" 
                           name="nama" 
                           value="{{ old('nama', $cabang->nama) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('nama') border-red-500 @enderror"
                           placeholder="Masukkan nama cabang"
                           maxlength="200"
                           required>
                    @error('nama')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Alamat -->
                <div class="md:col-span-2">
                    <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">
                        Alamat <span class="text-red-500">*</span>
                    </label>
                    <textarea id="alamat" 
                              name="alamat" 
                              rows="3"
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('alamat') border-red-500 @enderror"
                              placeholder="Masukkan alamat lengkap cabang"
                              maxlength="500"
                              required>{{ old('alamat', $cabang->alamat) }}</textarea>
                    @error('alamat')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Maksimal 500 karakter</p>
                </div>

                <!-- No. Telepon -->
                <div>
                    <label for="no_telp" class="block text-sm font-medium text-gray-700 mb-2">
                        No. Telepon <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="no_telp" 
                           name="no_telp" 
                           value="{{ old('no_telp', $cabang->no_telp) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('no_telp') border-red-500 @enderror"
                           placeholder="Contoh: 021-12345678"
                           maxlength="15"
                           required>
                    @error('no_telp')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Maksimal 15 karakter</p>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t border-gray-200">
                <a href="{{ route('master-data.cabang.index') }}" 
                   class="px-6 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-md transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-md transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Update Data
                </button>
            </div>
        </form>
    </div>
</div>

<!-- ID cabang read-only, tidak perlu JavaScript -->
@endsection
