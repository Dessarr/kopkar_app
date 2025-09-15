@extends('layouts.app')

@section('title', 'Edit Jenis Angsuran')
@section('sub-title', 'Master Data Jenis Angsuran')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Jenis Angsuran</h1>
        <a href="{{ route('master-data.jenis_angsuran.index') }}" 
           class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('master-data.jenis_angsuran.update', $angsuran->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="max-w-md mx-auto">
                <!-- Jumlah Bulan -->
                <div class="mb-6">
                    <label for="ket" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-alt mr-2"></i>Jumlah Bulan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="number" id="ket" name="ket" value="{{ old('ket', $angsuran->ket) }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('ket') border-red-500 @enderror"
                               placeholder="Masukkan jumlah bulan" min="1" max="120" required>
                        <span class="absolute right-3 top-2 text-gray-500 text-sm">bulan</span>
                    </div>
                    @error('ket')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Masukkan jumlah bulan angsuran (1-120 bulan)</p>
                </div>

                <!-- Status Aktif -->
                <div class="mb-6">
                    <label for="aktif" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-toggle-on mr-2"></i>Status Aktif <span class="text-red-500">*</span>
                    </label>
                    <select id="aktif" name="aktif" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('aktif') border-red-500 @enderror"
                            required>
                        <option value="">Pilih Status</option>
                        <option value="Y" {{ old('aktif', $angsuran->aktif ? 'Y' : 'T') == 'Y' ? 'selected' : '' }}>Aktif</option>
                        <option value="T" {{ old('aktif', $angsuran->aktif ? 'Y' : 'T') == 'T' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('aktif')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Preview Kategori -->
                <div id="kategori-preview" class="mb-6 p-4 bg-gray-50 rounded-lg">
                    <h4 class="text-sm font-medium text-gray-700 mb-2">Kategori Angsuran:</h4>
                    <div id="kategori-badge" class="inline-block">
                        <span class="px-2 py-1 text-xs rounded-full bg-{{ $angsuran->kategori_angsuran_badge }}-100 text-{{ $angsuran->kategori_angsuran_badge }}-800">
                            {{ $angsuran->kategori_angsuran }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 mt-8 pt-6 border-t">
                <a href="{{ route('master-data.jenis_angsuran.index') }}" 
                   class="px-6 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 transition-colors duration-200">
                    <i class="fas fa-times mr-2"></i>Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#11994F] transition-colors duration-200">
                    <i class="fas fa-save mr-2"></i>Update
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.getElementById('ket').addEventListener('input', function(e) {
    const value = parseInt(e.target.value);
    const preview = document.getElementById('kategori-preview');
    const badge = document.getElementById('kategori-badge');
    
    if (value && value >= 1 && value <= 120) {
        let kategori, badgeClass;
        
        if (value <= 6) {
            kategori = 'Jangka Pendek';
            badgeClass = 'bg-green-100 text-green-800';
        } else if (value <= 24) {
            kategori = 'Jangka Menengah';
            badgeClass = 'bg-yellow-100 text-yellow-800';
        } else {
            kategori = 'Jangka Panjang';
            badgeClass = 'bg-blue-100 text-blue-800';
        }
        
        badge.innerHTML = `<span class="px-2 py-1 text-xs rounded-full ${badgeClass}">${kategori}</span>`;
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
});
</script>
@endsection
