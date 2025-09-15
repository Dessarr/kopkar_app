@extends('layouts.app')

@section('title', 'Edit Data Mobil')
@section('sub-title', 'Master Data Mobil')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Data Mobil</h1>
        <a href="{{ route('master-data.data_mobil.index') }}" 
           class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition">
            <i class="fas fa-arrow-left"></i>
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('master-data.data_mobil.update', $mobil->id) }}" method="POST" class="p-6">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Nama Mobil -->
                <div class="md:col-span-2">
                    <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-car mr-2"></i>Nama Mobil <span class="text-red-500">*</span>
                    </label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $mobil->nama) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('nama') border-red-500 @enderror"
                           placeholder="Masukkan nama mobil">
                    @error('nama')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Jenis -->
                <div>
                    <label for="jenis" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-car-side mr-2"></i>Jenis
                    </label>
                    <input type="text" id="jenis" name="jenis" value="{{ old('jenis', $mobil->jenis) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('jenis') border-red-500 @enderror"
                           placeholder="Masukkan jenis mobil">
                    @error('jenis')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Merek -->
                <div>
                    <label for="merek" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-industry mr-2"></i>Merek
                    </label>
                    <input type="text" id="merek" name="merek" value="{{ old('merek', $mobil->merek) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('merek') border-red-500 @enderror"
                           placeholder="Masukkan merek mobil">
                    @error('merek')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Pabrikan -->
                <div>
                    <label for="pabrikan" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-building mr-2"></i>Pabrikan
                    </label>
                    <input type="text" id="pabrikan" name="pabrikan" value="{{ old('pabrikan', $mobil->pabrikan) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('pabrikan') border-red-500 @enderror"
                           placeholder="Masukkan pabrikan">
                    @error('pabrikan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Warna -->
                <div>
                    <label for="warna" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-palette mr-2"></i>Warna
                    </label>
                    <input type="text" id="warna" name="warna" value="{{ old('warna', $mobil->warna) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('warna') border-red-500 @enderror"
                           placeholder="Masukkan warna mobil">
                    @error('warna')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tahun -->
                <div>
                    <label for="tahun" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar mr-2"></i>Tahun
                    </label>
                    <input type="number" id="tahun" name="tahun" value="{{ old('tahun', $mobil->tahun) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('tahun') border-red-500 @enderror"
                           placeholder="Tahun pembuatan" min="1900" max="{{ date('Y') }}">
                    @error('tahun')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No Polisi -->
                <div>
                    <label for="no_polisi" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-id-card mr-2"></i>No Polisi
                    </label>
                    <input type="text" id="no_polisi" name="no_polisi" value="{{ old('no_polisi', $mobil->no_polisi) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('no_polisi') border-red-500 @enderror"
                           placeholder="Masukkan nomor polisi" maxlength="15">
                    @error('no_polisi')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No Rangka -->
                <div>
                    <label for="no_rangka" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-barcode mr-2"></i>No Rangka
                    </label>
                    <input type="text" id="no_rangka" name="no_rangka" value="{{ old('no_rangka', $mobil->no_rangka) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('no_rangka') border-red-500 @enderror"
                           placeholder="Masukkan nomor rangka" maxlength="50">
                    @error('no_rangka')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No Mesin -->
                <div>
                    <label for="no_mesin" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-cog mr-2"></i>No Mesin
                    </label>
                    <input type="text" id="no_mesin" name="no_mesin" value="{{ old('no_mesin', $mobil->no_mesin) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('no_mesin') border-red-500 @enderror"
                           placeholder="Masukkan nomor mesin" maxlength="50">
                    @error('no_mesin')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- No BPKB -->
                <div>
                    <label for="no_bpkb" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-file-alt mr-2"></i>No BPKB
                    </label>
                    <input type="text" id="no_bpkb" name="no_bpkb" value="{{ old('no_bpkb', $mobil->no_bpkb) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('no_bpkb') border-red-500 @enderror"
                           placeholder="Masukkan nomor BPKB" maxlength="50">
                    @error('no_bpkb')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tanggal Berlaku STNK -->
                <div>
                    <label for="tgl_berlaku_stnk" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-calendar-check mr-2"></i>Tanggal Berlaku STNK
                    </label>
                    <input type="date" id="tgl_berlaku_stnk" name="tgl_berlaku_stnk" value="{{ old('tgl_berlaku_stnk', $mobil->tgl_berlaku_stnk ? $mobil->tgl_berlaku_stnk->format('Y-m-d') : '') }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('tgl_berlaku_stnk') border-red-500 @enderror">
                    @error('tgl_berlaku_stnk')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- File PIC -->
                <div>
                    <label for="file_pic" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-image mr-2"></i>File PIC
                    </label>
                    <input type="text" id="file_pic" name="file_pic" value="{{ old('file_pic', $mobil->file_pic) }}" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('file_pic') border-red-500 @enderror"
                           placeholder="Nama file gambar" maxlength="100">
                    @error('file_pic')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Status Aktif -->
                <div>
                    <label for="aktif" class="block text-sm font-medium text-gray-700 mb-2">
                        <i class="fas fa-toggle-on mr-2"></i>Status Aktif <span class="text-red-500">*</span>
                    </label>
                    <select id="aktif" name="aktif" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent @error('aktif') border-red-500 @enderror">
                        <option value="">Pilih Status</option>
                        <option value="Y" {{ old('aktif', $mobil->aktif ? 'Y' : 'N') == 'Y' ? 'selected' : '' }}>Aktif</option>
                        <option value="N" {{ old('aktif', $mobil->aktif ? 'Y' : 'N') == 'N' ? 'selected' : '' }}>Nonaktif</option>
                    </select>
                    @error('aktif')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end gap-3 mt-8 pt-6 border-t">
                <a href="{{ route('master-data.data_mobil.index') }}" 
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
@endsection
