@extends('layouts.app')

@section('title', 'Tambah Data Anggota')

@section('content')
<div class="p-6">
    <div class="flex justify-between align-center mb-6">
        <h1 class="text-2xl font-bold">Tambah Data Anggota</h1>
        <a href="{{ route('master-data.data_anggota.index') }}"
            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('master-data.data_anggota.store') }}" method="POST" enctype="multipart/form-data"
            class="p-6">
            @csrf

            <!-- Data Pribadi -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Data Pribadi</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ old('nama') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('nama') border-red-500 @enderror"
                            placeholder="Masukkan nama lengkap">
                        @error('nama')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Identitas</label>
                        <input type="text" name="identitas" value="{{ old('identitas') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('identitas') border-red-500 @enderror"
                            placeholder="Masukkan identitas (opsional)">
                        @error('identitas')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Anggota</label>
                        <input type="text" name="no_ktp" id="no_ktp" value="{{ old('no_ktp') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('no_ktp') border-red-500 @enderror"
                               placeholder="Kosongkan untuk auto-generate">
                        <p class="text-xs text-gray-500 mt-1">Kosongkan untuk auto-generate ID, atau isi manual</p>
                        @error('no_ktp')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin</label>
                        <select name="jk" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('jk') border-red-500 @enderror">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" {{ old('jk') == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jk') == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jk')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Lahir</label>
                        <input type="text" name="tmp_lahir" value="{{ old('tmp_lahir') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('tmp_lahir') border-red-500 @enderror"
                            placeholder="Masukkan tempat lahir (opsional)">
                        @error('tmp_lahir')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir</label>
                        <input type="date" name="tgl_lahir" value="{{ old('tgl_lahir') }}"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('tgl_lahir') border-red-500 @enderror">
                        @error('tgl_lahir')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('status') border-red-500 @enderror">
                            <option value="">Pilih Status (opsional)</option>
                            <option value="Lajang" {{ old('status') == 'Lajang' ? 'selected' : '' }}>Lajang</option>
                            <option value="Menikah" {{ old('status') == 'Menikah' ? 'selected' : '' }}>Menikah</option>
                            <option value="Cerai" {{ old('status') == 'Cerai' ? 'selected' : '' }}>Cerai</option>
                        </select>
                        @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Agama</label>
                        <select name="agama"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('agama') border-red-500 @enderror">
                            <option value="">Pilih Agama (opsional)</option>
                            <option value="Islam" {{ old('agama') == 'Islam' ? 'selected' : '' }}>Islam</option>
                            <option value="Kristen" {{ old('agama') == 'Kristen' ? 'selected' : '' }}>Kristen</option>
                            <option value="Katolik" {{ old('agama') == 'Katolik' ? 'selected' : '' }}>Katolik</option>
                            <option value="Hindu" {{ old('agama') == 'Hindu' ? 'selected' : '' }}>Hindu</option>
                            <option value="Buddha" {{ old('agama') == 'Buddha' ? 'selected' : '' }}>Buddha</option>
                            <option value="Konghucu" {{ old('agama') == 'Konghucu' ? 'selected' : '' }}>Konghucu
                            </option>
                        </select>
                        @error('agama')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Foto</label>
                        <input type="file" name="file_pic" accept="image/*"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('file_pic') border-red-500 @enderror">
                        @error('file_pic')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Data Pekerjaan -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Data Pekerjaan</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Departemen</label>
                        <input type="text" name="departement" value="{{ old('departement') }}" nullable | string | max:255  
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('departement') border-red-500 @enderror"
                            placeholder="Masukkan departemen">
                        @error('departement')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pekerjaan</label>
                        <input type="text" name="pekerjaan" value="{{ old('pekerjaan') }}" nullable | string | max:255      
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('pekerjaan') border-red-500 @enderror"
                            placeholder="Masukkan pekerjaan">
                        @error('pekerjaan')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Data Kontak -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Data Kontak</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                        <textarea name="alamat" nullable | string
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('alamat') border-red-500 @enderror"
                            rows="3" placeholder="Masukkan alamat lengkap">{{ old('alamat') }}</textarea>
                        @error('alamat')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kota</label>
                        <input type="text" name="kota" value="{{ old('kota') }}" nullable | string | max:255
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('kota') border-red-500 @enderror"
                            placeholder="Masukkan kota">
                        @error('kota')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                        <input type="text" name="notelp" value="{{ old('notelp') }}" nullable | string | max:20
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('notelp') border-red-500 @enderror"
                            placeholder="Masukkan nomor telepon">
                        @error('notelp')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Data Bank -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Data Bank</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Bank</label>
                        <input type="text" name="bank" value="{{ old('bank') }}" nullable | string | max:255
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('bank') border-red-500 @enderror"
                            placeholder="Masukkan nama bank">
                        @error('bank')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pemilik Rekening</label>
                        <input type="text" name="nama_pemilik_rekening" value="{{ old('nama_pemilik_rekening') }}"
                            nullable | string | max:255
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('nama_pemilik_rekening') border-red-500 @enderror"
                            placeholder="Masukkan nama pemilik rekening">
                        @error('nama_pemilik_rekening')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Rekening</label>
                        <input type="text" name="no_rekening" value="{{ old('no_rekening') }}" nullable | string | max:255
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('no_rekening') border-red-500 @enderror"
                            placeholder="Masukkan nomor rekening">
                        @error('no_rekening')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>


            <!-- Data Simpanan -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Data Simpanan</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Wajib</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="text" name="simpanan_wajib" id="simpanan_wajib" required
                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('simpanan_wajib') border-red-500 @enderror"
                                placeholder="0" value="{{ old('simpanan_wajib') }}" oninput="formatRupiah(this)"
                                data-type="currency">
                        </div>
                        @error('simpanan_wajib')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Sukarela</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="text" name="simpanan_sukarela" id="simpanan_sukarela" required
                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('simpanan_sukarela') border-red-500 @enderror"
                                placeholder="0" value="{{ old('simpanan_sukarela') }}" oninput="formatRupiah(this)"
                                data-type="currency">
                        </div>
                        @error('simpanan_sukarela')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Simpanan Khusus 2</label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-gray-500">Rp</span>
                            <input type="text" name="simpanan_khusus_2" id="simpanan_khusus_2" required
                                class="w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('simpanan_khusus_2') border-red-500 @enderror"
                                placeholder="0" value="{{ old('simpanan_khusus_2') }}" oninput="formatRupiah(this)"
                                data-type="currency">
                        </div>
                        @error('simpanan_khusus_2')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Hidden Fields -->
            <input type="hidden" name="tgl_daftar" value="{{ date('Y-m-d') }}">
            <input type="hidden" name="jabatan_id" value="1">
            <input type="hidden" name="aktif" value="Y">
            <input type="hidden" name="id_tagihan" value="">
            <input type="hidden" name="jns_trans" value="">
            <input type="hidden" name="status_bayar" value="Belum Lunas">
            <input type="hidden" name="id_cabang" value="">
            <input type="hidden" name="username" value="">
            <input type="hidden" name="pass_word" value="">

            <div class="flex justify-end gap-4">
                <button type="reset" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600">
                    Reset
                </button>
                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600">
                    Simpan Data
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
