@extends('layouts.app')

@section('title', 'Tambah Data Pengguna')
@section('sub-title', 'Tambah User Baru')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Tambah Data Pengguna</h1>
        <a href="{{ route('master-data.data_pengguna') }}" 
           class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('master-data.data_pengguna.store') }}" method="POST" class="p-6">
            @csrf
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Username -->
                <div class="md:col-span-2">
                    <label for="u_name" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user mr-2"></i>Username <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="u_name" name="u_name" value="{{ old('u_name') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('u_name') border-red-500 @enderror"
                           placeholder="Masukkan username" required>
                    @error('u_name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="md:col-span-2">
                    <label for="pass_word" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-lock mr-2"></i>Password <span class="text-red-500">*</span>
                    </label>
                    <input type="password" id="pass_word" name="pass_word" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('pass_word') border-red-500 @enderror"
                           placeholder="Masukkan password (minimal 6 karakter)" required>
                    @error('pass_word')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Level -->
                <div>
                    <label for="level" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-user-tag mr-2"></i>Level <span class="text-red-500">*</span>
                    </label>
                    <select id="level" name="level" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('level') border-red-500 @enderror" required>
                        <option value="">Pilih Level</option>
                        <option value="admin" {{ old('level') == 'admin' ? 'selected' : '' }}>Administrator</option>
                        <option value="pinjaman" {{ old('level') == 'pinjaman' ? 'selected' : '' }}>Staff Pinjaman</option>
                        <option value="simpanan" {{ old('level') == 'simpanan' ? 'selected' : '' }}>Staff Simpanan</option>
                        <option value="kas" {{ old('level') == 'kas' ? 'selected' : '' }}>Staff Kas</option>
                        <option value="laporan" {{ old('level') == 'laporan' ? 'selected' : '' }}>Staff Laporan</option>
                        <option value="supervisor" {{ old('level') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                        <option value="manager" {{ old('level') == 'manager' ? 'selected' : '' }}>Manager</option>
                    </select>
                    @error('level')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Cabang -->
                <div>
                    <label for="id_cabang" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building mr-2"></i>Cabang <span class="text-red-500">*</span>
                    </label>
                    <select id="id_cabang" name="id_cabang" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('id_cabang') border-red-500 @enderror" required>
                        <option value="">Pilih Cabang</option>
                        @foreach($cabangs as $cabang)
                            <option value="{{ $cabang->id_cabang }}" {{ old('id_cabang') == $cabang->id_cabang ? 'selected' : '' }}>
                                {{ $cabang->nama }}
                            </option>
                        @endforeach
                    </select>
                    @error('id_cabang')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Aktif -->
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-toggle-on mr-2"></i>Status Aktif <span class="text-red-500">*</span>
                    </label>
                    <div class="flex space-x-4">
                        <label class="flex items-center">
                            <input type="radio" name="aktif" value="Y" {{ old('aktif', 'Y') == 'Y' ? 'checked' : '' }} 
                                   class="h-4 w-4 text-[#14AE5C] focus:ring-[#14AE5C] border-gray-300">
                            <span class="ml-2 text-sm text-gray-700">Aktif</span>
                        </label>
                        <label class="flex items-center">
                            <input type="radio" name="aktif" value="N" {{ old('aktif') == 'N' ? 'checked' : '' }} 
                                   class="h-4 w-4 text-[#14AE5C] focus:ring-[#14AE5C] border-gray-300">
                            <span class="ml-2 text-sm text-gray-700">Tidak Aktif</span>
                        </label>
                    </div>
                    @error('aktif')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 flex justify-end space-x-4">
                <a href="{{ route('master-data.data_pengguna') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                    <i class="fas fa-save mr-2"></i>Simpan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection