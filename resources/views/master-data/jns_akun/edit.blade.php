@extends('layouts.app')

@section('title', 'Edit Jenis Akun')
@section('sub-title', 'Edit Data Jenis Akun')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Jenis Akun</h1>
            <p class="text-gray-600 mt-1">Edit data jenis akun {{ $akun->kd_aktiva }}</p>
        </div>
        <a href="{{ route('master-data.jns_akun.index') }}" 
           class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <form action="{{ route('master-data.jns_akun.update', $akun->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kode Aktiva -->
                    <div>
                        <label for="kd_aktiva" class="block text-sm font-medium text-gray-700 mb-2">
                            Kode Aktiva <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="kd_aktiva" name="kd_aktiva" value="{{ old('kd_aktiva', $akun->kd_aktiva) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('kd_aktiva') border-red-500 @enderror"
                               placeholder="Masukkan kode aktiva" maxlength="10" required>
                        @error('kd_aktiva')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jenis Transaksi -->
                    <div>
                        <label for="jns_trans" class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Transaksi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="jns_trans" name="jns_trans" value="{{ old('jns_trans', $akun->jns_trans) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('jns_trans') border-red-500 @enderror"
                               placeholder="Masukkan jenis transaksi" required>
                        @error('jns_trans')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Akun -->
                    <div>
                        <label for="akun" class="block text-sm font-medium text-gray-700 mb-2">
                            Akun <span class="text-red-500">*</span>
                        </label>
                        <select id="akun" name="akun" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('akun') border-red-500 @enderror" required>
                            <option value="">Pilih Tipe Akun</option>
                            <option value="Aktiva" {{ old('akun', $akun->akun) == 'Aktiva' ? 'selected' : '' }}>Aktiva</option>
                            <option value="Pasiva" {{ old('akun', $akun->akun) == 'Pasiva' ? 'selected' : '' }}>Pasiva</option>
                            <option value="Pendapatan" {{ old('akun', $akun->akun) == 'Pendapatan' ? 'selected' : '' }}>Pendapatan</option>
                            <option value="Biaya" {{ old('akun', $akun->akun) == 'Biaya' ? 'selected' : '' }}>Biaya</option>
                            <option value="Modal" {{ old('akun', $akun->akun) == 'Modal' ? 'selected' : '' }}>Modal</option>
                        </select>
                        @error('akun')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Laba Rugi -->
                    <div>
                        <label for="laba_rugi" class="block text-sm font-medium text-gray-700 mb-2">
                            Laba Rugi
                        </label>
                        <input type="text" id="laba_rugi" name="laba_rugi" value="{{ old('laba_rugi', $akun->laba_rugi) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('laba_rugi') border-red-500 @enderror"
                               placeholder="Masukkan laba rugi" maxlength="50">
                        @error('laba_rugi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pemasukan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pemasukan <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="pemasukan" value="1" {{ old('pemasukan', $akun->pemasukan) == '1' ? 'checked' : '' }}
                                       class="mr-2 text-[#14AE5C] focus:ring-[#14AE5C]">
                                <span class="text-sm text-gray-700">Ya</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="pemasukan" value="0" {{ old('pemasukan', $akun->pemasukan) == '0' ? 'checked' : '' }}
                                       class="mr-2 text-[#14AE5C] focus:ring-[#14AE5C]">
                                <span class="text-sm text-gray-700">Tidak</span>
                            </label>
                        </div>
                        @error('pemasukan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pengeluaran -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pengeluaran <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="pengeluaran" value="1" {{ old('pengeluaran', $akun->pengeluaran) == '1' ? 'checked' : '' }}
                                       class="mr-2 text-[#14AE5C] focus:ring-[#14AE5C]">
                                <span class="text-sm text-gray-700">Ya</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="pengeluaran" value="0" {{ old('pengeluaran', $akun->pengeluaran) == '0' ? 'checked' : '' }}
                                       class="mr-2 text-[#14AE5C] focus:ring-[#14AE5C]">
                                <span class="text-sm text-gray-700">Tidak</span>
                            </label>
                        </div>
                        @error('pengeluaran')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="aktif" value="1" {{ old('aktif', $akun->aktif) == '1' ? 'checked' : '' }}
                                       class="mr-2 text-[#14AE5C] focus:ring-[#14AE5C]">
                                <span class="text-sm text-gray-700">Aktif</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="aktif" value="0" {{ old('aktif', $akun->aktif) == '0' ? 'checked' : '' }}
                                       class="mr-2 text-[#14AE5C] focus:ring-[#14AE5C]">
                                <span class="text-sm text-gray-700">Tidak Aktif</span>
                            </label>
                        </div>
                        @error('aktif')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Tombol Submit -->
                <div class="mt-8 flex justify-end space-x-3">
                    <a href="{{ route('master-data.jns_akun.index') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors">
                        <i class="fas fa-save mr-2"></i>Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
