@extends('layouts.app')

@section('title', 'Tambah Jenis Simpanan')
@section('sub-title', 'Master Data Jenis Simpanan')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Tambah Jenis Simpanan</h1>
        <a href="{{ route('master-data.jns_simpan') }}" 
           class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
            <i class="fas fa-arrow-left mr-2"></i>Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <form action="{{ route('master-data.jns_simpan.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Jenis Simpanan -->
                    <div>
                        <label for="jns_simpan" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-tag mr-2"></i>Jenis Simpanan <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="jns_simpan" name="jns_simpan" 
                               value="{{ old('jns_simpan') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('jns_simpan') border-red-500 @enderror"
                               placeholder="Masukkan jenis simpanan" required maxlength="30">
                        @error('jns_simpan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jumlah Minimum -->
                    <div>
                        <label for="jumlah" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-money-bill-wave mr-2"></i>Jumlah Minimum <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-500">Rp</span>
                            <input type="number" id="jumlah" name="jumlah" 
                                   value="{{ old('jumlah') }}"
                                   class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('jumlah') border-red-500 @enderror"
                                   placeholder="0" min="0" step="1000" required>
                        </div>
                        @error('jumlah')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Tampil -->
                    <div>
                        <label for="tampil" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-eye mr-2"></i>Status Tampil <span class="text-red-500">*</span>
                        </label>
                        <select id="tampil" name="tampil" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('tampil') border-red-500 @enderror" required>
                            <option value="">Pilih Status</option>
                            <option value="Y" {{ old('tampil') == 'Y' ? 'selected' : '' }}>Tampil</option>
                            <option value="T" {{ old('tampil') == 'T' ? 'selected' : '' }}>Tidak Tampil</option>
                        </select>
                        @error('tampil')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Urutan -->
                    <div>
                        <label for="urut" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-sort-numeric-up mr-2"></i>Urutan <span class="text-red-500">*</span>
                        </label>
                        <input type="number" id="urut" name="urut" 
                               value="{{ old('urut') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('urut') border-red-500 @enderror"
                               placeholder="1" min="1" max="99" required>
                        @error('urut')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t">
                    <a href="{{ route('master-data.jns_simpan') }}" 
                       class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors duration-200">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 transition-colors duration-200">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Format currency input
document.getElementById('jumlah').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    e.target.value = value;
});

// Auto-format display
document.getElementById('jumlah').addEventListener('blur', function(e) {
    if (e.target.value) {
        let formatted = new Intl.NumberFormat('id-ID').format(e.target.value);
        e.target.setAttribute('data-formatted', formatted);
    }
});
</script>
@endsection
