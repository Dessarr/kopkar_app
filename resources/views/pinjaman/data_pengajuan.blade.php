    @extends('layouts.app')

    @section('title', 'Data Pengajuan')
    @section('sub-title', 'Riwayat Pengajuan')

    @section('content')
    <div class="px-1 justify-center flex flex-col">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Data Pengajuan</h1>
            <div class="flex space-x-2">
                <a href="{{ route('pinjaman.data_pinjaman') }}"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-money-bill mr-2"></i>Data Pinjaman
                </a>
                <a href="{{ route('pinjaman.lunas') }}"
                    class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                    <i class="fas fa-check-circle mr-2"></i>Pinjaman Lunas
                </a>
            </div>
            <div class="flex place-content-around items-center w-1/2">
                <div class="bg-green-100 p-2 rounded-lg border-2 border-green-400 space-x-2 flex justify-around">
                    <p class="text-sm">Export</p> <img
                        src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}" class="h-auto w-[20px]">
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
                            <th class="py-2 px-3 border text-left whitespace-nowrap w-[110px]">ID Ajuan</th>
                            <th class="py-2 px-3 border text-left whitespace-nowrap w-[160px]">Anggota</th>
                            <th class="py-2 px-3 border text-left w-[110px]">Tanggal Pengajuan</th>
                            <th class="py-2 px-3 border text-left w-[80px]">Jenis</th>
                            <th class="py-2 px-3 border text-center w-[110px]">Jumlah</th>
                            <th class="py-2 px-3 border text-center whitespace-nowrap w-[46px]">Bln</th>
                            <th class="py-2 px-3 border text-left w-[180px]">Keterangan</th>
                            <th class="py-2 px-3 border text-center w-[120px]">Status</th>
                            <th class="py-2 px-3 border text-center w-[160px]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($dataPengajuan as $Pengajuan)
                        <tr class="hover:bg-gray-50">
                            <td class="py-1 px-2 border text-center align-top">
                                {{ ($dataPengajuan->currentPage() - 1) * $dataPengajuan->perPage() + $loop->iteration }}
                            </td>
                            <td class="py-1 px-2 border font-medium text-gray-800 align-top">
                                <div class="truncate" title="{{ $Pengajuan->ajuan_id }}">{{ $Pengajuan->ajuan_id }}
                                </div>
                            </td>
                            <td class="py-1 px-2 border align-top">
                                @php
                                $namaAnggota = optional($Pengajuan->anggota)->nama;
                                @endphp
                                <div class="leading-tight">
                                    <div class="truncate hover:whitespace-normal" title="{{ $namaAnggota ?? '' }}">
                                        {{ $namaAnggota ?? '-' }}
                                    </div>
                                    <div class="text-[10px] text-gray-500">({{ $Pengajuan->anggota_id }})</div>
                                </div>
                            </td>
                            <td class="py-1 px-2 border align-top">
                                @php $tgl = \Carbon\Carbon::parse($Pengajuan->tgl_input); @endphp
                                <div class="leading-tight">
                                    <div class="truncate">{{ $tgl->format('d M') }}</div>
                                    <div class="text-[10px] text-gray-500">{{ $tgl->format('Y') }}</div>
                                </div>
                            </td>
                            <td class="py-1 px-2 border align-top">
                                {{ $Pengajuan->jenis == '1' ? 'Biasa' : $Pengajuan->jenis }}
                            </td>
                            <td class="py-1 px-2 border text-right whitespace-nowrap align-top"
                                title="Rp {{ number_format($Pengajuan->nominal, 0, ',', '.') }}">
                                <div class="truncate max-w-[120px]"
                                    title="Rp {{ number_format($Pengajuan->nominal, 0, ',', '.') }}">
                                    Rp {{ number_format($Pengajuan->nominal, 0, ',', '.') }}
                                </div>
                            </td>
                            <td class="py-1 px-2 border text-center align-top">{{ $Pengajuan->lama_ags }}</td>
                            <td class="py-1 px-2 border align-top">
                                <div class="whitespace-normal break-words max-w-[180px] md:max-w-[220px]">
                                    {{ $Pengajuan->keterangan }}
                                </div>
                            </td>
                            <td class="py-1 px-2 border text-center align-top">
                                @php
                                $statusMap=[0=>['Menunggu Konfirmasi','bg-yellow-100 text-yellow-700
                                border-yellow-300'],
                                1=>['Disetujui','bg-green-100 text-green-700 border-green-300'],
                                2=>['Ditolak','bg-red-100 text-red-700 border-red-300'],
                                3=>['Terlaksana','bg-indigo-100 text-indigo-700 border-indigo-300'],
                                4=>['Batal','bg-gray-100 text-gray-700 border-gray-300']];
                                [$label,$cls] = $statusMap[$Pengajuan->status] ?? [$Pengajuan->status,'bg-gray-100
                                text-gray-700 border-gray-300'];
                                @endphp
                                <span
                                    class="px-1 py-0.5 text-[10px] rounded border truncate max-w-[110px] inline-block text-center {{ $cls }}"
                                    title="{{ $label }}">{{ $label }}</span>
                            </td>
                            <td class="py-1 px-2 border align-top">
                                <div class="grid grid-cols-3 gap-1">
                                    @if((int)$Pengajuan->status === 0)
                                    <button onclick="openApproveModal('{{ $Pengajuan->id }}')"
                                        class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-green-50 text-green-700 border-green-300 hover:bg-green-100">Setujui</button>
                                    <form action="{{ route('pinjaman.data_pengajuan.reject', $Pengajuan->id) }}"
                                        method="POST">
                                        @csrf
                                        <input type="hidden" name="alasan" value="Ditolak oleh admin" />
                                        <button
                                            class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-amber-50 text-amber-700 border-amber-300 hover:bg-amber-100">Tolak</button>
                                    </form>
                                    <form action="{{ route('pinjaman.data_pengajuan.cancel', $Pengajuan->id) }}"
                                        method="POST" onsubmit="return confirm('Batalkan pengajuan ini?')">
                                        @csrf
                                        <button
                                            class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-red-50 text-red-700 border-red-300 hover:bg-red-100">Batal</button>
                                    </form>
                                    @endif

                                    @if((int)$Pengajuan->status === 1)
                                    <form action="{{ route('pinjaman.data_pengajuan.terlaksana', $Pengajuan->id) }}"
                                        method="POST" onsubmit="return confirm('Ubah status menjadi terlaksana?')">
                                        @csrf
                                        <button
                                            class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-indigo-50 text-indigo-700 border-indigo-300 hover:bg-indigo-100">Terlaksana</button>
                                    </form>
                                    @endif
                                    <a class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-purple-50 text-purple-700 border-purple-300 hover:bg-purple-100"
                                        target="_blank"
                                        href="{{ route('pinjaman.data_pengajuan.cetak', $Pengajuan->id) }}">Cetak</a>
                                    <form action="{{ route('pinjaman.data_pengajuan.destroy', $Pengajuan->id) }}"
                                        method="POST" onsubmit="return confirm('Hapus pengajuan ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="w-full px-1.5 py-0.5 text-[10px] rounded border bg-gray-50 text-gray-700 border-gray-300 hover:bg-gray-100">Hapus</button>
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
                Menampilkan {{ $dataPengajuan->firstItem() }} - {{ $dataPengajuan->lastItem() }} dari
                {{ $dataPengajuan->total() }} data
            </div>

        </div>
    </div>

    <!-- Modal Approval -->
    <div id="approveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Setujui Pengajuan Pinjaman</h3>
                <form id="approveForm" method="POST">
                    @csrf
                    <div class="mb-4">
                        <label for="tgl_cair" class="block text-sm font-medium text-gray-700 mb-2">Tanggal Cair</label>
                        <input type="date" id="tgl_cair" name="tgl_cair" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                            value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-4">
                        <label for="alasan" class="block text-sm font-medium text-gray-700 mb-2">Alasan
                            (Opsional)</label>
                        <textarea id="alasan" name="alasan" rows="3"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                            placeholder="Alasan approval..."></textarea>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeApproveModal()"
                            class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 transition-colors duration-200">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#0f8a4a] transition-colors duration-200">
                            Setujui
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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

    <script>
function openApproveModal(pengajuanId) {
    const modal = document.getElementById('approveModal');
    const form = document.getElementById('approveForm');

    // Set action URL
    form.action = `/pinjaman/data_pengajuan/${pengajuanId}/approve`;

    // Show modal
    modal.classList.remove('hidden');
}

function closeApproveModal() {
    const modal = document.getElementById('approveModal');
    modal.classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('approveModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeApproveModal();
    }
});
    </script>
    @endsection