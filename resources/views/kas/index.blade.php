@extends('layouts.app')

@section('title', 'Transaksi')
@section('sub-title', 'Pemasukan Kas Tunai')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Data Transaksi</h1>
        <div class="flex place-content-around items-center w-1/2">
            <div class="bg-green-100 p-2 rounded-lg border-2 border-green-400 space-x-2 flex justify-around">
                <p class="text-sm">Export</p> <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}"
                    class="h-auto w-[20px]">
            </div>
            <div class="bg-gray-100 p-2 flex flex-row space-x-2 item-center rounded-lg border-2 border-gray-300">
                <i class="fa-solid fa-magnifying-glass  " style="color:gray;"></i>
                <p class="text-sm text-gray-500  ">Kode Transaksi</p>
            </div>

            <div class="bg-gray-100 p-3 flex flex-row item-center rounded-lg border-2 border-gray-300">
                <img src="{{ asset('img/icons-bootstrap/calendar/calendar4.svg') }}">
            </div>

            <div class="bg-green-100 py-2 px-5 rounded-lg border-2 border-green-400">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </div>
        </div>
    </div>

    <!-- Ringkasan Kas -->
    <!-- <div class=" grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            @foreach($kas as $k)
            <div class="bg-white rounded-lg shadow p-4">
                <h3 class="font-semibold text-lg mb-2">{{ $k->nama }}</h3>
                <p class="text-gray-600">{{ ucfirst($k->tipe) }}</p>
                <p class="text-xl font-bold mt-2">Rp {{ number_format($k->getSaldo(), 2, ',', '.') }}</p>
            </div>
            @endforeach
        </div> -->

    <!-- Tabel Transaksi -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Riwayat Transaksi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr class="text-sm">
                        <th class="px-3 py-2 text-left">Kode Transaksi</th>
                        <th class="px-3 py-2 text-left">Tanggal Transaksi</th>
                        <th class="px-3 py-2 text-left">Uraian</th>
                        <th class="px-3 py-2 text-left">Untuk Kas</th>
                        <th class="px-3 py-2 text-left">Dari Akun</th>
                        <th class="px-3 py-2 text-left">Oleh</th>
                        <th class="px-3 py-2 text-center">Jumlah</th>
                        <th class="px-3 py-2 text-center">User</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    <style>
                    td {
                        padding: 20px 0px
                    }
                    </style>
                    <tr class="items-center text-center text-[12px]">
                        <td>001
                        </td>
                        <td>20/20/2020
                        </td>
                        <td>Lorem Ipsum</td>
                        <td>Dolor sit amet</td>
                        <td>dessarrukmana</td>
                        <td>dessar</td>
                        <td>90000</td>
                        <td>admin</td>
                    </tr>
                    <tr class="items-center text-center text-[12px]">
                        <td>001
                        </td>
                        <td>20/20/2020
                        </td>
                        <td>Lorem Ipsum</td>
                        <td>Dolor sit amet</td>
                        <td>dessarrukmana</td>
                        <td>dessar</td>
                        <td>90000</td>
                        <td>admin</td>
                    </tr>
                    <tr class="items-center text-center text-[12px]">
                        <td>001
                        </td>
                        <td>20/20/2020
                        </td>
                        <td>Lorem Ipsum</td>
                        <td>Dolor sit amet</td>
                        <td>dessarrukmana</td>
                        <td>dessar</td>
                        <td>90000</td>
                        <td>admin</td>
                    </tr>

                </tbody>
                <!-- <tbody class="divide-y">
                    @forelse($transaksi as $t)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2">{{ $t->tanggal_transaksi->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-2">{{ $t->kas->nama }}</td>
                        <td class="px-4 py-2">
                            <span class="px-2 py-1 rounded text-sm
                                @if($t->jenis_transaksi == 'pemasukan') bg-green-100 text-green-800
                                @elseif($t->jenis_transaksi == 'pengeluaran') bg-red-100 text-red-800
                                @else bg-blue-100 text-blue-800 @endif">
                                {{ ucfirst($t->jenis_transaksi) }}
                            </span>
                        </td>
                        <td class="px-4 py-2">{{ $t->formatted_jumlah }}</td>
                        <td class="px-4 py-2">{{ $t->keterangan ?: '-' }}</td>
                        <td class="px-4 py-2">{{ $t->createdBy->username }}</td>
                        <td class="px-4 py-2 text-center">
                            <a href="{{ route('kas.show', $t->id) }}" class="text-blue-500 hover:text-blue-700">
                                Detail
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-2 text-center text-gray-500">
                            Tidak ada transaksi
                        </td>
                    </tr>
                    @endforelse
                </tbody> -->
            </table>
        </div>
    </div>
    <div class="mt-5 w-full relative px-2 py-2">
        <div class="mx-auto w-fit">
            <div
                class="bg-white px-4 py-1 flex flex-row rounded-full justify-center items-center space-x-2 border border-gray-300 shadow-sm">
                <div class="rounded-md px-2 py-0.5 border border-gray-300 bg-gray-100 text-sm">01</div>
                <div class="rounded-md px-2 py-0.5 text-sm">02</div>
                <div class="rounded-md px-2 py-0.5 text-sm">03</div>
                <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                <div class="rounded-md px-2 py-0.5 text-sm">98</div>
                <div class="rounded-md px-2 py-0.5 text-sm">99</div>
                <div class="rounded-md px-2 py-0.5 text-sm">100</div>
            </div>
        </div>

        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Displaying 1 to 0 of 0 items
        </div>
    </div>
</div>

<div class="popup">

</div>
@endsection