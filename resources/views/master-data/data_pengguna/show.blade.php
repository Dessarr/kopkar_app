@extends('layouts.app')

@section('title', 'Detail Data Pengguna')
@section('sub-title', 'Detail User')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Data Pengguna</h1>
        <div class="flex space-x-2">
            <a href="{{ route('master-data.data_pengguna.edit', $pengguna->id) }}" 
               class="inline-flex items-center gap-2 bg-blue-100 hover:bg-blue-200 text-blue-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-edit"></i>
                Edit
            </a>
            <a href="{{ route('master-data.data_pengguna') }}" 
               class="inline-flex items-center gap-2 bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg transition">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Informasi User</h3>
        </div>
        
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- ID -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">ID</label>
                    <p class="text-sm text-gray-900 font-mono">{{ $pengguna->id }}</p>
                </div>

                <!-- Username -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Username</label>
                    <p class="text-sm text-gray-900 font-semibold">{{ $pengguna->u_name }}</p>
                </div>

                <!-- Level -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Level</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pengguna->level_badge }}">
                        {{ $pengguna->level_text }}
                    </span>
                </div>

                <!-- Cabang -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Cabang</label>
                    <p class="text-sm text-gray-900">{{ $pengguna->cabang ? $pengguna->cabang->nama : '-' }}</p>
                </div>

                <!-- Status Aktif -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Status Aktif</label>
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pengguna->status_aktif_badge }}">
                        {{ $pengguna->status_aktif_text }}
                    </span>
                </div>

                <!-- Tanggal Dibuat -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Dibuat</label>
                    <p class="text-sm text-gray-900">{{ $pengguna->created_at ? $pengguna->created_at->format('d/m/Y H:i') : '-' }}</p>
                </div>

                <!-- Tanggal Diupdate -->
                <div>
                    <label class="block text-sm font-medium text-gray-500 mb-1">Tanggal Diupdate</label>
                    <p class="text-sm text-gray-900">{{ $pengguna->updated_at ? $pengguna->updated_at->format('d/m/Y H:i') : '-' }}</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 pt-6 border-t border-gray-200">
                <div class="flex justify-between items-center">
                    <div class="text-sm text-gray-500">
                        <i class="fas fa-info-circle mr-1"></i>
                        Data terakhir diupdate {{ $pengguna->updated_at ? $pengguna->updated_at->diffForHumans() : 'tidak diketahui' }}
                    </div>
                    <div class="flex space-x-2">
                        <a href="{{ route('master-data.data_pengguna.edit', $pengguna->id) }}" 
                           class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-[#14AE5C] hover:bg-[#11994F] focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#14AE5C]">
                            <i class="fas fa-edit mr-2"></i>
                            Edit Data
                        </a>
                        <form action="{{ route('master-data.data_pengguna.destroy', $pengguna->id) }}" method="POST" 
                              onsubmit="return confirm('Apakah Anda yakin ingin menghapus data ini?');" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                <i class="fas fa-trash mr-2"></i>
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection