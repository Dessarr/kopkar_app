@extends('layouts.app')

@section('title', 'Tambah Data Kas')
@section('sub-title', 'Master Data Kas')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Data Kas</h1>
            <p class="text-sm text-gray-600 mt-1">Tambahkan konfigurasi kas baru ke sistem</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('master-data.data_kas.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Form Section -->
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('master-data.data_kas.store') }}" method="POST" class="p-6">
            @csrf
            
            <!-- Basic Information -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Dasar</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Kas -->
                    <div class="md:col-span-2">
                        <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Kas <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nama" 
                               name="nama" 
                               value="{{ old('nama') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nama') border-red-500 @enderror"
                               placeholder="Masukkan nama kas"
                               required>
                        @error('nama')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Aktif -->
                    <div>
                        <label for="aktif" class="block text-sm font-medium text-gray-700 mb-2">
                            Status Aktif <span class="text-red-500">*</span>
                        </label>
                        <select id="aktif" 
                                name="aktif" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('aktif') border-red-500 @enderror"
                                required>
                            <option value="">Pilih Status</option>
                            <option value="Y" {{ old('aktif') == 'Y' ? 'selected' : '' }}>Aktif</option>
                            <option value="T" {{ old('aktif') == 'T' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                        @error('aktif')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Fitur Configuration -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Konfigurasi Fitur</h3>
                <p class="text-sm text-gray-600 mb-6">Pilih fitur-fitur yang akan tersedia untuk kas ini</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <!-- Simpanan -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <label for="tmpl_simpan" class="text-sm font-medium text-gray-700">
                                Template Simpanan
                            </label>
                            <input type="checkbox" 
                                   id="tmpl_simpan" 
                                   name="tmpl_simpan" 
                                   value="Y"
                                   {{ old('tmpl_simpan', 'Y') == 'Y' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        </div>
                        <p class="text-xs text-gray-500">Mengaktifkan fitur simpanan untuk kas ini</p>
                    </div>

                    <!-- Penarikan -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <label for="tmpl_penarikan" class="text-sm font-medium text-gray-700">
                                Template Penarikan
                            </label>
                            <input type="checkbox" 
                                   id="tmpl_penarikan" 
                                   name="tmpl_penarikan" 
                                   value="Y"
                                   {{ old('tmpl_penarikan', 'Y') == 'Y' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        </div>
                        <p class="text-xs text-gray-500">Mengaktifkan fitur penarikan untuk kas ini</p>
                    </div>

                    <!-- Pinjaman -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <label for="tmpl_pinjaman" class="text-sm font-medium text-gray-700">
                                Template Pinjaman
                            </label>
                            <input type="checkbox" 
                                   id="tmpl_pinjaman" 
                                   name="tmpl_pinjaman" 
                                   value="Y"
                                   {{ old('tmpl_pinjaman', 'Y') == 'Y' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        </div>
                        <p class="text-xs text-gray-500">Mengaktifkan fitur pinjaman untuk kas ini</p>
                    </div>

                    <!-- Bayar -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <label for="tmpl_bayar" class="text-sm font-medium text-gray-700">
                                Template Bayar
                            </label>
                            <input type="checkbox" 
                                   id="tmpl_bayar" 
                                   name="tmpl_bayar" 
                                   value="Y"
                                   {{ old('tmpl_bayar', 'Y') == 'Y' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        </div>
                        <p class="text-xs text-gray-500">Mengaktifkan fitur bayar untuk kas ini</p>
                    </div>

                    <!-- Pemasukan -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <label for="tmpl_pemasukan" class="text-sm font-medium text-gray-700">
                                Template Pemasukan
                            </label>
                            <input type="checkbox" 
                                   id="tmpl_pemasukan" 
                                   name="tmpl_pemasukan" 
                                   value="Y"
                                   {{ old('tmpl_pemasukan', 'Y') == 'Y' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        </div>
                        <p class="text-xs text-gray-500">Mengaktifkan fitur pemasukan untuk kas ini</p>
                    </div>

                    <!-- Pengeluaran -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <label for="tmpl_pengeluaran" class="text-sm font-medium text-gray-700">
                                Template Pengeluaran
                            </label>
                            <input type="checkbox" 
                                   id="tmpl_pengeluaran" 
                                   name="tmpl_pengeluaran" 
                                   value="Y"
                                   {{ old('tmpl_pengeluaran', 'Y') == 'Y' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        </div>
                        <p class="text-xs text-gray-500">Mengaktifkan fitur pengeluaran untuk kas ini</p>
                    </div>

                    <!-- Transfer -->
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center justify-between mb-2">
                            <label for="tmpl_transfer" class="text-sm font-medium text-gray-700">
                                Template Transfer
                            </label>
                            <input type="checkbox" 
                                   id="tmpl_transfer" 
                                   name="tmpl_transfer" 
                                   value="Y"
                                   {{ old('tmpl_transfer', 'Y') == 'Y' ? 'checked' : '' }}
                                   class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                        </div>
                        <p class="text-xs text-gray-500">Mengaktifkan fitur transfer untuk kas ini</p>
                    </div>
                </div>
            </div>

            <!-- Preview Section -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Preview Konfigurasi</h3>
                <div class="bg-blue-50 p-4 rounded-lg">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600" id="totalFitur">0</div>
                            <div class="text-sm text-gray-600">Total Fitur Aktif</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-blue-600" id="kategoriKas">-</div>
                            <div class="text-sm text-gray-600">Kategori Kas</div>
                        </div>
                        <div class="text-center">
                            <div class="text-lg font-semibold text-blue-600" id="statusKas">-</div>
                            <div class="text-sm text-gray-600">Status</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-6 border-t">
                <a href="{{ route('master-data.data_kas.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
                    <i class="fas fa-save"></i>
                    <span>Simpan Data</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const checkboxes = document.querySelectorAll('input[type="checkbox"]');
    const totalFiturEl = document.getElementById('totalFitur');
    const kategoriKasEl = document.getElementById('kategoriKas');
    const statusKasEl = document.getElementById('statusKas');
    const statusSelect = document.getElementById('aktif');

    function updatePreview() {
        let totalAktif = 0;
        checkboxes.forEach(checkbox => {
            if (checkbox.checked) totalAktif++;
        });

        totalFiturEl.textContent = totalAktif;

        // Update kategori
        let kategori = 'Minimal';
        if (totalAktif >= 6) kategori = 'Komprehensif';
        else if (totalAktif >= 4) kategori = 'Menengah';
        else if (totalAktif >= 2) kategori = 'Dasar';
        
        kategoriKasEl.textContent = kategori;

        // Update status
        const status = statusSelect.value;
        statusKasEl.textContent = status === 'Y' ? 'Aktif' : status === 'T' ? 'Tidak Aktif' : '-';
    }

    checkboxes.forEach(checkbox => {
        checkbox.addEventListener('change', updatePreview);
    });

    statusSelect.addEventListener('change', updatePreview);

    // Initial update
    updatePreview();
});
</script>
@endsection