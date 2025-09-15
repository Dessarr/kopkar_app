@extends('layouts.app')

@section('title', 'Edit Data Anggota')
@section('sub-title', 'Master Data Anggota')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Edit Data Anggota</h1>
            <p class="text-sm text-gray-600 mt-1">Edit data anggota: {{ $anggota->nama }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('master-data.data_anggota.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Form Section -->
    <div class="bg-white rounded-lg shadow">
        <form action="{{ route('master-data.data_anggota.update', $anggota->id) }}" method="POST" class="p-6" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            
            <!-- Data Pribadi -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Pribadi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Nama Lengkap -->
                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nama" 
                               name="nama" 
                               value="{{ old('nama', $anggota->nama) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nama') border-red-500 @enderror"
                               placeholder="Masukkan nama lengkap"
                               required>
                        @error('nama')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Identitas -->
                    <div>
                        <label for="identitas" class="block text-sm font-medium text-gray-700 mb-2">
                            Identitas <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="identitas" 
                               name="identitas" 
                               value="{{ old('identitas', $anggota->identitas) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('identitas') border-red-500 @enderror"
                               placeholder="Masukkan identitas"
                               required>
                        @error('identitas')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ID Anggota (Read Only) -->
                    <div>
                        <label for="no_ktp" class="block text-sm font-medium text-gray-700 mb-2">
                            ID Anggota
                        </label>
                        <div class="w-full px-3 py-2 bg-gray-100 border border-gray-300 rounded-lg text-gray-700">
                            <span class="text-sm font-medium">{{ $anggota->no_ktp }}</span>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">ID Anggota tidak dapat diubah</p>
                    </div>

                    <!-- Jenis Kelamin -->
                    <div>
                        <label for="jk" class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Kelamin
                        </label>
                        <select id="jk" 
                                name="jk" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('jk') border-red-500 @enderror">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" {{ old('jk', $anggota->jk) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jk', $anggota->jk) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jk')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tempat Lahir -->
                    <div>
                        <label for="tmp_lahir" class="block text-sm font-medium text-gray-700 mb-2">
                            Tempat Lahir
                        </label>
                        <input type="text" 
                               id="tmp_lahir" 
                               name="tmp_lahir" 
                               value="{{ old('tmp_lahir', $anggota->tmp_lahir) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tmp_lahir') border-red-500 @enderror"
                               placeholder="Masukkan tempat lahir">
                        @error('tmp_lahir')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Tanggal Lahir -->
                    <div>
                        <label for="tgl_lahir" class="block text-sm font-medium text-gray-700 mb-2">
                            Tanggal Lahir
                        </label>
                        <input type="date" 
                               id="tgl_lahir" 
                               name="tgl_lahir" 
                               value="{{ old('tgl_lahir', $anggota->tgl_lahir) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('tgl_lahir') border-red-500 @enderror">
                        @error('tgl_lahir')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <select id="status" 
                                name="status" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status') border-red-500 @enderror">
                            <option value="">Pilih Status</option>
                            <option value="Lajang" {{ old('status', $anggota->status) == 'Lajang' ? 'selected' : '' }}>Lajang</option>
                            <option value="Menikah" {{ old('status', $anggota->status) == 'Menikah' ? 'selected' : '' }}>Menikah</option>
                            <option value="Cerai" {{ old('status', $anggota->status) == 'Cerai' ? 'selected' : '' }}>Cerai</option>
                        </select>
                        @error('status')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Agama -->
                    <div>
                        <label for="agama" class="block text-sm font-medium text-gray-700 mb-2">
                            Agama
                        </label>
                        <select id="agama" 
                                name="agama" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('agama') border-red-500 @enderror">
                            <option value="">Pilih Agama</option>
                            <option value="Islam" {{ old('agama', $anggota->agama) == 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Kristen" {{ old('agama', $anggota->agama) == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                            <option value="Katolik" {{ old('agama', $anggota->agama) == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                            <option value="Hindu" {{ old('agama', $anggota->agama) == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                            <option value="Buddha" {{ old('agama', $anggota->agama) == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                            <option value="Konghucu" {{ old('agama', $anggota->agama) == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                        </select>
                        @error('agama')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Alamat -->
                    <div class="md:col-span-2">
                        <label for="alamat" class="block text-sm font-medium text-gray-700 mb-2">
                            Alamat
                        </label>
                        <textarea id="alamat" 
                                  name="alamat" 
                                  rows="3"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('alamat') border-red-500 @enderror"
                                  placeholder="Masukkan alamat lengkap">{{ old('alamat', $anggota->alamat) }}</textarea>
                        @error('alamat')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Kota -->
                    <div>
                        <label for="kota" class="block text-sm font-medium text-gray-700 mb-2">
                            Kota
                        </label>
                        <input type="text" 
                               id="kota" 
                               name="kota" 
                               value="{{ old('kota', $anggota->kota) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('kota') border-red-500 @enderror"
                               placeholder="Masukkan kota">
                        @error('kota')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- No Telepon -->
                    <div>
                        <label for="notelp" class="block text-sm font-medium text-gray-700 mb-2">
                            No Telepon
                        </label>
                        <input type="text" 
                               id="notelp" 
                               name="notelp" 
                               value="{{ old('notelp', $anggota->notelp) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('notelp') border-red-500 @enderror"
                               placeholder="Masukkan nomor telepon">
                        @error('notelp')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Foto -->
                    <div class="md:col-span-2">
                        <label for="file_pic" class="block text-sm font-medium text-gray-700 mb-2">
                            Foto Profil
                        </label>
                        
                        <!-- Current Photo -->
                        @if($anggota->file_pic && Storage::disk('public')->exists('anggota/' . $anggota->file_pic))
                        <div class="mb-4">
                            <p class="text-sm text-gray-600 mb-2">Foto saat ini:</p>
                            <img src="{{ asset('storage/anggota/' . $anggota->file_pic) }}" 
                                 alt="Foto {{ $anggota->nama }}" 
                                 class="w-20 h-20 rounded-lg object-cover">
                        </div>
                        @endif
                        
                        <input type="file" 
                               id="file_pic" 
                               name="file_pic" 
                               accept="image/*"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('file_pic') border-red-500 @enderror">
                        @error('file_pic')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-sm text-gray-500">Format yang didukung: JPG, PNG, GIF. Maksimal 2MB.</p>
                    </div>
                </div>
            </div>

            <!-- Data Pekerjaan -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Pekerjaan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Departement -->
                    <div>
                        <label for="departement" class="block text-sm font-medium text-gray-700 mb-2">
                            Departement
                        </label>
                        <input type="text" 
                               id="departement" 
                               name="departement" 
                               value="{{ old('departement', $anggota->departement) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('departement') border-red-500 @enderror"
                               placeholder="Masukkan departement">
                        @error('departement')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Pekerjaan -->
                    <div>
                        <label for="pekerjaan" class="block text-sm font-medium text-gray-700 mb-2">
                            Pekerjaan
                        </label>
                        <input type="text" 
                               id="pekerjaan" 
                               name="pekerjaan" 
                               value="{{ old('pekerjaan', $anggota->pekerjaan) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('pekerjaan') border-red-500 @enderror"
                               placeholder="Masukkan pekerjaan">
                        @error('pekerjaan')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Data Bank -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Bank</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Bank -->
                    <div>
                        <label for="bank" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Bank
                        </label>
                        <input type="text" 
                               id="bank" 
                               name="bank" 
                               value="{{ old('bank', $anggota->bank) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('bank') border-red-500 @enderror"
                               placeholder="Masukkan nama bank">
                        @error('bank')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Nama Pemilik Rekening -->
                    <div>
                        <label for="nama_pemilik_rekening" class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Pemilik Rekening
                        </label>
                        <input type="text" 
                               id="nama_pemilik_rekening" 
                               name="nama_pemilik_rekening" 
                               value="{{ old('nama_pemilik_rekening', $anggota->nama_pemilik_rekening) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nama_pemilik_rekening') border-red-500 @enderror"
                               placeholder="Masukkan nama pemilik rekening">
                        @error('nama_pemilik_rekening')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- No Rekening -->
                    <div>
                        <label for="no_rekening" class="block text-sm font-medium text-gray-700 mb-2">
                            No Rekening
                        </label>
                        <input type="text" 
                               id="no_rekening" 
                               name="no_rekening" 
                               value="{{ old('no_rekening', $anggota->no_rekening) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('no_rekening') border-red-500 @enderror"
                               placeholder="Masukkan nomor rekening">
                        @error('no_rekening')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- ID Cabang -->
                    <div>
                        <label for="id_cabang" class="block text-sm font-medium text-gray-700 mb-2">
                            ID Cabang
                        </label>
                        <input type="text" 
                               id="id_cabang" 
                               name="id_cabang" 
                               value="{{ old('id_cabang', $anggota->id_cabang) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('id_cabang') border-red-500 @enderror"
                               placeholder="Masukkan ID cabang">
                        @error('id_cabang')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Data Simpanan -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Data Simpanan</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Simpanan Wajib -->
                    <div>
                        <label for="simpanan_wajib" class="block text-sm font-medium text-gray-700 mb-2">
                            Simpanan Wajib <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="text" 
                                   id="simpanan_wajib" 
                                   name="simpanan_wajib" 
                                   value="{{ number_format((float)old('simpanan_wajib', $anggota->simpanan_wajib), 0, ',', '.') }}"
                                   class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('simpanan_wajib') border-red-500 @enderror"
                                   placeholder="0"
                                   oninput="formatRupiah(this)"
                                   data-type="currency"
                                   required>
                        </div>
                        @error('simpanan_wajib')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Simpanan Sukarela -->
                    <div>
                        <label for="simpanan_sukarela" class="block text-sm font-medium text-gray-700 mb-2">
                            Simpanan Sukarela <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="text" 
                                   id="simpanan_sukarela" 
                                   name="simpanan_sukarela" 
                                   value="{{ number_format((float)old('simpanan_sukarela', $anggota->simpanan_sukarela), 0, ',', '.') }}"
                                   class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('simpanan_sukarela') border-red-500 @enderror"
                                   placeholder="0"
                                   oninput="formatRupiah(this)"
                                   data-type="currency"
                                   required>
                        </div>
                        @error('simpanan_sukarela')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Simpanan Khusus 2 -->
                    <div>
                        <label for="simpanan_khusus_2" class="block text-sm font-medium text-gray-700 mb-2">
                            Simpanan Khusus 2 <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="text" 
                                   id="simpanan_khusus_2" 
                                   name="simpanan_khusus_2" 
                                   value="{{ number_format((float)old('simpanan_khusus_2', $anggota->simpanan_khusus_2), 0, ',', '.') }}"
                                   class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('simpanan_khusus_2') border-red-500 @enderror"
                                   placeholder="0"
                                   oninput="formatRupiah(this)"
                                   data-type="currency"
                                   required>
                        </div>
                        @error('simpanan_khusus_2')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Status & Hidden Fields -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Status & Pengaturan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Status Aktif -->
                    <div>
                        <label for="aktif" class="block text-sm font-medium text-gray-700 mb-2">
                            Status Aktif
                        </label>
                        <select id="aktif" 
                                name="aktif" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('aktif') border-red-500 @enderror">
                            <option value="Y" {{ old('aktif', $anggota->aktif) == 'Y' ? 'selected' : '' }}>Aktif</option>
                            <option value="N" {{ old('aktif', $anggota->aktif) == 'N' ? 'selected' : '' }}>Tidak Aktif</option>
                        </select>
                        @error('aktif')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Bayar -->
                    <div>
                        <label for="status_bayar" class="block text-sm font-medium text-gray-700 mb-2">
                            Status Bayar
                        </label>
                        <select id="status_bayar" 
                                name="status_bayar" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('status_bayar') border-red-500 @enderror">
                            <option value="Belum Lunas" {{ old('status_bayar', $anggota->status_bayar) == 'Belum Lunas' ? 'selected' : '' }}>Belum Lunas</option>
                            <option value="Lunas" {{ old('status_bayar', $anggota->status_bayar) == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                        </select>
                        @error('status_bayar')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 pt-6 border-t">
                <a href="{{ route('master-data.data_anggota.index') }}" 
                   class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
                    Batal
                </a>
                <button type="submit" 
                        class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors flex items-center space-x-2">
                    <i class="fas fa-save"></i>
                    <span>Update Data</span>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
/* Format Rupiah */
function formatRupiah(input) {
    // Remove all non-digits and leading zeros
    let value = input.value.replace(/[^\d]/g, '').replace(/^0+/, '') || '0';

    // Store the raw value without formatting
    input.setAttribute('data-raw-value', value);

    // Format the number with thousand separators
    const formatted = new Intl.NumberFormat('id-ID').format(value);
    input.value = formatted;
}

// Handle form submission
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();

    // Get all currency inputs
    const currencyInputs = document.querySelectorAll('input[data-type="currency"]');
    currencyInputs.forEach(input => {
        // Use the stored raw value instead of parsing the formatted value
        const rawValue = input.getAttribute('data-raw-value') || input.value.replace(/[^\d]/g, '');
        input.value = rawValue;
    });

    this.submit();
});

// Format existing values on page load
document.addEventListener('DOMContentLoaded', function() {
    const currencyInputs = document.querySelectorAll('input[data-type="currency"]');
    currencyInputs.forEach(input => {
        if (input.value) {
            formatRupiah(input);
        }
    });
});
</script>
@endpush
