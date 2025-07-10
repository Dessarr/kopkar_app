@extends('layouts.app')

@section('title', 'Billing Anggota')
@section('sub-title', 'Tagihan Bulanan Anggota')

@section('content')

<div class="container">
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif


    <!-- Tabel Transaksi -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Riwayat Tagihan</h2>
            <div class="bg-white rounded-lg shadow-lg p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-semibold">Daftar Tagihan Anggota</h2>
                    <div class="flex space-x-2">
                        <a href="{{ route('billing.export.excel') }}"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                        <a href="{{ route('billing.export.pdf') }}"
                            class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
                            <i class="fas fa-file-pdf"></i> Export PDF
                        </a>
                    </div>
                </div>

                <!-- Filter -->
                <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                    <form action="{{ route('billing.index') }}" method="GET" class="flex flex-wrap items-end gap-4">
                        <div class="w-full sm:w-auto">
                            <label for="filter_bulan" class="block text-sm font-medium text-gray-700">Bulan</label>
                            <select name="bulan" id="filter_bulan"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                                <option value="">Semua</option>
                                <option value="1" {{ request('bulan') == '1' ? 'selected' : '' }}>Januari</option>
                                <option value="2" {{ request('bulan') == '2' ? 'selected' : '' }}>Februari</option>
                                <option value="3" {{ request('bulan') == '3' ? 'selected' : '' }}>Maret</option>
                                <option value="4" {{ request('bulan') == '4' ? 'selected' : '' }}>April</option>
                                <option value="5" {{ request('bulan') == '5' ? 'selected' : '' }}>Mei</option>
                                <option value="6" {{ request('bulan') == '6' ? 'selected' : '' }}>Juni</option>
                                <option value="7" {{ request('bulan') == '7' ? 'selected' : '' }}>Juli</option>
                                <option value="8" {{ request('bulan') == '8' ? 'selected' : '' }}>Agustus</option>
                                <option value="9" {{ request('bulan') == '9' ? 'selected' : '' }}>September</option>
                                <option value="10" {{ request('bulan') == '10' ? 'selected' : '' }}>Oktober</option>
                                <option value="11" {{ request('bulan') == '11' ? 'selected' : '' }}>November
                                </option>
                                <option value="12" {{ request('bulan') == '12' ? 'selected' : '' }}>Desember
                                </option>
                            </select>
                        </div>

                        <div class="w-full sm:w-auto">
                            <label for="filter_tahun" class="block text-sm font-medium text-gray-700">Tahun</label>
                            <input type="number" name="tahun" id="filter_tahun"
                                value="{{ request('tahun', date('Y')) }}"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        </div>

                        <div class="w-full sm:w-auto">
                            <label for="search" class="block text-sm font-medium text-gray-700">Cari Anggota</label>
                            <input type="text" name="search" id="search" value="{{ request('search') }}"
                                placeholder="Nama atau No KTP"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        </div>

                        <div class="flex-shrink-0">
                            <button type="submit"
                                class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50">
                                Filter
                            </button>
                            <a href="{{ route('billing.index') }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 ml-2">
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Tabel Data -->
                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white border border-gray-200">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="px-4 py-2 border-b text-left">No.</th>
                                <th class="px-4 py-2 border-b text-left">No KTP</th>
                                <th class="px-4 py-2 border-b text-left">Nama</th>
                                <th class="px-4 py-2 border-b text-left">Simpanan Wajib</th>
                                <th class="px-4 py-2 border-b text-left">Simpanan Sukarela</th>
                                <th class="px-4 py-2 border-b text-left">Toserda (Khusus 2)</th>
                                <th class="px-4 py-2 border-b text-left">Jenis Transaksi</th>
                                <th class="px-4 py-2 border-b text-left">Total Tagihan</th>
                                <th class="px-4 py-2 border-b text-center">Status</th>
                                <th class="px-4 py-2 border-b text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($dataBilling as $index => $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2 border-b">{{ $index + $dataBilling->firstItem() }}</td>
                                <td class="px-4 py-2 border-b">{{ $item->no_ktp }}</td>
                                <td class="px-4 py-2 border-b">{{ $item->nama }}</td>
                                <td class="px-4 py-2 border-b">Rp
                                    {{ number_format($item->simpanan_wajib ?? 0, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 border-b">Rp
                                    {{ number_format($item->simpanan_sukarela ?? 0, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 border-b">Rp
                                    {{ number_format($item->simpanan_khusus_2 ?? 0, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 border-b">{{ $item->jns_trans ?? 'Toserda' }}</td>
                                <td class="px-4 py-2 border-b font-bold">Rp
                                    {{ number_format($item->total_billing, 0, ',', '.') }}</td>
                                <td class="px-4 py-2 border-b text-center">
                                    <span
                                        class="px-2 py-1 rounded-full text-xs 
                                {{ $item->status_bayar == 'Lunas' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                        {{ $item->status_bayar ?? 'Belum Lunas' }}
                                    </span>
                                </td>
                                <td class="px-4 py-2 border-b text-center">
                                    @if(($item->status_bayar ?? 'Belum Lunas') != 'Lunas')
                                    <form action="{{ route('billing.process', $item->id) }}" method="POST"
                                        class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="px-3 py-1 bg-green-500 text-white rounded hover:bg-green-600">
                                            Proses
                                        </button>
                                    </form>
                                    @else
                                    <span class="text-gray-400">Sudah Diproses</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="9" class="px-4 py-4 text-center text-gray-500">Belum ada data billing
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="mt-4">
                    {{ $dataBilling->links() }}
                </div>
            </div>
        </div>
        <div class="popup">

        </div>

        <style>
        .active-month {
            background-color: #dbeafe;
            /* Tailwind bg-blue-100 */
            border: 2px solid #2563eb;
            /* Tailwind blue-600 */
            font-weight: bold;
        }
        </style>
        @endsection