@extends('layouts.app')

@section('title', 'Data Pengajuan')
@section('sub-title', 'Riwayat Pengajuan')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Data Pengajuan</h1>
        <div class="flex place-content-around items-center w-1/2">
            <div class="bg-green-100 p-2 rounded-lg border-2 border-green-400 space-x-2 flex justify-around">
                <p class="text-sm">Export</p> <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}"
                    class="h-auto w-[20px]">
            </div>
            <div class="bg-gray-100 p-2 flex flex-row space-x-2 item-center rounded-lg border-2 border-gray-300">
                <i class="fa-solid fa-magnifying-glass  " style="color:gray;"></i>
                <p class="text-sm text-gray-500  ">Kode Pengajuan</p>
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

        <div class="p-4 border-b flex items-center justify-between">
            <h2 class="text-lg font-semibold">Riwayat Transaksi</h2>
            @if (session('success'))
                <div class="text-green-700 bg-green-100 border border-green-300 rounded px-3 py-1 text-sm">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="text-red-700 bg-red-100 border border-red-300 rounded px-3 py-1 text-sm">{{ session('error') }}</div>
            @endif
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-200">
                <thead class="bg-gray-50 text-xs uppercase text-gray-600">
                    <tr class="w-full">
                        <th class="py-3 px-4 border text-left">No</th>
                        <th class="py-3 px-4 border text-left whitespace-nowrap">ID Ajuan</th>
                        <th class="py-3 px-4 border text-left whitespace-nowrap">Anggota</th>
                        <th class="py-3 px-4 border text-left whitespace-nowrap">Tanggal Pengajuan</th>
                        <th class="py-3 px-4 border text-left">Jenis</th>
                        <th class="py-3 px-4 border text-right">Jumlah</th>
                        <th class="py-3 px-4 border text-center whitespace-nowrap">Bln</th>
                        <th class="py-3 px-4 border text-left">Keterangan</th>
                        <th class="py-3 px-4 border text-center">Status</th>
                        <th class="py-3 px-4 border text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">
                    @foreach($dataPengajuan as $Pengajuan)
                    <tr class="hover:bg-gray-50">
                        <td class="py-2 px-4 border">{{ ($dataPengajuan->currentPage() - 1) * $dataPengajuan->perPage() + $loop->iteration }}</td>
                        <td class="py-2 px-4 border font-medium text-gray-800">{{ $Pengajuan->ajuan_id }}</td>
                        <td class="py-2 px-4 border">{{ $Pengajuan->anggota_id }}</td>
                        <td class="py-2 px-4 border whitespace-nowrap">{{ \Carbon\Carbon::parse($Pengajuan->tgl_input)->format('d M Y H:i') }}</td>
                        <td class="py-2 px-4 border">{{ $Pengajuan->jenis == '1' ? 'Biasa' : $Pengajuan->jenis }}</td>
                        <td class="py-2 px-4 border text-right whitespace-nowrap">Rp {{ number_format($Pengajuan->nominal, 0, ',', '.') }}</td>
                        <td class="py-2 px-4 border text-center">{{ $Pengajuan->lama_ags }}</td>
                        <td class="py-2 px-4 border">{{ $Pengajuan->keterangan }}</td>
                        <td class="py-2 px-4 border text-center">
                            @php 
                                $statusMap=[0=>['Menunggu Konfirmasi','bg-yellow-100 text-yellow-700 border-yellow-300'],
                                            1=>['Disetujui','bg-green-100 text-green-700 border-green-300'],
                                            2=>['Ditolak','bg-red-100 text-red-700 border-red-300'],
                                            3=>['Terlaksana','bg-indigo-100 text-indigo-700 border-indigo-300'],
                                            4=>['Batal','bg-gray-100 text-gray-700 border-gray-300']]; 
                                [$label,$cls] = $statusMap[$Pengajuan->status] ?? [$Pengajuan->status,'bg-gray-100 text-gray-700 border-gray-300'];
                            @endphp
                            <span class="px-2 py-1 text-xs rounded border {{ $cls }}">{{ $label }}</span>
                        </td>
                        <td class="py-2 px-4 border">
                            <div class="flex items-center justify-center gap-2">
                                @if((int)$Pengajuan->status === 0)
                                    <form action="{{ route('pinjaman.data_pengajuan.approve', $Pengajuan->id) }}" method="POST">
                                        @csrf
                                        <button class="px-2 py-1 text-xs rounded border bg-green-50 text-green-700 border-green-300 hover:bg-green-100">Setujui</button>
                                    </form>
                                    <form action="{{ route('pinjaman.data_pengajuan.reject', $Pengajuan->id) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="alasan" value="Ditolak oleh admin" />
                                        <button class="px-2 py-1 text-xs rounded border bg-amber-50 text-amber-700 border-amber-300 hover:bg-amber-100">Tolak</button>
                                    </form>
                                    <form action="{{ route('pinjaman.data_pengajuan.cancel', $Pengajuan->id) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan ini?')">
                                        @csrf
                                        <button class="px-2 py-1 text-xs rounded border bg-red-50 text-red-700 border-red-300 hover:bg-red-100">Batal</button>
                                    </form>
                                @endif
                                <a class="px-2 py-1 text-xs rounded border bg-purple-50 text-purple-700 border-purple-300 hover:bg-purple-100" target="_blank" href="{{ route('pinjaman.data_pengajuan.cetak', $Pengajuan->id) }}">Cetak</a>
                                <form action="{{ route('pinjaman.data_pengajuan.destroy', $Pengajuan->id) }}" method="POST" onsubmit="return confirm('Hapus pengajuan ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-2 py-1 text-xs rounded border bg-gray-50 text-gray-700 border-gray-300 hover:bg-gray-100">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-5 w-full relative px-2 py-2">
        <div class="mx-auto w-fit">{{ $dataPengajuan->links('vendor.pagination.simple-tailwind') }}</div>


        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Menampilkan {{ $dataPengajuan->firstItem() }} - {{ $dataPengajuan->lastItem() }} dari {{ $dataPengajuan->total() }} data
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