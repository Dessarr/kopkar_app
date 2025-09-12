@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Jenis Akun</h1>
                <p class="text-gray-600">Edit data jenis akun</p>
            </div>
            <a href="{{ route('master-data.jns_akun.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali
            </a>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow-sm border">
            <form action="{{ route('master-data.jns_akun.update', $akun->id) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kode Aktiva -->
                    <div>
                        <label for="kd_aktiva" class="block text-sm font-medium text-gray-700 mb-2">
                            Kode Aktiva <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="kd_aktiva" 
                               name="kd_aktiva" 
                               value="{{ old('kd_aktiva', $akun->kd_aktiva) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('kd_aktiva') border-red-500 @enderror"
                               placeholder="Masukkan kode aktiva"
                               required>
                        @error('kd_aktiva')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jenis Transaksi -->
                    <div>
                        <label for="jns_trans" class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Transaksi <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="jns_trans" 
                               name="jns_trans" 
                               value="{{ old('jns_trans', $akun->jns_trans) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('jns_trans') border-red-500 @enderror"
                               placeholder="Masukkan jenis transaksi"
                               required>
                        @error('jns_trans')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Akun -->
                    <div>
                        <label for="akun" class="block text-sm font-medium text-gray-700 mb-2">
                            Akun <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="akun" 
                               name="akun" 
                               value="{{ old('akun', $akun->akun) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('akun') border-red-500 @enderror"
                               placeholder="Masukkan nama akun"
                               required>
                        @error('akun')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Laba Rugi -->
                    <div>
                        <label for="laba_rugi" class="block text-sm font-medium text-gray-700 mb-2">
                            Laba Rugi
                        </label>
                        <input type="text" 
                               id="laba_rugi" 
                               name="laba_rugi" 
                               value="{{ old('laba_rugi', $akun->laba_rugi) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('laba_rugi') border-red-500 @enderror"
                               placeholder="Masukkan laba rugi">
                        @error('laba_rugi')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pemasukan -->
                    <div>
                        <label for="pemasukan" class="block text-sm font-medium text-gray-700 mb-2">
                            Pemasukan <span class="text-red-500">*</span>
                        </label>
                        <select id="pemasukan" 
                                name="pemasukan" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('pemasukan') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Status</option>
                            <option value="1" {{ old('pemasukan', $akun->pemasukan) == '1' ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ old('pemasukan', $akun->pemasukan) == '0' ? 'selected' : '' }}>Tidak</option>
                        </select>
                        @error('pemasukan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pengeluaran -->
                    <div>
                        <label for="pengeluaran" class="block text-sm font-medium text-gray-700 mb-2">
                            Pengeluaran <span class="text-red-500">*</span>
                        </label>
                        <select id="pengeluaran" 
                                name="pengeluaran" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('pengeluaran') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Status</option>
                            <option value="1" {{ old('pengeluaran', $akun->pengeluaran) == '1' ? 'selected' : '' }}>Ya</option>
                            <option value="0" {{ old('pengeluaran', $akun->pengeluaran) == '0' ? 'selected' : '' }}>Tidak</option>
                        </select>
                        @error('pengeluaran')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div class="md:col-span-2">
                        <label for="aktif" class="block text-sm font-medium text-gray-700 mb-2">
                            Status <span class="text-red-500">*</span>
                        </label>
                        <select id="aktif" 
                                name="aktif" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('aktif') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Status</option>
                            <option value="1" {{ old('aktif', $akun->aktif) == '1' ? 'selected' : '' }}>Aktif</option>
                            <option value="0" {{ old('aktif', $akun->aktif) == '0' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                        @error('aktif')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                    <a href="{{ route('master-data.jns_akun.index') }}" 
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700">
                        <i class="fas fa-save mr-2"></i>
                        Update
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection