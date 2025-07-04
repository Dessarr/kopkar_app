@extends('layouts.app')

@section('title', 'Pengajuan Penarikan')
@section('sub-title', 'Pengajuan Penarikan')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Pengajuan Penarikan</h1>
        <div class="flex place-content-around items-center w-1/2">
            <div class="bg-green-100 p-2 rounded-lg border-2 border-green-400 space-x-2 flex justify-around">
                <p class="text-sm">Export</p> <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}"
                    class="h-auto w-[20px]">
            </div>
            <div class="bg-gray-100 p-2 flex flex-row space-x-2 item-center rounded-lg border-2 border-gray-300">
                <i class="fa-solid fa-magnifying-glass" style="color:gray;"></i>
                <p class="text-sm text-gray-500">Cari Pengajuan</p>
            </div>
            <div class="bg-gray-100 p-3 flex flex-row item-center rounded-lg border-2 border-gray-300">
                <img src="{{ asset('img/icons-bootstrap/calendar/calendar4.svg') }}">
            </div>
            <div class="bg-green-100 py-2 px-5 rounded-lg border-2 border-green-400">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </div>
        </div>
    </div>

    <!-- Tabel Pengajuan Penarikan -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Daftar Pengajuan Penarikan</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300 text-center">
                <thead class="bg-gray-50">
                    <tr class="text-sm align-middle w-full">
                        <th class="py-2 px-5 border">No</th>
                        <th class="p-5 border whitespace-nowrap">ID</th>
                        <th class="p-5 border whitespace-nowrap">No Ajuan</th>
                        <th class="p-5 border whitespace-nowrap">Ajuan ID</th>
                        <th class="p-5 border whitespace-nowrap">Anggota ID</th>
                        <th class="p-5 border whitespace-nowrap">Tgl Input</th>
                        <th class="p-5 border whitespace-nowrap">Jenis</th>
                        <th class="p-5 border whitespace-nowrap">Nominal</th>
                        <th class="p-5 border whitespace-nowrap">Lama Angsuran</th>
                        <th class="p-5 border whitespace-nowrap">Keterangan</th>
                        <th class="p-5 border whitespace-nowrap">Status</th>
                        <th class="p-5 border whitespace-nowrap">Alasan</th>
                        <th class="p-5 border whitespace-nowrap">Tgl Cair</th>
                        <th class="p-5 border whitespace-nowrap">Tgl Update</th>
                        <th class="p-5 border whitespace-nowrap">ID Cabang</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dataPengajuan as $pengajuan)
                    <tr class="text-sm align-middle">
                        <td class="py-2 border">
                            {{ ($dataPengajuan->currentPage() - 1) * $dataPengajuan->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-2 border">{{ $pengajuan->id }}</td>
                        <td class="py-2 border">{{ $pengajuan->no_ajuan }}</td>
                        <td class="py-2 border">{{ $pengajuan->ajuan_id }}</td>
                        <td class="py-2 border">{{ $pengajuan->anggota_id }}</td>
                        <td class="py-2 border">{{ $pengajuan->tgl_input }}</td>
                        <td class="py-2 border">{{ $pengajuan->jenis }}</td>
                        <td class="py-2 border">{{ number_format($pengajuan->nominal, 2) }}</td>
                        <td class="py-2 border">{{ $pengajuan->lama_ags }}</td>
                        <td class="py-2 border">{{ $pengajuan->keterangan }}</td>
                        <td class="py-2 border">{{ $pengajuan->status }}</td>
                        <td class="py-2 border">{{ $pengajuan->alasan }}</td>
                        <td class="py-2 border">{{ $pengajuan->tgl_cair }}</td>
                        <td class="py-2 border">{{ $pengajuan->tgl_update }}</td>
                        <td class="py-2 border">{{ $pengajuan->id_cabang }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <!-- Pagination -->
    <div class="mt-5 w-full relative px-2 py-2">
        <div class="mx-auto w-fit">
            <div
                class="bg-white px-4 py-1 flex flex-row rounded-full justify-center items-center space-x-2 border border-gray-300 shadow-sm">
                @for ($i = 1; $i <= $dataPengajuan->lastPage(); $i++)
                    @if ($i == 1 || $i == $dataPengajuan->lastPage() || ($i >= $dataPengajuan->currentPage() - 1 && $i
                    <= $dataPengajuan->
                        currentPage() + 1))
                        <a href="{{ $dataPengajuan->url($i) }}">
                            <div
                                class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $dataPengajuan->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </a>
                        @elseif ($i == 2 || $i == $dataPengajuan->lastPage() - 1)
                        <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                        @endif
                        @endfor
            </div>
        </div>

        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Displaying {{ $dataPengajuan->firstItem() }} to {{ $dataPengajuan->lastItem() }} of
            {{ $dataPengajuan->total() }}
            items
        </div>
    </div>
</div>
@endsection
