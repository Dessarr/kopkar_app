@extends('layouts.app')

@section('title', 'Pinjaman Lunas')
@section('sub-title', 'Data Pinjaman yang Sudah Lunas')

@section('content')
<div class="bg-white rounded-lg shadow-md p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Data Pinjaman Lunas</h2>
        <div class="flex space-x-2">
            <a href="{{ route('pinjaman.data_pinjaman') }}"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg transition-colors duration-200">
                <i class="fas fa-arrow-left mr-2"></i>Kembali ke Data Pinjaman
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
        {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
        {{ session('error') }}
    </div>
    @endif

    <!-- Filter dan Pencarian -->
    <div class="mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <div class="flex-1 min-w-64">
                <label for="search" class="block text-sm font-medium text-gray-700 mb-2">Cari Anggota</label>
                <input type="text" id="search" placeholder="Cari berdasarkan nama atau NIK..."
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent">
            </div>
            <div>
                <button type="button" onclick="filterData()"
                    class="bg-[#14AE5C] hover:bg-[#0f8a4a] text-white px-6 py-2 rounded-md transition-colors duration-200">
                    <i class="fas fa-search mr-2"></i>Cari
                </button>
            </div>
        </div>
    </div>

    <!-- Tabel Data -->
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white border border-gray-200 rounded-lg">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                        No
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                        ID Transaksi
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                        Nama Anggota
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                        NIK
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                        Tanggal Pinjam
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                        Nominal
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                        Lama Angsuran
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                        Status
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider border-b">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                @forelse($dataPinjamanLunas as $index => $pinjaman)
                <tr class="hover:bg-gray-50">
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                        {{ $dataPinjamanLunas->firstItem() + $index }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 border-b">
                        {{ $pinjaman->id }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                        {{ $pinjaman->anggota->nama ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                        {{ $pinjaman->no_ktp ?? 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                        {{ $pinjaman->tgl_pinjam ? \Carbon\Carbon::parse($pinjaman->tgl_pinjam)->format('d/m/Y') : 'N/A' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                        Rp {{ number_format($pinjaman->jumlah, 0, ',', '.') }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 border-b">
                        {{ $pinjaman->lama_angsuran }} bulan
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap border-b">
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <i class="fas fa-check-circle mr-1"></i>Lunas
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium border-b">
                        <div class="flex space-x-2">
                            <a href="{{ route('pinjaman.data_pinjaman.show', $pinjaman->id) }}"
                                class="text-blue-600 hover:text-blue-900 transition-colors duration-200">
                                <i class="fas fa-eye"></i> Detail
                            </a>
                            <a href="{{ route('pinjaman.data_angsuran.show', $pinjaman->id) }}"
                                class="text-green-600 hover:text-green-900 transition-colors duration-200">
                                <i class="fas fa-list"></i> Angsuran
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-gray-500 border-b">
                        <div class="flex flex-col items-center py-8">
                            <i class="fas fa-inbox text-4xl text-gray-300 mb-2"></i>
                            <p class="text-lg font-medium text-gray-400">Tidak ada data pinjaman lunas</p>
                            <p class="text-sm text-gray-300">Semua pinjaman masih dalam proses pembayaran</p>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($dataPinjamanLunas->hasPages())
    <div class="mt-6">
        {{ $dataPinjamanLunas->links('vendor.pagination.simple') }}
    </div>
    @endif

</div>

<script>
function filterData() {
    const searchTerm = document.getElementById('search').value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const nama = row.cells[2].textContent.toLowerCase();
        const nik = row.cells[3].textContent.toLowerCase();
        const id = row.cells[1].textContent.toLowerCase();

        if (nama.includes(searchTerm) || nik.includes(searchTerm) || id.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
}

// Auto-filter saat mengetik
document.getElementById('search').addEventListener('input', filterData);
</script>
@endsection