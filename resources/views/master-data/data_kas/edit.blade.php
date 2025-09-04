@extends('layouts.app')

@section('title', 'Edit Data Kas')
@section('sub-title', 'Edit Data Kas')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Edit Data Kas</h1>
                <a href="{{ route('master-data.data_kas') }}" 
                   class="text-gray-600 hover:text-gray-900 flex items-center space-x-2">
                    <i class="fas fa-arrow-left"></i>
                    <span>Kembali</span>
                </a>
            </div>
        </div>

        <form action="{{ route('master-data.data_kas.update', $dataKas->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Kas -->
                <div class="md:col-span-2">
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Kas <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="nama" 
                           id="nama" 
                           value="{{ old('nama', $dataKas->nama) }}"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nama') border-red-500 @enderror"
                           placeholder="Masukkan nama kas">
                    @error('nama')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Aktif -->
                <div>
                    <label for="aktif" class="block text-sm font-medium text-gray-700 mb-2">
                        Status Aktif <span class="text-red-500">*</span>
                    </label>
                    <select name="aktif" 
                            id="aktif" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('aktif') border-red-500 @enderror">
                        <option value="">Pilih Status</option>
                        <option value="Y" {{ old('aktif', $dataKas->aktif) == 'Y' ? 'selected' : '' }}>Aktif</option>
                        <option value="T" {{ old('aktif', $dataKas->aktif) == 'T' ? 'selected' : '' }}>Tidak Aktif</option>
                    </select>
                    @error('aktif')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Divider -->
                <div class="md:col-span-2">
                    <hr class="my-4">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Konfigurasi Fitur</h3>
                </div>

                <!-- Tampil Simpanan -->
                <div>
                    <label for="tmpl_simpan" class="block text-sm font-medium text-gray-700 mb-2">
                        Tampil Simpanan <span class="text-red-500">*</span>
                    </label>
                    <select name="tmpl_simpan" 
                            id="tmpl_simpan" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tmpl_simpan') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option value="Y" {{ old('tmpl_simpan', $dataKas->tmpl_simpan) == 'Y' ? 'selected' : '' }}>Ya</option>
                        <option value="T" {{ old('tmpl_simpan', $dataKas->tmpl_simpan) == 'T' ? 'selected' : '' }}>Tidak</option>
                    </select>
                    @error('tmpl_simpan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tampil Penarikan -->
                <div>
                    <label for="tmpl_penarikan" class="block text-sm font-medium text-gray-700 mb-2">
                        Tampil Penarikan <span class="text-red-500">*</span>
                    </label>
                    <select name="tmpl_penarikan" 
                            id="tmpl_penarikan" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tmpl_penarikan') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option value="Y" {{ old('tmpl_penarikan', $dataKas->tmpl_penarikan) == 'Y' ? 'selected' : '' }}>Ya</option>
                        <option value="T" {{ old('tmpl_penarikan', $dataKas->tmpl_penarikan) == 'T' ? 'selected' : '' }}>Tidak</option>
                    </select>
                    @error('tmpl_penarikan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tampil Pinjaman -->
                <div>
                    <label for="tmpl_pinjaman" class="block text-sm font-medium text-gray-700 mb-2">
                        Tampil Pinjaman <span class="text-red-500">*</span>
                    </label>
                    <select name="tmpl_pinjaman" 
                            id="tmpl_pinjaman" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tmpl_pinjaman') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option value="Y" {{ old('tmpl_pinjaman', $dataKas->tmpl_pinjaman) == 'Y' ? 'selected' : '' }}>Ya</option>
                        <option value="T" {{ old('tmpl_pinjaman', $dataKas->tmpl_pinjaman) == 'T' ? 'selected' : '' }}>Tidak</option>
                    </select>
                    @error('tmpl_pinjaman')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tampil Bayar -->
                <div>
                    <label for="tmpl_bayar" class="block text-sm font-medium text-gray-700 mb-2">
                        Tampil Bayar <span class="text-red-500">*</span>
                    </label>
                    <select name="tmpl_bayar" 
                            id="tmpl_bayar" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tmpl_bayar') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option value="Y" {{ old('tmpl_bayar', $dataKas->tmpl_bayar) == 'Y' ? 'selected' : '' }}>Ya</option>
                        <option value="T" {{ old('tmpl_bayar', $dataKas->tmpl_bayar) == 'T' ? 'selected' : '' }}>Tidak</option>
                    </select>
                    @error('tmpl_bayar')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tampil Pemasukan -->
                <div>
                    <label for="tmpl_pemasukan" class="block text-sm font-medium text-gray-700 mb-2">
                        Tampil Pemasukan <span class="text-red-500">*</span>
                    </label>
                    <select name="tmpl_pemasukan" 
                            id="tmpl_pemasukan" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tmpl_pemasukan') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option value="Y" {{ old('tmpl_pemasukan', $dataKas->tmpl_pemasukan) == 'Y' ? 'selected' : '' }}>Ya</option>
                        <option value="T" {{ old('tmpl_pemasukan', $dataKas->tmpl_pemasukan) == 'T' ? 'selected' : '' }}>Tidak</option>
                    </select>
                    @error('tmpl_pemasukan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tampil Pengeluaran -->
                <div>
                    <label for="tmpl_pengeluaran" class="block text-sm font-medium text-gray-700 mb-2">
                        Tampil Pengeluaran <span class="text-red-500">*</span>
                    </label>
                    <select name="tmpl_pengeluaran" 
                            id="tmpl_pengeluaran" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tmpl_pengeluaran') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option value="Y" {{ old('tmpl_pengeluaran', $dataKas->tmpl_pengeluaran) == 'Y' ? 'selected' : '' }}>Ya</option>
                        <option value="T" {{ old('tmpl_pengeluaran', $dataKas->tmpl_pengeluaran) == 'T' ? 'selected' : '' }}>Tidak</option>
                    </select>
                    @error('tmpl_pengeluaran')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tampil Transfer -->
                <div>
                    <label for="tmpl_transfer" class="block text-sm font-medium text-gray-700 mb-2">
                        Tampil Transfer <span class="text-red-500">*</span>
                    </label>
                    <select name="tmpl_transfer" 
                            id="tmpl_transfer" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tmpl_transfer') border-red-500 @enderror">
                        <option value="">Pilih</option>
                        <option value="Y" {{ old('tmpl_transfer', $dataKas->tmpl_transfer) == 'Y' ? 'selected' : '' }}>Ya</option>
                        <option value="T" {{ old('tmpl_transfer', $dataKas->tmpl_transfer) == 'T' ? 'selected' : '' }}>Tidak</option>
                    </select>
                    @error('tmpl_transfer')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                <a href="{{ route('master-data.data_kas') }}" 
                   class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition-colors">
                    Update Data
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
