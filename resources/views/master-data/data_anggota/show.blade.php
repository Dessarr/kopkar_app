@extends('layouts.app')

@section('title', 'Detail Data Anggota')
@section('sub-title', 'Detail Data Anggota')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Data Anggota</h1>
            <p class="text-sm text-gray-600 mt-1">Informasi lengkap data anggota koperasi</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('master-data.data_anggota') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ route('master-data.data_anggota.edit', $anggota->id) }}" 
               class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-edit"></i>
                <span>Edit</span>
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-6">
            <!-- Header with Photo and Basic Info -->
            <div class="flex flex-col md:flex-row gap-6 mb-8">
                <div class="flex-shrink-0">
                    @if($anggota->file_pic && Storage::disk('public')->exists('anggota/' . $anggota->file_pic))
                        <img src="{{ asset('storage/anggota/' . $anggota->file_pic) }}" 
                             alt="Foto {{ $anggota->nama }}" 
                             class="w-32 h-32 object-cover rounded-lg border-2 border-gray-200">
                    @else
                        <div class="w-32 h-32 bg-gray-100 rounded-lg border-2 border-gray-200 flex items-center justify-center">
                            <i class="fas fa-user text-4xl text-gray-400"></i>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $anggota->nama }}</h2>
                    <p class="text-lg text-gray-600 mb-2">ID Koperasi: <span class="font-semibold">{{ $anggota->no_ktp }}</span></p>
                    <div class="flex items-center space-x-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $anggota->aktif == 'Y' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <i class="fas fa-circle text-xs mr-2"></i>
                            {{ $anggota->status_aktif_text }}
                        </span>
                        <span class="text-gray-500">{{ $anggota->jenis_kelamin_text }}</span>
                        @if($anggota->umur)
                            <span class="text-gray-500">{{ $anggota->umur }} tahun</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Informasi Pribadi</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Tempat Lahir</label>
                        <p class="text-sm text-gray-900">{{ $anggota->tmp_lahir }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Tanggal Lahir</label>
                        <p class="text-sm text-gray-900">{{ $anggota->tgl_lahir ? $anggota->tgl_lahir->format('d F Y') : '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Status</label>
                        <p class="text-sm text-gray-900">{{ $anggota->status }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Agama</label>
                        <p class="text-sm text-gray-900">{{ $anggota->agama }}</p>
                    </div>
                </div>
            </div>

            <!-- Work Information -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Informasi Pekerjaan</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Departemen</label>
                        <p class="text-sm text-gray-900">{{ $anggota->departement }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Pekerjaan</label>
                        <p class="text-sm text-gray-900">{{ $anggota->pekerjaan }}</p>
                    </div>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Informasi Kontak</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-500">Alamat</label>
                        <p class="text-sm text-gray-900">{{ $anggota->alamat }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Kota</label>
                        <p class="text-sm text-gray-900">{{ $anggota->kota }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">No. Telepon</label>
                        <p class="text-sm text-gray-900">{{ $anggota->notelp }}</p>
                    </div>
                </div>
            </div>

            <!-- Bank Information -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Informasi Bank</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Bank</label>
                        <p class="text-sm text-gray-900">{{ $anggota->bank }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Nama Pemilik Rekening</label>
                        <p class="text-sm text-gray-900">{{ $anggota->nama_pemilik_rekening }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">No. Rekening</label>
                        <p class="text-sm text-gray-900">{{ $anggota->no_rekening }}</p>
                    </div>
                </div>
            </div>

            <!-- Savings Information -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Informasi Simpanan</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-blue-700">Simpanan Wajib</label>
                        <p class="text-lg font-bold text-blue-900">{{ $anggota->simpanan_wajib_formatted }}</p>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-green-700">Simpanan Sukarela</label>
                        <p class="text-lg font-bold text-green-900">{{ $anggota->simpanan_sukarela_formatted }}</p>
                    </div>
                    <div class="bg-purple-50 p-4 rounded-lg">
                        <label class="block text-sm font-medium text-purple-700">Simpanan Khusus 2</label>
                        <p class="text-lg font-bold text-purple-900">{{ $anggota->simpanan_khusus_2_formatted }}</p>
                    </div>
                </div>
            </div>

            <!-- Registration Information -->
            <div class="mb-8">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Informasi Pendaftaran</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Tanggal Daftar</label>
                        <p class="text-sm text-gray-900">{{ $anggota->tgl_daftar ? $anggota->tgl_daftar->format('d F Y') : '-' }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Status Keanggotaan</label>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $anggota->aktif == 'Y' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <i class="fas fa-circle text-xs mr-2"></i>
                            {{ $anggota->status_aktif_text }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
