@extends('layouts.app')

@section('title', 'Detail Data Pengguna')
@section('sub-title', 'Detail Data Pengguna')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Detail Data Pengguna</h1>
                <p class="text-green-100">{{ $pengguna->u_name }}</p>
            </div>
            <div class="text-right">
                <a href="{{ route('master-data.data_pengguna') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Detail Section -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Basic Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">
                        <i class="fas fa-info-circle mr-2"></i>Informasi Dasar
                    </h3>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">ID</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono">{{ $pengguna->id }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Username</label>
                            <p class="mt-1 text-sm text-gray-900 font-semibold">{{ $pengguna->u_name }}</p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Level</label>
                            <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pengguna->level_badge }}">
                                {{ $pengguna->level_text }}
                            </span>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Status</label>
                            <span class="mt-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $pengguna->status_aktif_badge }}">
                                {{ $pengguna->status_aktif_text }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Additional Information -->
                <div class="space-y-4">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-2">
                        <i class="fas fa-building mr-2"></i>Informasi Cabang
                    </h3>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-500">Cabang</label>
                            <p class="mt-1 text-sm text-gray-900">
                                {{ $pengguna->cabang ? $pengguna->cabang->nama : 'Tidak ada cabang' }}
                            </p>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-500">ID Cabang</label>
                            <p class="mt-1 text-sm text-gray-900 font-mono">{{ $pengguna->id_cabang }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="mt-8 pt-6 border-t">
                <h3 class="text-lg font-semibold text-gray-900 border-b pb-2 mb-4">
                    <i class="fas fa-clock mr-2"></i>Informasi Waktu
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Dibuat Pada</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $pengguna->created_at ? $pengguna->created_at->format('d F Y H:i:s') : 'Tidak ada data' }}
                        </p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-500">Terakhir Diupdate</label>
                        <p class="mt-1 text-sm text-gray-900">
                            {{ $pengguna->updated_at ? $pengguna->updated_at->format('d F Y H:i:s') : 'Tidak ada data' }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-8 pt-6 border-t">
                <div class="flex justify-end space-x-4">
                    <a href="{{ route('master-data.data_pengguna') }}" 
                       class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali
                    </a>
                    <a href="{{ route('master-data.data_pengguna.edit', $pengguna->id) }}" 
                       class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-edit mr-2"></i>Edit
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection