@extends('layouts.app')

@section('title', 'Tagihan Toserda')
@section('sub-title', 'Tagihan & Riwayat Toserda')

@section('content')
<div class="container">
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('error') }}</span>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-lg p-6 col-span-3 md:col-span-1">
            <h2 class="text-lg font-semibold mb-4">Informasi Anggota</h2>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span class="text-gray-600">Nama:</span>
                    <span class="font-medium">{{ $member->nama }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">No. KTP:</span>
                    <span class="font-medium">{{ $member->no_ktp }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Tanggal Daftar:</span>
                    <span class="font-medium">{{ \Carbon\Carbon::parse($member->tgl_daftar)->format('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6 col-span-3 md:col-span-2">
            <h2 class="text-lg font-semibold mb-4">Ringkasan Tagihan Toserda</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="bg-blue-50 p-4 rounded-lg border border-blue-200">
                    <div class="text-sm text-blue-700">Total Tagihan</div>
                    <div class="text-2xl font-bold text-blue-800">{{ number_format($billings->sum('total_tagihan'), 0, ',', '.') }}</div>
                </div>
                <div class="bg-green-50 p-4 rounded-lg border border-green-200">
                    <div class="text-sm text-green-700">Sudah Dibayar</div>
                    <div class="text-2xl font-bold text-green-800">{{ number_format($billings->where('status_bayar', 'Lunas')->sum('total_tagihan'), 0, ',', '.') }}</div>
                </div>
                <div class="bg-red-50 p-4 rounded-lg border border-red-200">
                    <div class="text-sm text-red-700">Belum Dibayar</div>
                    <div class="text-2xl font-bold text-red-800">{{ number_format($billings->where('status_bayar', 'Belum Lunas')->sum('total_tagihan'), 0, ',', '.') }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Tagihan Toserda -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Tagihan Toserda</h2>
        
        <!-- Filter -->
        <div class="mb-6 p-4 border rounded-lg bg-gray-50">
            <form action="{{ route('member.toserda.payment') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-auto">
                    <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="status"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        <option value="">Semua</option>
                        <option value="Lunas" {{ request('status') == 'Lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="Belum Lunas" {{ request('status') == 'Belum Lunas' ? 'selected' : '' }}>Belum Lunas</option>
                    </select>
                </div>

                <div class="flex-shrink-0">
                    <button type="submit"
                        class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50">
                        Filter
                    </button>
                    <a href="{{ route('member.toserda.payment') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 ml-2">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Tagihan -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b text-left">ID Billing</th>
                        <th class="px-4 py-2 border-b text-left">Periode</th>
                        <th class="px-4 py-2 border-b text-left">Total Tagihan</th>
                        <th class="px-4 py-2 border-b text-left">Status</th>
                        <th class="px-4 py-2 border-b text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($billings as $billing)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border-b">{{ $billing->biliing_code}}</td>
                        <td class="px-4 py-2 border-b">{{ $billing->bulan_tahun }}</td>
                        <td class="px-4 py-2 border-b">{{ number_format($billing->total_tagihan, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 border-b">
                            @if($billing->status_bayar == 'Lunas')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Lunas</span>
                            @else
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Belum Lunas</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 border-b">
                            @if($billing->status_bayar != 'Lunas')
                                <form action="{{ route('member.toserda.process', $billing->billing_code) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-blue-600 hover:text-blue-800 hover:underline">
                                        Bayar Sekarang
                                    </button>
                                </form>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada tagihan Toserda</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $billings->links() }}
        </div>
    </div>

    <!-- Riwayat Transaksi -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Riwayat Transaksi Toserda</h2>
        
        <!-- Tabel Transaksi -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b text-left">Tanggal</th>
                        <th class="px-4 py-2 border-b text-left">Barang</th>
                        <th class="px-4 py-2 border-b text-left">Jumlah</th>
                        <th class="px-4 py-2 border-b text-left">Keterangan</th>
                        <th class="px-4 py-2 border-b text-left">Status Billing</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tr)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border-b">{{ $tr->tgl_transaksi->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 border-b">{{ $tr->barang->nama_barang ?? 'N/A' }}</td>
                        <td class="px-4 py-2 border-b">{{ number_format($tr->jumlah, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 border-b">{{ $tr->keterangan }}</td>
                        <td class="px-4 py-2 border-b">
                            @if($tr->billing->count() > 0)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Sudah Billing</span>
                            @else
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Belum Billing</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-4 text-center text-gray-500">Belum ada transaksi Toserda</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $transactions->links() }}
        </div>
    </div>
</div>
@endsection 