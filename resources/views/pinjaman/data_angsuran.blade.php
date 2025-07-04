@extends('layouts.app')

@section('title', 'Data Anggota')
@section('sub-title', 'Data Anggota')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Data Anggota</h1>
        <div class="flex place-content-around items-center w-1/2">
            <div class="bg-green-100 p-2 rounded-lg border-2 border-green-400 space-x-2 flex justify-around">
                <p class="text-sm">Export</p> <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}"
                    class="h-auto w-[20px]">
            </div>
            <div class="bg-gray-100 p-2 flex flex-row space-x-2 item-center rounded-lg border-2 border-gray-300">
                <i class="fa-solid fa-magnifying-glass  " style="color:gray;"></i>
                <p class="text-sm text-gray-500  ">Kode Anggota</p>
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
            <h2 class="text-lg font-semibold">Riwayat Transaksi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300 text-center">
                <thead class="bg-gray-50">
                    <tr class="text-sm align-middle w-full">
                        <th class="py-2 px-5 border">No</th>
                        <th class="p-5 border whitespace-nowrap">Nama Anggota</th>
                        <th class="p-5 border whitespace-nowrap">Identitas</th>
                        <th class="p-5 border whitespace-nowrap">JK</th>
                        <th class="p-5 border whitespace-nowrap">Tgl Lahir</th>
                        <th class="p-5 border whitespace-nowrap">Status</th>
                        <th class="p-5 border whitespace-nowrap">Agama</th>
                        <th class="p-5 border whitespace-nowrap">Departement</th>
                        <th class="p-5 border whitespace-nowrap">Pekerjaan</th>
                        <th class="p-5 border whitespace-nowrap">Alamat</th>
                        <th class="p-5 border whitespace-nowrap">Kota</th>
                        <th class="p-5 border whitespace-nowrap">No Telp</th>
                        <th class="p-5 border whitespace-nowrap">Tgl Daftar</th>
                        <th class="p-5 border whitespace-nowrap">Jabatan</th>
                        <th class="p-5 border whitespace-nowrap">Aktif</th>
                        <th class="p-5 border whitespace-nowrap">File Pic</th>
                        <th class="p-5 border whitespace-nowrap">No KTP</th>
                        <th class="p-5 border whitespace-nowrap">Bank</th>
                        <th class="p-5 border whitespace-nowrap">Nama Pemilik Rekening</th>
                        <th class="p-5 border whitespace-nowrap">No Rekening</th>
                        <th class="p-5 border whitespace-nowrap">ID Tagihan</th>
                        <th class="p-5 border whitespace-nowrap">Simpanan Wajib</th>
                        <th class="p-5 border whitespace-nowrap">Simpanan Sukarela</th>
                        <th class="p-5 border whitespace-nowrap">Simpanan Khusus 2</th>
                        <th class="p-5 border whitespace-nowrap">ID Cabang</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dataAnggota as $anggota)
                    <tr class="text-sm align-middle">
                        <td class="py-2 border">
                            {{ ($dataAnggota->currentPage() - 1) * $dataAnggota->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-2 border">{{ $anggota->nama }}</td>
                        <td class="py-2 border">{{ $anggota->identitas }}</td>
                        <td class="py-2 border">{{ $anggota->jk }}</td>
                        <td class="py-2 border">{{ $anggota->tgl_lahir }}</td>
                        <td class="py-2 border">{{ $anggota->status }}</td>
                        <td class="py-2 border">{{ $anggota->agama }}</td>
                        <td class="py-2 border">{{ $anggota->departement }}</td>
                        <td class="py-2 border">{{ $anggota->pekerjaan }}</td>
                        <td class="py-2 border">{{ $anggota->alamat }}</td>
                        <td class="py-2 border">{{ $anggota->kota }}</td>
                        <td class="py-2 border">{{ $anggota->notelp }}</td>
                        <td class="py-2 border">{{ $anggota->tgl_daftar }}</td>
                        <td class="py-2 border">{{ $anggota->jabatan_id }}</td>
                        <td class="py-2 border">{{ $anggota->aktif ? 'Aktif' : 'Nonaktif' }}</td>
                        <td class="py-2 border">{{ $anggota->file_pic }}</td>
                        <td class="py-2 border">{{ $anggota->no_ktp }}</td>
                        <td class="py-2 border">{{ $anggota->bank }}</td>
                        <td class="py-2 border">{{ $anggota->nama_pemilik_rekening }}</td>
                        <td class="py-2 border">{{ $anggota->no_rekening }}</td>
                        <td class="py-2 border">{{ $anggota->id_tagihan }}</td>
                        <td class="py-2 border">{{ $anggota->simpanan_wajib }}</td>
                        <td class="py-2 border">{{ $anggota->simpanan_sukarela }}</td>
                        <td class="py-2 border">{{ $anggota->simpanan_khusus_2 }}</td>
                        <td class="py-2 border">{{ $anggota->id_cabang }}</td>
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
                @for ($i = 1; $i <= $dataAnggota->lastPage(); $i++)
                    @if ($i == 1 || $i == $dataAnggota->lastPage() || ($i >= $dataAnggota->currentPage() - 1 && $i
                    <= $dataAnggota->
                        currentPage() + 1))
                        <a href="{{ $dataAnggota->url($i) }}">
                            <div
                                class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $dataAnggota->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </a>
                        @elseif ($i == 2 || $i == $dataAnggota->lastPage() - 1)
                        <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                        @endif
                        @endfor
            </div>
        </div>


        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Displaying {{ $dataAnggota->firstItem() }} to {{ $dataAnggota->lastItem() }} of
            {{ $dataAnggota->total() }}
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