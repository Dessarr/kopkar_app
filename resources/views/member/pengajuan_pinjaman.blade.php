@extends('layouts.member')

@section('title', 'Data Pengajuan Pinjaman')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
        @endif
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Data Pengajuan Pinjaman</h1>
            <a href="{{ route('member.tambah.pengajuan.pinjaman') }}"
                class="bg-[#14AE5C] hover:bg-[#14AE5C]/80 text-white px-4 py-2 rounded-lg">
                + Pengajuan Baru
            </a>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border">
                <thead class="bg-white">
                    <tr>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Tanggal</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Jenis</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Jumlah</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Jml Angsur</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Keterangan</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Alasan</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Tanggal Update</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Status</th>
                        <th class="py-3 px-4 text-left text-sm font-semibold text-gray-600 border">Opsi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($dataPengajuan as $pengajuan)
                    <tr>
                        <td class="py-3 px-4 text-sm text-gray-700 border">{{ \Carbon\Carbon::parse($pengajuan->tgl_input)->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">{{ $pengajuan->jenis == '1' ? 'Biasa' : $pengajuan->jenis }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">Rp {{ number_format($pengajuan->nominal,0,',','.') }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">{{ $pengajuan->lama_ags }} bln</td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">{{ $pengajuan->keterangan }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">{{ $pengajuan->alasan }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">{{ $pengajuan->tgl_update ? \Carbon\Carbon::parse($pengajuan->tgl_update)->format('d/m/Y H:i') : '-' }}</td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">
                            @php $statusMap = [0=>'Pending',1=>'Disetujui',2=>'Ditolak',3=>'Terlaksana',4=>'Batal']; @endphp
                            <span>{{ $statusMap[$pengajuan->status] ?? $pengajuan->status }}</span>
                        </td>
                        <td class="py-3 px-4 text-sm text-gray-700 border">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('member.pengajuan.pinjaman.show', $pengajuan->id) }}" class="px-2 py-1 text-xs bg-blue-50 text-blue-700 border border-blue-200 rounded">Detail</a>
                                @if((int)$pengajuan->status === 0)
                                    <form action="{{ route('member.pengajuan.pinjaman.cancel', $pengajuan->id) }}" method="POST" onsubmit="return confirm('Batalkan pengajuan ini?')">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 text-xs bg-red-50 text-red-700 border border-red-200 rounded">Batal</button>
                                    </form>
                                @endif
                                @if((int)$pengajuan->status === 1)
                                    <a href="{{ route('member.pengajuan.pinjaman.cetak', $pengajuan->id) }}" target="_blank" class="px-2 py-1 text-xs bg-purple-50 text-purple-700 border border-purple-200 rounded">Cetak</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td class="py-3 px-4 text-center text-sm text-gray-500 border" colspan="9">Tidak ada data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">{{ $dataPengajuan->firstItem() }} - {{ $dataPengajuan->lastItem() }} dari total {{ $dataPengajuan->total() }} data</div>
            <div class="flex items-center space-x-2">
                {{ $dataPengajuan->links('vendor.pagination.simple-tailwind') }}
            </div>
        </div>
    </div>
</div>
@endsection