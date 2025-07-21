@extends('layouts.app')

@section('title', 'Edit Data Anggota')

@section('content')
<div class="p-6">
    <div class="flex justify-between align-center mb-6">
        <h1 class="text-2xl font-bold">Edit Data Anggota</h1>
        <a href="{{ route('master-data.data_anggota') }}"
            class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('master-data.data_anggota.update', $anggota->id) }}" method="POST"
            enctype="multipart/form-data" class="p-6">
            @csrf
            @method('PUT')

            <!-- Data Pribadi -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Data Pribadi</h2>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Lengkap</label>
                        <input type="text" name="nama" value="{{ old('nama', $anggota->nama) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('nama') border-red-500 @enderror"
                            placeholder="Masukkan nama lengkap">
                        @error('nama')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. KTP</label>
                        <input type="text" name="no_ktp" value="{{ old('no_ktp', $anggota->no_ktp) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('no_ktp') border-red-500 @enderror"
                            placeholder="Masukkan nomor KTP">
                        @error('no_ktp')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Kelamin</label>
                        <select name="jk" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('jk') border-red-500 @enderror">
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="L" {{ old('jk', $anggota->jk) == 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('jk', $anggota->jk) == 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                        @error('jk')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tempat Lahir</label>
                        <input type="text" name="tmp_lahir" value="{{ old('tmp_lahir', $anggota->tmp_lahir) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('tmp_lahir') border-red-500 @enderror"
                            placeholder="Masukkan tempat lahir">
                        @error('tmp_lahir')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Lahir</label>
                        <input type="date" name="tgl_lahir" value="{{ old('tgl_lahir', $anggota->tgl_lahir) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('tgl_lahir') border-red-500 @enderror">
                        @error('tgl_lahir')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Status</label>
                        <select name="status" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('status') border-red-500 @enderror">
                            <option value="">Pilih Status</option>
                            <option value="Lajang" {{ old('status', $anggota->status) == 'Lajang' ? 'selected' : '' }}>
                                Lajang</option>
                            <option value="Menikah"
                                {{ old('status', $anggota->status) == 'Menikah' ? 'selected' : '' }}>Menikah</option>
                            <option value="Cerai" {{ old('status', $anggota->status) == 'Cerai' ? 'selected' : '' }}>
                                Cerai</option>
                        </select>
                        @error('status')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Agama</label>
                        <select name="agama" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('agama') border-red-500 @enderror">
                            <option value="">Pilih Agama</option>
                            <option value="Islam" {{ old('agama', $anggota->agama) == 'Islam' ? 'selected' : '' }}>Islam
                            </option>
                            <option value="Kristen" {{ old('agama', $anggota->agama) == 'Kristen' ? 'selected' : '' }}>
                                Kristen</option>
                            <option value="Katolik" {{ old('agama', $anggota->agama) == 'Katolik' ? 'selected' : '' }}>
                                Katolik</option>
                            <option value="Hindu" {{ old('agama', $anggota->agama) == 'Hindu' ? 'selected' : '' }}>Hindu
                            </option>
                            <option value="Buddha" {{ old('agama', $anggota->agama) == 'Buddha' ? 'selected' : '' }}>
                                Buddha</option>
                            <option value="Konghucu"
                                {{ old('agama', $anggota->agama) == 'Konghucu' ? 'selected' : '' }}>Konghucu</option>
                        </select>
                        @error('agama')
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
                        <input type="text" name="departement" value="{{ old('departement', $anggota->departement) }}"
                            required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('departement') border-red-500 @enderror"
                            placeholder="Masukkan departemen">
                        @error('departement')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Pekerjaan</label>
                        <input type="text" name="pekerjaan" value="{{ old('pekerjaan', $anggota->pekerjaan) }}" required
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
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                        <textarea name="alamat" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('alamat') border-red-500 @enderror"
                            rows="3"
                            placeholder="Masukkan alamat lengkap">{{ old('alamat', $anggota->alamat) }}</textarea>
                        @error('alamat')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kota</label>
                        <input type="text" name="kota" value="{{ old('kota', $anggota->kota) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('kota') border-red-500 @enderror"
                            placeholder="Masukkan kota">
                        @error('kota')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                        <input type="text" name="notelp" value="{{ old('notelp', $anggota->notelp) }}" required
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
                        <input type="text" name="bank" value="{{ old('bank', $anggota->bank) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('bank') border-red-500 @enderror"
                            placeholder="Masukkan nama bank">
                        @error('bank')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Pemilik Rekening</label>
                        <input type="text" name="nama_pemilik_rekening"
                            value="{{ old('nama_pemilik_rekening', $anggota->nama_pemilik_rekening) }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('nama_pemilik_rekening') border-red-500 @enderror"
                            placeholder="Masukkan nama pemilik rekening">
                        @error('nama_pemilik_rekening')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No. Rekening</label>
                        <input type="text" name="no_rekening" value="{{ old('no_rekening', $anggota->no_rekening) }}"
                            required
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
                                placeholder="0"
                                value="{{ number_format((float)old('simpanan_wajib', $anggota->simpanan_wajib), 0, ',', '.') }}"
                                oninput="formatRupiah(this)" data-type="currency">
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
                                placeholder="0"
                                value="{{ number_format((float)old('simpanan_sukarela', $anggota->simpanan_sukarela), 0, ',', '.') }}"
                                oninput="formatRupiah(this)" data-type="currency">
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
                                placeholder="0"
                                value="{{ number_format((float)old('simpanan_khusus_2', $anggota->simpanan_khusus_2), 0, ',', '.') }}"
                                oninput="formatRupiah(this)" data-type="currency">
                        </div>
                        @error('simpanan_khusus_2')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Foto -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Foto</h2>
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Upload Foto</label>
                        <input type="file" name="file_pic" accept="image/*"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 @error('file_pic') border-red-500 @enderror">
                        <p class="text-sm text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengubah foto</p>
                        @error('file_pic')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        @if($anggota->file_pic)
                        <div class="mt-2">
                            <img src="{{ asset('storage/anggota/' . $anggota->file_pic) }}" alt="Foto Anggota"
                                class="w-32 h-32 object-cover rounded">
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                    Simpan Perubahan
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
    // Remove all non-digits
    let value = input.value.replace(/[^\d]/g, '');
    if (value === '') {
        input.value = '';
        return;
    }

    // Format with thousand separators using Indonesian locale
    const formatted = new Intl.NumberFormat('id-ID').format(value);
    input.value = formatted;

    // Store the raw value as a data attribute
    input.setAttribute('data-raw-value', value);
}

// Handle form submission
document.querySelector('form').addEventListener('submit', function(e) {
    e.preventDefault();

    // Get all currency inputs
    const currencyInputs = document.querySelectorAll('input[data-type="currency"]');
    currencyInputs.forEach(input => {
        // Use the stored raw value
        const rawValue = input.getAttribute('data-raw-value') || input.value.replace(/[^\d]/g, '');
        input.value = rawValue;
    });

    this.submit();
});

// No need to format on page load since we're using PHP number_format
</script>
@endpush