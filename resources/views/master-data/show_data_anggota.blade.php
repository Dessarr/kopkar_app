@extends('layouts.app')

@section('title', 'Detail Data Anggota')

@section('content')
<div class="p-6">
    <div class="flex justify-between align-center mb-6">
        <h1 class="text-2xl font-bold">Detail Data Anggota</h1>
        <a href="{{ route('master-data.data_anggota') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
            Kembali
        </a>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Foto Profil -->
                <div class="col-span-2 flex justify-center">
                    @if($anggota->file_pic)
                        <img src="{{ Storage::url('anggota/'.$anggota->file_pic) }}" 
                             alt="Foto {{ $anggota->nama }}" 
                             class="w-48 h-48 object-cover rounded-full border-4 border-green-500">
                    @else
                        <div class="w-48 h-48 rounded-full border-4 border-green-500 flex items-center justify-center bg-gray-100">
                            <span class="text-gray-400 text-xl">No Image</span>
                        </div>
                    @endif
                </div>

                <!-- Informasi Pribadi -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h2 class="text-lg font-semibold mb-4 text-green-600">Informasi Pribadi</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">ID Koperasi</p>
                            <p class="font-medium">
                                @if($anggota->no_ktp)
                                    {{ $anggota->no_ktp }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Nama Lengkap</p>
                            <p class="font-medium">
                                @if($anggota->nama)
                                    {{ $anggota->nama }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                            <p class="text-sm text-gray-500">
                                @if($anggota->username)
                                    {{ $anggota->username }}
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Jenis Kelamin</p>
                            <p class="font-medium">
                                @if($anggota->jk)
                                    {{ $anggota->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status</p>
                            <p class="font-medium">
                                @if($anggota->status)
                                    {{ $anggota->status }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Agama</p>
                            <p class="font-medium">
                                @if($anggota->agama)
                                    {{ $anggota->agama }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Tanggal Lahir</p>
                            <p class="font-medium">
                                @if($anggota->tgl_lahir && $anggota->tgl_lahir != '0000-00-00')
                                    {{ date('d/m/Y', strtotime($anggota->tgl_lahir)) }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Status Aktif</p>
                            <span class="px-2 py-1 rounded-full text-xs {{ $anggota->aktif ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $anggota->aktif ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Informasi Pekerjaan -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h2 class="text-lg font-semibold mb-4 text-green-600">Informasi Pekerjaan</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Jabatan</p>
                            <p class="font-medium">
                                @if($anggota->jabatan_id)
                                    {{ $anggota->jabatan_id }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Department</p>
                            <p class="font-medium">
                                @if($anggota->departement)
                                    {{ $anggota->departement }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Pekerjaan</p>
                            <p class="font-medium">
                                @if($anggota->pekerjaan)
                                    {{ $anggota->pekerjaan }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Tanggal Daftar</p>
                            <p class="font-medium">
                                @if($anggota->tgl_daftar && $anggota->tgl_daftar != '0000-00-00')
                                    {{ date('d/m/Y', strtotime($anggota->tgl_daftar)) }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Informasi Kontak -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h2 class="text-lg font-semibold mb-4 text-green-600">Informasi Kontak</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Alamat</p>
                            <p class="font-medium">
                                @if($anggota->alamat)
                                    {{ $anggota->alamat }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Kota</p>
                            <p class="font-medium">
                                @if($anggota->kota)
                                    {{ $anggota->kota }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">No. Telepon</p>
                            <p class="font-medium">
                                @if($anggota->notelp)
                                    {{ $anggota->notelp }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Informasi Bank -->
                <div class="bg-gray-50 p-6 rounded-lg">
                    <h2 class="text-lg font-semibold mb-4 text-green-600">Informasi Bank</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Nama Bank</p>
                            <p class="font-medium">
                                @if($anggota->bank)
                                    {{ $anggota->bank }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">No. Rekening</p>
                            <p class="font-medium">
                                @if($anggota->no_rekening)
                                    {{ $anggota->no_rekening }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-span-2">
                            <p class="text-sm text-gray-600">Nama Pemilik Rekening</p>
                            <p class="font-medium">
                                @if($anggota->nama_pemilik_rekening)
                                    {{ $anggota->nama_pemilik_rekening }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Informasi Simpanan -->
                <div class="col-span-2 bg-gray-50 p-6 rounded-lg">
                    <h2 class="text-lg font-semibold mb-4 text-green-600">Informasi Simpanan</h2>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Simpanan Wajib</p>
                            <p class="font-medium">
                                @if($anggota->simpanan_wajib)
                                    Rp {{ number_format($anggota->simpanan_wajib, 0, ',', '.') }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Simpanan Sukarela</p>
                            <p class="font-medium">
                                @if($anggota->simpanan_sukarela)
                                    Rp {{ number_format($anggota->simpanan_sukarela, 0, ',', '.') }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Simpanan Khusus 2</p>
                            <p class="font-medium">
                                @if($anggota->simpanan_khusus_2)
                                    Rp {{ number_format($anggota->simpanan_khusus_2, 0, ',', '.') }}
                                @else
                                    <span class="text-gray-500 italic">Tidak ada Data</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Aksi -->
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('master-data.data_anggota.edit', $anggota->id) }}" 
                   class="bg-yellow-500 text-white px-4 py-2 rounded-lg hover:bg-yellow-600">
                    <i class="fas fa-edit mr-2"></i>Edit Data
                </a>
                <form action="{{ route('master-data.data_anggota.destroy', $anggota->id) }}" 
                      method="POST" 
                      onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
                      class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600">
                        <i class="fas fa-trash mr-2"></i>Hapus Data
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection 