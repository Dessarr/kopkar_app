@extends('layouts.app')

@section('title', 'Pelunasan Pinjaman')
@section('sub-title', 'Data Pinjaman yang Bisa Dilunasi')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Pelunasan Pinjaman</h1>
        <div class="flex space-x-2">
            <a href="{{ route('pinjaman.data_angsuran') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-list mr-2"></i>Data Angsuran
            </a>
            <a href="{{ route('pinjaman.data_pinjaman') }}" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-money-bill mr-2"></i>Data Pinjaman
            </a>
            <a href="{{ route('pinjaman.lunas') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-check-circle mr-2"></i>Pinjaman Lunas
            </a>
        </div>
    </div>

    <!-- Tabel Transaksi -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b flex items-center justify-between">
            <h2 class="text-lg font-semibold">Data Pinjaman yang Bisa Dilunasi</h2>
            @if (session('success'))
            <div class="text-green-700 bg-green-100 border border-green-300 rounded px-3 py-1 text-sm">
                {{ session('success') }}</div>
            @endif
            @if (session('error'))
            <div class="text-red-700 bg-red-100 border border-red-300 rounded px-3 py-1 text-sm">
                {{ session('error') }}</div>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full table-fixed border border-gray-200 text-[12px]">
                <thead class="bg-gray-50 text-[12px] uppercase text-gray-600">
                    <tr class="w-full">
                        <th class="py-2 px-3 border text-center w-[36px]">No</th>
                        <th class="py-2 px-3 border text-left whitespace-nowrap w-[110px]">Kode Pinjam</th>
                        <th class="py-2 px-3 border text-left whitespace-nowrap w-[160px]">Anggota</th>
                        <th class="py-2 px-3 border text-left w-[110px]">Tanggal Pinjam</th>
                        <th class="py-2 px-3 border text-center w-[110px]">Jumlah</th>
                        <th class="py-2 px-3 border text-center whitespace-nowrap w-[46px]">Bln</th>
                        <th class="py-2 px-3 border text-center w-[100px]">Angsuran/Bln</th>
                        <th class="py-2 px-3 border text-center w-[80px]">Sudah Bayar</th>
                        <th class="py-2 px-3 border text-center w-[80px]">Sisa</th>
                        <th class="py-2 px-3 border text-center w-[100px]">Status</th>
                        <th class="py-2 px-3 border text-center w-[160px]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($dataPinjaman as $pinjaman)
                    <tr class="hover:bg-gray-50">
                        <td class="py-1 px-2 border text-center align-top">
                            {{ ($dataPinjaman->currentPage() - 1) * $dataPinjaman->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-1 px-2 border font-medium text-gray-800 align-top">
                            <div class="truncate" title="{{ $pinjaman->id }}">{{ $pinjaman->id }}</div>
                        </td>
                        <td class="py-1 px-2 border align-top">
                            @php
                            $namaAnggota = optional($pinjaman->anggota)->nama;
                            @endphp
                            <div class="leading-tight">
                                <div class="truncate hover:whitespace-normal" title="{{ $namaAnggota ?? '' }}">
                                    {{ $namaAnggota ?? '-' }}
                                </div>
                                <div class="text-[10px] text-gray-500">({{ $pinjaman->anggota_id }})</div>
                            </div>
                        </td>
                        <td class="py-1 px-2 border align-top">
                            @php $tgl = \Carbon\Carbon::parse($pinjaman->tgl_pinjam); @endphp
                            <div class="leading-tight">
                                <div class="truncate">{{ $tgl->format('d M') }}</div>
                                <div class="text-[10px] text-gray-500">{{ $tgl->format('Y') }}</div>
                            </div>
                        </td>
                        <td class="py-1 px-2 border text-right whitespace-nowrap align-top"
                            title="Rp {{ number_format($pinjaman->jumlah, 0, ',', '.') }}">
                            <div class="truncate max-w-[120px]"
                                title="Rp {{ number_format($pinjaman->jumlah, 0, ',', '.') }}">
                                Rp {{ number_format($pinjaman->jumlah, 0, ',', '.') }}
                            </div>
                        </td>
                        <td class="py-2 px-3 border text-center align-top">{{ $pinjaman->lama_angsuran }}</td>
                        <td class="py-2 px-3 border text-right align-top">
                            Rp {{ number_format($pinjaman->jumlah_angsuran, 0, ',', '.') }}
                        </td>
                        <td class="py-2 px-3 border text-center align-top">
                            @php
                            $sudahBayar = $pinjaman->detail_angsuran->count();
                            @endphp
                            {{ $sudahBayar }}x
                        </td>
                        <td class="py-2 px-3 border text-center align-top">
                            @php
                            $sisa = $pinjaman->lama_angsuran - $sudahBayar;
                            @endphp
                            {{ $sisa }}x
                        </td>
                        <td class="py-2 px-3 border text-center align-top">
                            @php
                            $statusMap = [
                                '1' => ['Aktif', 'bg-blue-100 text-blue-700 border-blue-300'],
                                '3' => ['Terlaksana', 'bg-indigo-100 text-indigo-700 border-indigo-300']
                            ];
                            [$label, $cls] = $statusMap[$pinjaman->status] ?? [$pinjaman->status, 'bg-gray-100 text-gray-700 border-gray-300'];
                            @endphp
                            <span
                                class="px-1 py-0.5 text-[10px] rounded border truncate max-w-[80px] inline-block text-center {{ $cls }}"
                                title="{{ $label }}">{{ $label }}</span>
                        </td>
                        <td class="py-2 px-3 border align-top">
                            <div class="grid grid-cols-2 gap-1">
                                <a class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-blue-50 text-blue-700 border-blue-300 hover:bg-blue-100"
                                    href="{{ route('pinjaman.data_angsuran.show', $pinjaman->id) }}">Detail</a>
                                <a class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-green-50 text-green-700 border-green-300 hover:bg-green-100"
                                    href="{{ route('pinjaman.pelunasan.show', $pinjaman->id) }}">Pelunasan</a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-5 w-full relative px-2 py-2">
            <div class="mt-6">{{ $dataPinjaman->links('vendor.pagination.simple-tailwind') }}</div>

            <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
                Menampilkan {{ $dataPinjaman->firstItem() }} - {{ $dataPinjaman->lastItem() }} dari
                {{ $dataPinjaman->total() }} data
            </div>
        </div>
    </div>
</div>

<style>
.scroll-tbody {
    display: block;
    max-height: 400px;
    overflow-x: auto;
    width: 100%;
}

.scroll-tbody tr {
    display: table;
    width: 100%;
    table-layout: fixed;
}

thead,
.scroll-tbody tr {
    width: 100%;
    table-layout: fixed;
}
</style>
@endsection
