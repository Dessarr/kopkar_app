@extends('layouts.app')

@section('title', 'Edit Data Barang')
@section('sub-title', 'Master Data Barang')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Data Barang</h1>
        <a href="{{ route('master-data.data_barang') }}" 
           class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('master-data.data_barang.update', $barang->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Barang -->
                <div class="md:col-span-2">
                    <label for="nm_barang" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-box mr-2"></i>Nama Barang <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nm_barang" name="nm_barang" value="{{ old('nm_barang', $barang->nm_barang) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('nm_barang') border-red-500 @enderror"
                           placeholder="Masukkan nama barang">
                    @error('nm_barang')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Type -->
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-tag mr-2"></i>Type <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="type" name="type" value="{{ old('type', $barang->type) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('type') border-red-500 @enderror"
                           placeholder="Masukkan type barang">
                    @error('type')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Merk -->
                <div>
                    <label for="merk" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-industry mr-2"></i>Merk <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="merk" name="merk" value="{{ old('merk', $barang->merk) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('merk') border-red-500 @enderror"
                           placeholder="Masukkan merk barang">
                    @error('merk')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Harga -->
                <div>
                    <label for="harga" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-money-bill-wave mr-2"></i>Harga <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                        <input type="number" id="harga" name="harga" value="{{ old('harga', $barang->harga) }}" 
                               class="w-full pl-8 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('harga') border-red-500 @enderror"
                               placeholder="0" min="0" step="100">
                    </div>
                    @error('harga')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jumlah Barang -->
                <div>
                    <label for="jml_brg" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-boxes mr-2"></i>Jumlah Barang <span class="text-red-500">*</span>
                    </label>
                    <input type="number" id="jml_brg" name="jml_brg" value="{{ old('jml_brg', $barang->jml_brg) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('jml_brg') border-red-500 @enderror"
                           placeholder="0" min="0">
                    @error('jml_brg')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- ID Cabang -->
                <div>
                    <label for="id_cabang" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building mr-2"></i>ID Cabang
                    </label>
                    <input type="text" id="id_cabang" name="id_cabang" value="{{ old('id_cabang', $barang->id_cabang) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('id_cabang') border-red-500 @enderror"
                           placeholder="Masukkan ID cabang" maxlength="8">
                    @error('id_cabang')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keterangan -->
                <div class="md:col-span-2">
                    <label for="ket" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-comment mr-2"></i>Keterangan <span class="text-red-500">*</span>
                    </label>
                    <textarea id="ket" name="ket" rows="3" 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('ket') border-red-500 @enderror"
                              placeholder="Masukkan keterangan barang">{{ old('ket', $barang->ket) }}</textarea>
                    @error('ket')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 mt-8 pt-6 border-t">
                <a href="{{ route('master-data.data_barang') }}" 
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
// Format harga input
document.getElementById('harga').addEventListener('input', function(e) {
    let value = e.target.value.replace(/\D/g, '');
    if (value) {
        e.target.value = parseInt(value);
    }
});
</script>
@endsection
