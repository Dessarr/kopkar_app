@extends('layouts.app')

@section('title', 'Detail Data Anggota')
@section('sub-title', 'Master Data Anggota')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Data Anggota</h1>
            <p class="text-sm text-gray-600 mt-1">Informasi lengkap data anggota: {{ $anggota->nama }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('master-data.data_anggota.edit', $anggota->id) }}" 
               class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-edit"></i>
                <span>Edit</span>
            </a>
            <a href="{{ route('master-data.data_anggota.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
        </div>
    </div>

    <!-- Detail Information -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Profile Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-center">
                    <!-- Photo -->
                    <div class="mb-4">
                        @if($anggota->file_pic && Storage::disk('public')->exists('anggota/' . $anggota->file_pic))
                        <img src="{{ asset('storage/anggota/' . $anggota->file_pic) }}" 
                             alt="Foto {{ $anggota->nama }}" 
                             class="w-32 h-32 rounded-full mx-auto object-cover border-4 border-gray-200">
                        @else
                        <div class="w-32 h-32 rounded-full bg-gray-100 mx-auto flex items-center justify-center border-4 border-gray-200">
                            <i class="fas fa-user text-gray-400 text-4xl"></i>
                        </div>
                        @endif
                    </div>
                    
                    <!-- Name and Status -->
                    <h2 class="text-xl font-bold text-gray-900 mb-2">{{ $anggota->nama }}</h2>
                    <p class="text-sm text-gray-600 mb-4">{{ $anggota->no_ktp }}</p>
                    
                    <!-- Status Badge -->
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $anggota->aktif == 'Y' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                            <i class="fas {{ $anggota->aktif == 'Y' ? 'fa-check-circle' : 'fa-times-circle' }} mr-2"></i>
                            {{ $anggota->aktif == 'Y' ? 'Aktif' : 'Tidak Aktif' }}
                        </span>
                    </div>
                    
                    <!-- Gender Badge -->
                    <div class="mb-4">
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $anggota->jk == 'L' ? 'bg-blue-100 text-blue-800' : 'bg-pink-100 text-pink-800' }}">
                            <i class="fas {{ $anggota->jk == 'L' ? 'fa-male' : 'fa-female' }} mr-2"></i>
                            {{ $anggota->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Information Details -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-lg shadow">
                <!-- Personal Information -->
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Pribadi</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Nama Lengkap</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $anggota->nama ?: '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">No KTP</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $anggota->no_ktp ?: '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Tempat Lahir</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $anggota->tmp_lahir ?: '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Tanggal Lahir</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($anggota->tgl_lahir && $anggota->tgl_lahir != '0000-00-00')
                                    {{ date('d F Y', strtotime($anggota->tgl_lahir)) }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Jenis Kelamin</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $anggota->jk == 'L' ? 'Laki-laki' : 'Perempuan' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Username</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $anggota->username ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div class="p-6 border-b">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Kontak</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-500">Alamat</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $anggota->alamat ?: '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Kota</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $anggota->kota ?: '-' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Departement</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $anggota->departement ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Informasi Sistem</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Tanggal Daftar</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($anggota->tgl_daftar && $anggota->tgl_daftar != '0000-00-00')
                                    {{ date('d F Y', strtotime($anggota->tgl_daftar)) }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <p class="mt-1 text-sm text-gray-900">{{ $anggota->aktif == 'Y' ? 'Aktif' : 'Tidak Aktif' }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Dibuat</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($anggota->created_at)
                                    {{ $anggota->created_at->format('d F Y H:i') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Diupdate</label>
                            <p class="mt-1 text-sm text-gray-900">
                                @if($anggota->updated_at)
                                    {{ $anggota->updated_at->format('d F Y H:i') }}
                                @else
                                    -
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="mt-6 flex justify-center space-x-4">
        <a href="{{ route('master-data.data_anggota.edit', $anggota->id) }}" 
           class="bg-yellow-600 hover:bg-yellow-700 text-white px-6 py-2 rounded-lg flex items-center space-x-2 transition-colors">
            <i class="fas fa-edit"></i>
            <span>Edit Data</span>
        </a>
        <form action="{{ route('master-data.data_anggota.destroy', $anggota->id) }}" 
              method="POST" 
              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?')"
              class="inline">
            @csrf
            @method('DELETE')
            <button type="submit" 
                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-trash"></i>
                <span>Hapus Data</span>
            </button>
        </form>
    </div>
</div>
@endsection
