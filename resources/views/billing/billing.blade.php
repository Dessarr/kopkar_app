@extends('layouts.app')

@section('title', 'Billing Anggota')
@section('sub-title', 'Tagihan Bulanan Anggota')

@section('content')
<div class="container mx-auto px-4">
    @if(isset($error))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ $error }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Error!</strong>
            <span class="block sm:inline">{{ session('error') }}</span>
        </div>
    @endif

    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <strong class="font-bold">Success!</strong>
            <span class="block sm:inline">{{ session('success') }}</span>
            </div>
    @endif

    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="border-b border-gray-200 px-6 py-4">
            <h5 class="font-semibold text-lg">Riwayat Tagihan</h5>
            </div>
        <div class="p-6">
            <h4 class="text-xl font-semibold mb-6">Daftar Tagihan Anggota</h4>
            
            <div class="mb-6 flex">
                <div class="flex space-x-2">
                    <a href="{{ route('billing.export.excel') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Export Excel
                    </a>
                    <a href="{{ route('billing.export.pdf') }}" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md flex items-center">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        Export PDF
                    </a>
                </div>
            </div>

            <div class="mb-6">
                <form action="{{ route('billing.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-3">
                        <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                        <select name="bulan" id="bulan" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                            @foreach($bulanList as $key => $namaBulan)
                                <option value="{{ $key }}" {{ (isset($bulan) && $bulan == $key) ? 'selected' : '' }}>{{ $namaBulan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                        <select name="tahun" id="tahun" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50">
                            @foreach($tahunList ?? [] as $tahunOption)
                                <option value="{{ $tahunOption }}" {{ (isset($tahun) && $tahun == $tahunOption) ? 'selected' : '' }}>{{ $tahunOption }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Anggota</label>
                        <input type="text" class="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50" 
                               id="search" name="search" placeholder="Nama atau No KTP" value="{{ request('search') }}">
                    </div>
                    <div class="md:col-span-2 flex items-end">
                        <div class="flex space-x-2">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">Filter</button>
                            <a href="{{ route('billing.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-md">Reset</a>
            </div>
        </div>
                </form>
    </div>

        <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No.</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Billing</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No KTP</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bulan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tahun</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Transaksi</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total Tagihan</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($dataBilling as $index => $billing)
                            <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $billing->biliing_code ?? 'BIL-' . ($billing->bulan ?? '') . ($billing->tahun ?? '') . '-' . substr(md5($billing->id ?? $index), 0, 5) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $billing->no_ktp }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $billing->nama }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $billing->bulan }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $billing->tahun }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $billing->jns_trans ?? 'Billing' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right font-medium">{{ number_format($billing->total_billing ?? $billing->total_tagihan ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($billing->status_bayar == 'Lunas' || $billing->status == 'Y')
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Lunas</span>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Belum Lunas</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    @php
                                        $billingId = $billing->biliing_code ?? $billing->id ?? null;
                                    @endphp
                                    @if(($billing->status_bayar != 'Lunas' && $billing->status != 'Y') && $billingId)
                                        <form action="{{ route('billing.process', $billingId) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white text-xs px-3 py-1 rounded-md" onclick="return confirm('Proses pembayaran ini?')">
                                                Proses Bayar
                                            </button>
                                        </form>
                                    @else
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Sudah Dibayar</span>
                                    @endif
                        </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada data billing</td>
                    </tr>
                        @endforelse
                </tbody>
            </table>
        </div>
            
            <div class="mt-6 flex justify-center">
                @if($dataBilling->hasPages())
                    <div class="pagination-links">
                        {{ $dataBilling->withQueryString()->links() }}
                            </div>
                        @endif
            </div>
        </div>
    </div>
</div>
@endsection