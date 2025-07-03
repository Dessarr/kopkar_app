@extends('layouts.app')

@section('title', 'Data Barang')
@section('sub-title', 'Data Barang')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Data Barang</h1>
        <div class="flex place-content-around items-center w-1/2">
            <div class="bg-green-100 p-2 rounded-lg border-2 border-green-400 space-x-2 flex justify-around">
                <p class="text-sm">Export</p> <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}"
                    class="h-auto w-[20px]">
            </div>
            <div class="bg-gray-100 p-2 flex flex-row space-x-2 item-center rounded-lg border-2 border-gray-300">
                <i class="fa-solid fa-magnifying-glass  " style="color:gray;"></i>
                <p class="text-sm text-gray-500  ">Kode Barang</p>
            </div>

            <div class="bg-gray-100 p-3 flex flex-row item-center rounded-lg border-2 border-gray-300">
                <img src="{{ asset('img/icons-bootstrap/calendar/calendar4.svg') }}">
            </div>

            <div class="bg-green-100 py-2 px-5 rounded-lg border-2 border-green-400">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </div>
        </div>
    </div>

    <!-- Tabel Transaksi -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Riwayat Barang</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="table-auto w-full border border-gray-300 text-center">
                <thead class="bg-gray-100">
                    <tr class="text-sm">
                        <th class="py-2 border">No</th>
                        <th class="py-2 border">Nama Barang</th>
                        <th class="py-2 border">Type</th>
                        <th class="py-2 border">Merk</th>
                        <th class="py-2 border">Harga</th>
                        <th class="py-2 border">Jumlah Barang</th>
                        <th class="py-2 border">ID Cabang</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dataBarang as $barang)
                    <tr class="text-sm align-middle">
                        <td class="py-2 border">
                            {{ ($dataBarang->currentPage() - 1) * $dataBarang->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-2 border">{{ $barang->nm_barang }}</td>
                        <td class="py-2 border">{{ $barang->type }}</td>
                        <td class="py-2 border">{{ $barang->merk }}</td>
                        <td class="py-2 border">{{ $barang->harga }}</td>
                        <td class="py-2 border">{{ $barang->jml_brg }}</td>
                        <td class="py-2 border">{{ $barang->id_cabang }}</td>
                    </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-5 w-full relative px-2 py-2">
        <div class="mx-auto w-fit">
            <div
                class="bg-white px-4 py-1 flex flex-row rounded-full justify-center items-center space-x-2 border border-gray-300 shadow-sm">
                @for ($i = 1; $i <= $dataBarang->lastPage(); $i++)
                    @if ($i == 1 || $i == $dataBarang->lastPage() || ($i >= $dataBarang->currentPage() - 1 && $i <=
                        $dataBarang->
                        currentPage() + 1))
                        <a href="{{ $dataBarang->url($i) }}">
                            <div
                                class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $dataBarang->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </a>
                        @elseif ($i == 2 || $i == $dataBarang->lastPage() - 1)
                        <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                        @endif
                        @endfor
            </div>
        </div>


        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Displaying {{ $dataBarang->firstItem() }} to {{ $dataBarang->lastItem() }} of {{ $dataBarang->total() }} items
        </div>

    </div>
</div>

<div class="popup">

</div>
@endsection