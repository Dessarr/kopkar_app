@extends('layouts.app')

@section('title', 'Detail Data Kas')
@section('sub-title', 'Master Data Kas')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Detail Data Kas</h1>
            <p class="text-sm text-gray-600 mt-1">{{ $dataKas->nama }}</p>
        </div>
        <div class="flex space-x-3">
            <a href="{{ route('master-data.data_kas.index') }}" 
               class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-arrow-left"></i>
                <span>Kembali</span>
            </a>
            <a href="{{ route('master-data.data_kas.edit', $dataKas->id) }}" 
               class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                <i class="fas fa-edit"></i>
                <span>Edit</span>
            </a>
        </div>
    </div>

    <!-- Information Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Basic Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fas fa-info-circle text-blue-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 ml-3">Informasi Dasar</h3>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Nama Kas</label>
                    <p class="text-lg font-semibold text-gray-900">{{ $dataKas->nama }}</p>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Status</label>
                    <div class="mt-1">
                        {!! $dataKas->status_aktif_badge !!}
                    </div>
                </div>
            </div>
        </div>

        <!-- Kategori Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 bg-purple-100 rounded-lg">
                    <i class="fas fa-star text-purple-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 ml-3">Kategori</h3>
            </div>
            <div class="space-y-3">
                <div>
                    <label class="text-sm font-medium text-gray-500">Kategori Kas</label>
                    <div class="mt-1">
                        {!! $dataKas->kategori_kas_badge !!}
                    </div>
                </div>
                <div>
                    <label class="text-sm font-medium text-gray-500">Total Fitur Aktif</label>
                    <div class="flex items-center mt-1">
                        <div class="w-24 bg-gray-200 rounded-full h-2 mr-3">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: {{ ($dataKas->total_fitur_aktif / 7) * 100 }}%"></div>
                        </div>
                        <span class="text-sm font-medium">{{ $dataKas->total_fitur_aktif }}/7</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Summary Info -->
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center mb-4">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-chart-bar text-green-600"></i>
                </div>
                <h3 class="text-lg font-semibold text-gray-900 ml-3">Ringkasan</h3>
            </div>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Fitur Aktif</span>
                    <span class="font-semibold text-green-600">{{ $dataKas->total_fitur_aktif }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Fitur Tidak Aktif</span>
                    <span class="font-semibold text-red-600">{{ 7 - $dataKas->total_fitur_aktif }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-sm text-gray-500">Persentase</span>
                    <span class="font-semibold text-blue-600">{{ round(($dataKas->total_fitur_aktif / 7) * 100, 1) }}%</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Fitur Configuration -->
    <div class="bg-white rounded-lg shadow mb-8">
        <div class="p-6 border-b">
            <h3 class="text-lg font-semibold text-gray-900">Konfigurasi Fitur</h3>
            <p class="text-sm text-gray-600 mt-1">Daftar fitur yang tersedia untuk kas ini</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Simpanan -->
                <div class="flex items-center justify-between p-4 border rounded-lg {{ $dataKas->tmpl_simpan === 'Y' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                    <div class="flex items-center">
                        <div class="p-2 rounded-lg {{ $dataKas->tmpl_simpan === 'Y' ? 'bg-green-100' : 'bg-red-100' }}">
                            <i class="fas fa-piggy-bank {{ $dataKas->tmpl_simpan === 'Y' ? 'text-green-600' : 'text-red-600' }}"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Template Simpanan</h4>
                            <p class="text-xs text-gray-500">Fitur simpanan tunai</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        {!! $dataKas->tampil_simpanan_badge !!}
                    </div>
                </div>

                <!-- Penarikan -->
                <div class="flex items-center justify-between p-4 border rounded-lg {{ $dataKas->tmpl_penarikan === 'Y' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                    <div class="flex items-center">
                        <div class="p-2 rounded-lg {{ $dataKas->tmpl_penarikan === 'Y' ? 'bg-green-100' : 'bg-red-100' }}">
                            <i class="fas fa-hand-holding-usd {{ $dataKas->tmpl_penarikan === 'Y' ? 'text-green-600' : 'text-red-600' }}"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Template Penarikan</h4>
                            <p class="text-xs text-gray-500">Fitur penarikan tunai</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        {!! $dataKas->tampil_penarikan_badge !!}
                    </div>
                </div>

                <!-- Pinjaman -->
                <div class="flex items-center justify-between p-4 border rounded-lg {{ $dataKas->tmpl_pinjaman === 'Y' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                    <div class="flex items-center">
                        <div class="p-2 rounded-lg {{ $dataKas->tmpl_pinjaman === 'Y' ? 'bg-green-100' : 'bg-red-100' }}">
                            <i class="fas fa-credit-card {{ $dataKas->tmpl_pinjaman === 'Y' ? 'text-green-600' : 'text-red-600' }}"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Template Pinjaman</h4>
                            <p class="text-xs text-gray-500">Fitur pinjaman tunai</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        {!! $dataKas->tampil_pinjaman_badge !!}
                    </div>
                </div>

                <!-- Bayar -->
                <div class="flex items-center justify-between p-4 border rounded-lg {{ $dataKas->tmpl_bayar === 'Y' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                    <div class="flex items-center">
                        <div class="p-2 rounded-lg {{ $dataKas->tmpl_bayar === 'Y' ? 'bg-green-100' : 'bg-red-100' }}">
                            <i class="fas fa-money-bill-wave {{ $dataKas->tmpl_bayar === 'Y' ? 'text-green-600' : 'text-red-600' }}"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Template Bayar</h4>
                            <p class="text-xs text-gray-500">Fitur pembayaran</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        {!! $dataKas->tampil_bayar_badge !!}
                    </div>
                </div>

                <!-- Pemasukan -->
                <div class="flex items-center justify-between p-4 border rounded-lg {{ $dataKas->tmpl_pemasukan === 'Y' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                    <div class="flex items-center">
                        <div class="p-2 rounded-lg {{ $dataKas->tmpl_pemasukan === 'Y' ? 'bg-green-100' : 'bg-red-100' }}">
                            <i class="fas fa-arrow-up {{ $dataKas->tmpl_pemasukan === 'Y' ? 'text-green-600' : 'text-red-600' }}"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Template Pemasukan</h4>
                            <p class="text-xs text-gray-500">Fitur pemasukan kas</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        {!! $dataKas->tampil_pemasukan_badge !!}
                    </div>
                </div>

                <!-- Pengeluaran -->
                <div class="flex items-center justify-between p-4 border rounded-lg {{ $dataKas->tmpl_pengeluaran === 'Y' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                    <div class="flex items-center">
                        <div class="p-2 rounded-lg {{ $dataKas->tmpl_pengeluaran === 'Y' ? 'bg-green-100' : 'bg-red-100' }}">
                            <i class="fas fa-arrow-down {{ $dataKas->tmpl_pengeluaran === 'Y' ? 'text-green-600' : 'text-red-600' }}"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Template Pengeluaran</h4>
                            <p class="text-xs text-gray-500">Fitur pengeluaran kas</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        {!! $dataKas->tampil_pengeluaran_badge !!}
                    </div>
                </div>

                <!-- Transfer -->
                <div class="flex items-center justify-between p-4 border rounded-lg {{ $dataKas->tmpl_transfer === 'Y' ? 'border-green-200 bg-green-50' : 'border-red-200 bg-red-50' }}">
                    <div class="flex items-center">
                        <div class="p-2 rounded-lg {{ $dataKas->tmpl_transfer === 'Y' ? 'bg-green-100' : 'bg-red-100' }}">
                            <i class="fas fa-exchange-alt {{ $dataKas->tmpl_transfer === 'Y' ? 'text-green-600' : 'text-red-600' }}"></i>
                        </div>
                        <div class="ml-3">
                            <h4 class="text-sm font-medium text-gray-900">Template Transfer</h4>
                            <p class="text-xs text-gray-500">Fitur transfer kas</p>
                        </div>
                    </div>
                    <div class="flex items-center">
                        {!! $dataKas->tampil_transfer_badge !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="flex justify-end space-x-3">
        <a href="{{ route('master-data.data_kas.index') }}" 
           class="px-6 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors">
            Kembali ke Daftar
        </a>
        <a href="{{ route('master-data.data_kas.edit', $dataKas->id) }}" 
           class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors flex items-center space-x-2">
            <i class="fas fa-edit"></i>
            <span>Edit Data</span>
        </a>
    </div>
</div>
@endsection