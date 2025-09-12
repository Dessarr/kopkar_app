@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="max-w-4xl mx-auto">
        <!-- Header -->
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Detail Jenis Akun</h1>
                <p class="text-gray-600">Informasi lengkap jenis akun</p>
            </div>
            <div class="flex space-x-3">
                <a href="{{ route('master-data.jns_akun.edit', $akun->id) }}" 
                   class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-edit mr-2"></i>
                    Edit
                </a>
                <a href="{{ route('master-data.jns_akun.index') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center">
                    <i class="fas fa-arrow-left mr-2"></i>
                    Kembali
                </a>
            </div>
        </div>

        <!-- Detail Card -->
        <div class="bg-white rounded-lg shadow-sm border">
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Kode Aktiva -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Kode Aktiva
                        </label>
                        <div class="px-3 py-2 bg-gray-50 rounded-md font-mono">
                            {{ $akun->kd_aktiva }}
                        </div>
                    </div>

                    <!-- Jenis Transaksi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jenis Transaksi
                        </label>
                        <div class="px-3 py-2 bg-gray-50 rounded-md">
                            {{ $akun->jns_trans }}
                        </div>
                    </div>

                    <!-- Akun -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Akun
                        </label>
                        <div class="px-3 py-2 bg-gray-50 rounded-md">
                            <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                {{ $akun->akun }}
                            </span>
                        </div>
                    </div>

                    <!-- Laba Rugi -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Laba Rugi
                        </label>
                        <div class="px-3 py-2 bg-gray-50 rounded-md">
                            {{ $akun->laba_rugi ?? '-' }}
                        </div>
                    </div>

                    <!-- Pemasukan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pemasukan
                        </label>
                        <div class="px-3 py-2 bg-gray-50 rounded-md">
                            @if($akun->pemasukan)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Ya
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i>Tidak
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Pengeluaran -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Pengeluaran
                        </label>
                        <div class="px-3 py-2 bg-gray-50 rounded-md">
                            @if($akun->pengeluaran)
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                    <i class="fas fa-check mr-1"></i>Ya
                                </span>
                            @else
                                <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">
                                    <i class="fas fa-times mr-1"></i>Tidak
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Status -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Status
                        </label>
                        <div class="px-3 py-2 bg-gray-50 rounded-md">
                            <span class="px-2 py-1 text-xs rounded-full {{ $akun->aktif ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $akun->aktif ? 'Aktif' : 'Tidak Aktif' }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                    <a href="{{ route('master-data.jns_akun.index') }}" 
                       class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50">
                        Kembali ke Daftar
                    </a>
                    <a href="{{ route('master-data.jns_akun.edit', $akun->id) }}" 
                       class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                        <i class="fas fa-edit mr-2"></i>
                        Edit Data
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection