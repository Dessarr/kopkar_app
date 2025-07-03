@extends('layouts.app')

@section('title', 'Billing')
@section('sub-title', 'Tagihan Bulanan')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Billing</h1>
        <div class="flex place-content-around items-center w-1/2">
            <div class="bg-green-100 p-2 rounded-lg border-2 border-green-400 space-x-2 flex justify-around">
                <p class="text-sm">Export</p> <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}"
                    class="h-auto w-[20px]">
            </div>
            <div class="bg-gray-100 p-2 flex flex-row space-x-2 item-center rounded-lg border-2 border-gray-300">
                <i class="fa-solid fa-magnifying-glass  " style="color:gray;"></i>
                <p class="text-sm text-gray-500  ">Kode Anggota</p>
            </div>

            <div class="relative">
                <input type="month" id="popupMonthPicker" name="periode" class="hidden" onchange="this.form.submit()" />

                <div onclick="document.getElementById('popupMonthPicker').showPicker()"
                    class="cursor-pointer bg-gray-100 p-3 flex flex-row items-center rounded-lg border-2 border-gray-300">
                    <img src="{{ asset('img/icons-bootstrap/calendar/calendar4.svg') }}" class="w-5 h-5"
                        alt="Calendar Icon">
                </div>
            </div>


            <div class="bg-green-100 py-2 px-5 rounded-lg border-2 border-green-400">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </div>
        </div>
    </div>

    <!-- Tabel Transaksi -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Riwayat Tagihan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300 text-center">
                <thead class="bg-gray-50">
                    <tr class="text-sm align-middle w-full">
                        <th class="py-2 px-5 border">No</th>
                        <th class="p-5 border whitespace-nowrap">Nama Anggota</th>
                        <th class="p-5 border whitespace-nowrap">KTP</th>
                        <th class="p-5 border whitespace-nowrap">ID Tagihan</th>
                        <th class="p-5 border whitespace-nowrap">Simpanan Wajib</th>
                        <th class="p-5 border whitespace-nowrap">Simpanan Sukarela</th>
                        <th class="p-5 border whitespace-nowrap">Simpanan Khusus 2</th>
                        <th class="p-5 border whitespace-nowrap">Billing Total</th>

                    </tr>
                </thead>
                <tbody>
                    @foreach($dataBilling as $billing)
                    <tr class="text-sm align-middle">
                        <td class="py-2 border">
                            {{ ($dataBilling->currentPage() - 1) * $dataBilling->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-2 border">{{ $billing->nama }}</td>
                        <td class="py-2 border">{{ $billing->no_ktp }}</td>
                        <td class="py-2 border">{{ $billing->id_tagihan }}</td>
                        <td class="py-2 border">Rp {{ number_format($billing->simpanan_wajib, 0, ',', '.') }}</td>
                        <td class="py-2 border">Rp {{ number_format($billing->simpanan_sukarela, 0, ',', '.') }}</td>
                        <td class="py-2 border">Rp {{ number_format($billing->simpanan_khusus_2, 0, ',', '.') }}</td>
                        <td class="py-2 border">Rp {{ number_format($billing->total_billing, 0, ',', '.') }}</td>


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
                @for ($i = 1; $i <= $dataBilling->lastPage(); $i++)
                    @if ($i == 1 || $i == $dataBilling->lastPage() || ($i >= $dataBilling->currentPage() - 1 && $i <=
                        $dataBilling->
                        currentPage() + 1))
                        <a href="{{ $dataBilling->url($i) }}">
                            <div
                                class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $dataBilling->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </a>
                        @elseif ($i == 2 || $i == $dataBilling->lastPage() - 1)
                        <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                        @endif
                        @endfor
            </div>
        </div>


        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Displaying {{ $dataBilling->firstItem() }} to {{ $dataBilling->lastItem() }} of {{ $dataBilling->total() }}
            items
        </div>

    </div>
</div>

<div class="popup">

</div>

<style>
.scroll-tbody {
    display: block;
    max-height: 400px;
    /* atur tinggi sesuai kebutuhan */
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