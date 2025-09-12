@extends('layouts.app')

@section('title', 'Tambah Data Anggota')
@section('sub-title', 'Tambah Data Anggota Baru')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tambah Data Anggota</h1>
            <p class="text-sm text-gray-600 mt-1">Formulir untuk menambahkan anggota koperasi baru</p>
        </div>
        <a href="{{ route('master-data.data_anggota') }}" 
           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
            <i class="fas fa-arrow-left"></i>
            <span>Kembali</span>
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('master-data.data_anggota.store') }}" method="POST" enctype="multipart/form-data" class="p-6">
            @csrf
            
            <!-- Personal Information -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Informasi Pribadi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="nama" value="{{ old('nama') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nama') border-red-500 @enderror">
                        @error('nama')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select name="jk" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('jk') border-red-500 @enderror">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" {{ old('jk') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jk') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jk')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Lahir <span class="text-red-500">*</span></label>
                        <input type="text" name="tmp_lahir" value="{{ old('tmp_lahir') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tmp_lahir') border-red-500 @enderror">
                        @error('tmp_lahir')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir <span class="text-red-500">*</span></label>
                        <input type="date" name="tgl_lahir" value="{{ old('tgl_lahir') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tgl_lahir') border-red-500 @enderror">
                        @error('tgl_lahir')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status <span class="text-red-500">*</span></label>
                        <select name="status" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                            <option value="">Pilih Status</option>
                            <option value="Belum Kawin" {{ old('status') == 'Belum Kawin' ? 'selected' : '' }}>Belum Kawin</option>
                            <option value="Kawin" {{ old('status') == 'Kawin' ? 'selected' : '' }}>Kawin</option>
                            <option value="Cerai Hidup" {{ old('status') == 'Cerai Hidup' ? 'selected' : '' }}>Cerai Hidup</option>
                            <option value="Cerai Mati" {{ old('status') == 'Cerai Mati' ? 'selected' : '' }}>Cerai Mati</option>
                        </select>
                        @error('status')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Agama <span class="text-red-500">*</span></label>
                        <select name="agama" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('agama') border-red-500 @enderror">
                            <option value="">Pilih Agama</option>
                            <option value="Islam" {{ old('agama') == 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Kristen" {{ old('agama') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                            <option value="Katolik" {{ old('agama') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                            <option value="Hindu" {{ old('agama') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                            <option value="Buddha" {{ old('agama') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                            <option value="Konghucu" {{ old('agama') == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                        </select>
                        @error('agama')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Work Information -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Informasi Pekerjaan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Departemen <span class="text-red-500">*</span></label>
                        <input type="text" name="departement" value="{{ old('departement') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('departement') border-red-500 @enderror">
                        @error('departement')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pekerjaan <span class="text-red-500">*</span></label>
                        <input type="text" name="pekerjaan" value="{{ old('pekerjaan') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('pekerjaan') border-red-500 @enderror">
                        @error('pekerjaan')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Informasi Kontak</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat <span class="text-red-500">*</span></label>
                        <textarea name="alamat" rows="3" required
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('alamat') border-red-500 @enderror">{{ old('alamat') }}</textarea>
                        @error('alamat')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kota <span class="text-red-500">*</span></label>
                        <input type="text" name="kota" value="{{ old('kota') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('kota') border-red-500 @enderror">
                        @error('kota')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon <span class="text-red-500">*</span></label>
                        <input type="text" name="notelp" value="{{ old('notelp') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('notelp') border-red-500 @enderror">
                        @error('notelp')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Bank Information -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Informasi Bank</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Bank <span class="text-red-500">*</span></label>
                        <input type="text" name="bank" value="{{ old('bank') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('bank') border-red-500 @enderror">
                        @error('bank')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pemilik Rekening <span class="text-red-500">*</span></label>
                        <input type="text" name="nama_pemilik_rekening" value="{{ old('nama_pemilik_rekening') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nama_pemilik_rekening') border-red-500 @enderror">
                        @error('nama_pemilik_rekening')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Rekening <span class="text-red-500">*</span></label>
                        <input type="text" name="no_rekening" value="{{ old('no_rekening') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('no_rekening') border-red-500 @enderror">
                        @error('no_rekening')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Savings Information -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Informasi Simpanan</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Wajib <span class="text-red-500">*</span></label>
                        <input type="text" name="simpanan_wajib" value="{{ old('simpanan_wajib', '0') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('simpanan_wajib') border-red-500 @enderror"
                               onkeyup="formatCurrency(this)">
                        @error('simpanan_wajib')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Sukarela <span class="text-red-500">*</span></label>
                        <input type="text" name="simpanan_sukarela" value="{{ old('simpanan_sukarela', '0') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('simpanan_sukarela') border-red-500 @enderror"
                               onkeyup="formatCurrency(this)">
                        @error('simpanan_sukarela')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Khusus 2 <span class="text-red-500">*</span></label>
                        <input type="text" name="simpanan_khusus_2" value="{{ old('simpanan_khusus_2', '0') }}" required
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('simpanan_khusus_2') border-red-500 @enderror"
                               onkeyup="formatCurrency(this)">
                        @error('simpanan_khusus_2')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Photo Upload -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Foto Profil</h3>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Upload Foto</label>
                    <input type="file" name="file_pic" accept="image/*"
                           class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('file_pic') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">Format: JPG, PNG, JPEG. Maksimal 2MB</p>
                    @error('file_pic')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Submit Buttons -->
            <div class="flex justify-end space-x-4">
                <a href="{{ route('master-data.data_anggota') }}" 
                   class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    Simpan Data
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function formatCurrency(input) {
    // Remove non-numeric characters
    let value = input.value.replace(/[^\d]/g, '');
    
    // Format with thousand separators
    if (value) {
        value = parseInt(value).toLocaleString('id-ID');
    }
    
    input.value = value;
}
</script>
@endsection
