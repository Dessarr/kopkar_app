@extends('layouts.app')

@section('title', 'Detail Data Kas')
@section('sub-title', 'Detail Data Kas')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="bg-white rounded-lg shadow">
        <div class="p-6 border-b">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900">Detail Data Kas</h1>
                <div class="flex space-x-3">
                    <a href="{{ route('master-data.data_kas.edit', $dataKas->id) }}" 
                       class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2 transition-colors">
                        <i class="fas fa-edit"></i>
                        <span>Edit</span>
                    </a>
                    <a href="{{ route('master-data.data_kas') }}" 
                       class="text-gray-600 hover:text-gray-900 flex items-center space-x-2">
                        <i class="fas fa-arrow-left"></i>
                        <span>Kembali</span>
                    </a>
                </div>
            </div>
        </div>

        <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                <!-- Basic Information -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Dasar</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">ID</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $dataKas->id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Nama Kas</dt>
                            <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $dataKas->nama }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status Aktif</dt>
                            <dd class="mt-1">{!! $dataKas->status_aktif_badge !!}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Kategori</dt>
                            <dd class="mt-1">{!! $dataKas->kategori_kas_badge !!}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Total Fitur Aktif</dt>
                            <dd class="mt-1">
                                <div class="flex items-center">
                                    <div class="w-32 bg-gray-200 rounded-full h-3 mr-3">
                                        <div class="bg-blue-600 h-3 rounded-full" style="width: {{ ($dataKas->total_fitur_aktif / 7) * 100 }}%"></div>
                                    </div>
                                    <span class="text-sm font-medium text-gray-900">{{ $dataKas->total_fitur_aktif }}/7</span>
                                </div>
                            </dd>
                        </div>
                    </dl>
                </div>

                <!-- Fitur Configuration -->
                <div>
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Konfigurasi Fitur</h3>
                    <div class="space-y-4">
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Simpanan</span>
                            {!! $dataKas->tampil_simpanan_badge !!}
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Penarikan</span>
                            {!! $dataKas->tampil_penarikan_badge !!}
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Pinjaman</span>
                            {!! $dataKas->tampil_pinjaman_badge !!}
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Bayar</span>
                            {!! $dataKas->tampil_bayar_badge !!}
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Pemasukan</span>
                            {!! $dataKas->tampil_pemasukan_badge !!}
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Pengeluaran</span>
                            {!! $dataKas->tampil_pengeluaran_badge !!}
                        </div>
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm font-medium text-gray-700">Transfer</span>
                            {!! $dataKas->tampil_transfer_badge !!}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Fitur Summary -->
            <div class="mt-8 pt-6 border-t">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Ringkasan Fitur</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-blue-100 rounded-lg">
                                <i class="fas fa-check-circle text-blue-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-blue-900">Fitur Aktif</p>
                                <p class="text-2xl font-bold text-blue-600">{{ $dataKas->total_fitur_aktif }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-gray-100 rounded-lg">
                                <i class="fas fa-times-circle text-gray-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-gray-900">Fitur Non-Aktif</p>
                                <p class="text-2xl font-bold text-gray-600">{{ 7 - $dataKas->total_fitur_aktif }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg">
                        <div class="flex items-center">
                            <div class="p-2 bg-green-100 rounded-lg">
                                <i class="fas fa-percentage text-green-600"></i>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium text-green-900">Persentase</p>
                                <p class="text-2xl font-bold text-green-600">{{ number_format(($dataKas->total_fitur_aktif / 7) * 100, 1) }}%</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timestamps -->
            <div class="mt-8 pt-6 border-t">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Informasi Sistem</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Dibuat</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $dataKas->created_at ? $dataKas->created_at->format('d/m/Y H:i:s') : '-' }}
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Diperbarui</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ $dataKas->updated_at ? $dataKas->updated_at->format('d/m/Y H:i:s') : '-' }}
                        </dd>
                    </div>
                </dl>
            </div>

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-3 mt-8 pt-6 border-t">
                <a href="{{ route('master-data.data_kas') }}" 
                   class="px-4 py-2 text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                    Kembali ke Daftar
                </a>
                <a href="{{ route('master-data.data_kas.edit', $dataKas->id) }}" 
                   class="px-6 py-2 bg-blue-600 text-white hover:bg-blue-700 rounded-lg transition-colors">
                    Edit Data
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
