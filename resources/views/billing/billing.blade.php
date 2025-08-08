@extends('layouts.app')

@section('title', 'Billing Simpanan')
@section('sub-title', 'Tagihan Bulanan Simpanan')

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
                    <a href="{{ route('billing.export.excel') }}"
                        class="inline-flex items-center gap-2 bg-green-50 border border-green-400 text-green-900 font-medium px-5 py-2 rounded-lg transition hover:bg-green-100 hover:border-green-500">
                        <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}" class="h-5 w-5"
                            alt="Export Excel">
                        <span class="text-sm">Export Excel</span>
                    </a>
                    <a href="{{ route('billing.export.pdf') }}"
                        class="inline-flex items-center gap-2 bg-red-50 border border-red-400 text-red-900 font-medium px-5 py-2 rounded-lg transition hover:bg-red-100 hover:border-red-500">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" />
                        </svg>
                        <span class="text-sm">Export PDF</span>
                    </a>
                    <form action="{{ route('billing.simpanan.process_all') }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="bulan" value="{{ $bulan }}" />
                        <input type="hidden" name="tahun" value="{{ $tahun }}" />
                        <button type="submit"
                            class="inline-flex items-center gap-2 bg-blue-50 border border-blue-400 text-blue-900 font-medium px-5 py-2 rounded-lg transition hover:bg-blue-100 hover:border-blue-500"
                            onclick="return confirm('Proses semua billing simpanan bulan ini ke Billing Utama?')">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            <span class="text-sm">Proses All ke Billing Utama</span>
                        </button>
                    </form>
                </div>
            </div>

            <div class="mb-6">
                <form action="{{ route('billing.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-3">
                        <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                        <select name="bulan" id="bulan"
                            class="w-full rounded-lg border-2 border-gray-300 bg-gray-100 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm py-2 px-3">
                            @foreach($bulanList as $key => $namaBulan)
                            <option value="{{ $key }}" {{ (isset($bulan) && $bulan == $key) ? 'selected' : '' }}>
                                {{ $namaBulan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                        <select name="tahun" id="tahun"
                            class="w-full rounded-lg border-2 border-gray-300 bg-gray-100 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm py-2 px-3">
                            @foreach($tahunList ?? [] as $tahunOption)
                            <option value="{{ $tahunOption }}"
                                {{ (isset($tahun) && $tahun == $tahunOption) ? 'selected' : '' }}>{{ $tahunOption }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Anggota</label>
                        <div class="flex items-center bg-gray-100 p-2 rounded-lg border-2 border-gray-300">
                            <i class="fa-solid fa-magnifying-glass mr-2 text-gray-400"></i>
                            <input type="text"
                                class="text-sm text-gray-500 bg-transparent border-none focus:outline-none w-full"
                                id="search" name="search" placeholder="Nama atau No ID Koperasi"
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="md:col-span-2 flex items-end">
                        <div class="flex space-x-2">
                            <button type="submit"
                                class="bg-blue-100 hover:bg-blue-200 text-blue-800 text-sm font-medium px-4 py-2 rounded-lg border-2 border-blue-300 transition">Filter</button>
                            <a href="{{ route('billing.index') }}"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg border-2 border-gray-300 transition">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm">
                            <th class="px-4 py-3 border-b text-center w-12">No.</th>
                            <th class="px-4 py-3 border-b text-center">ID Billing</th>
                            <th class="px-4 py-3 border-b text-center">ID Koperasi</th>
                            <th class="px-4 py-3 border-b text-center">Nama</th>
                            <th class="px-4 py-3 border-b text-center">Jenis Transaksi</th>
                            <th class="px-4 py-3 border-b text-center">Total Tagihan</th>
                            <th class="px-4 py-3 border-b text-center">Status</th>
                            <th class="px-4 py-3 border-b text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($dataBilling as $index => $billing)
                        <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                            <td class="px-4 py-3 text-center text-sm">{{ $index + 1 }}</td>
                            <td class="px-4 py-3 text-center text-[12px]">
                                {{ $billing->billing_code ?? 'BIL-' . ($billing->bulan ?? '') . ($billing->tahun ?? '') . '-' . substr(md5($billing->id ?? $index), 0, 5) }}
                            </td>
                            <td class="px-4 py-3 text-center text-sm">{{ $billing->no_ktp }}</td>
                            <td class="px-4 py-3">
                                <div class="text-sm">
                                    <p class="font-medium text-gray-900">{{ $billing->nama }}</p>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-center text-sm">{{ $billing->jns_trans ?? 'Billing' }}</td>
                            @php
                            $total = $billing->total_tagihan;
                            if (empty($total) || $total == 0) {
                            $total = $billing->jumlah;
                            }
                            @endphp
                            <td class="px-4 py-3 text-right text-sm font-medium">
                                {{ number_format($total ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                @if($billing->status_bayar == 'Lunas' || $billing->status == 'Y')
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800 text-nowrap">Lunas</span>
                                @else
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800 text-nowrap">Belum
                                    Lunas</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-center text-sm font-medium">
                                @php
                                $billingId = $billing->billing_code ?? $billing->id ?? null;
                                @endphp
                                @if(($billing->status_bayar != 'Lunas' && $billing->status != 'Y') && $billingId)
                                <form action="{{ route('billing.process', $billingId) }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="bg-green-100 hover:bg-green-200 text-green-800 text-xs px-3 py-1 rounded-lg border-2 border-green-300 transition"
                                        onclick="return confirm('Proses pembayaran ini?')">
                                        Proses
                                    </button>
                                </form>
                                @else
                                <span
                                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">Sudah
                                    Dibayar</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="12" class="px-4 py-3 text-center text-sm text-gray-500">Belum ada data billing
                            </td>
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