@extends('layouts.app')

@section('title', 'Detail Pinjaman')
@section('sub-title', 'Detail Data Pinjaman')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Pinjaman</h1>
        <div class="flex space-x-3">
            <a href="{{ route('pinjaman.data_pinjaman') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                ← Kembali
            </a>
        </div>
    </div>

    @if (session('success'))
    <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif
    @if (session('error'))
    <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    <!-- Informasi Pinjaman -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Informasi Pinjaman</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Kode Pinjam</label>
                <p class="text-lg font-semibold text-gray-900">{{ $pinjaman->id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Anggota</label>
                <p class="text-lg font-semibold text-gray-900">{{ $pinjaman->anggota->nama ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">ID Anggota</label>
                <p class="text-lg font-semibold text-gray-900">{{ $pinjaman->anggota_id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">No KTP</label>
                <p class="text-lg font-semibold text-gray-900">{{ $pinjaman->no_ktp ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Pinjam</label>
                <p class="text-lg font-semibold text-gray-900">
                    {{ \Carbon\Carbon::parse($pinjaman->tgl_pinjam)->format('d/m/Y') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jenis Pinjaman</label>
                <p class="text-lg font-semibold text-gray-900">
                    {{ $pinjaman->jenis_pinjaman == '1' ? 'Biasa' : 'Barang' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Lama Angsuran</label>
                <p class="text-lg font-semibold text-gray-900">{{ $pinjaman->lama_angsuran }} Bulan</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status</label>
                <p class="text-lg font-semibold text-gray-900">{{ $pinjaman->status == '1' ? 'Aktif' : 'Terlaksana' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Detail Keuangan -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Detail Keuangan</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Jumlah Pinjaman</label>
                <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($pinjaman->jumlah, 0, ',', '.') }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Bunga</label>
                <p class="text-lg font-semibold text-gray-900">{{ $pinjaman->bunga }}%</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Bunga (Rp)</label>
                <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($pinjaman->bunga_rp, 0, ',', '.') }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Biaya Admin</label>
                <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($pinjaman->biaya_adm, 0, ',', '.') }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Angsuran per Bulan</label>
                <p class="text-lg font-semibold text-gray-900">Rp
                    {{ number_format($pinjaman->jumlah_angsuran, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Total Tagihan</label>
                <p class="text-lg font-semibold text-gray-900">Rp
                    {{ number_format($pinjaman->jumlah + $pinjaman->biaya_adm, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Status Lunas</label>
                <p class="text-lg font-semibold text-gray-900">{{ $pinjaman->lunas }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Kas Sumber</label>
                <p class="text-lg font-semibold text-gray-900">{{ $pinjaman->kas_id }}</p>
            </div>
        </div>
    </div>

    <!-- Rangkuman Angsuran -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Rangkuman Angsuran</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-blue-700">Total Angsuran</label>
                <p class="text-2xl font-bold text-blue-900">{{ $pinjaman->lama_angsuran }} Bulan</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-green-700">Sudah Dibayar</label>
                <p class="text-2xl font-bold text-green-900">{{ $pinjaman->detail_angsuran->count() }}x</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-yellow-700">Sisa Angsuran</label>
                <p class="text-2xl font-bold text-yellow-900">
                    {{ $pinjaman->lama_angsuran - $pinjaman->detail_angsuran->count() }} Bulan</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-purple-700">Total Dibayar</label>
                <p class="text-2xl font-bold text-purple-900">Rp
                    {{ number_format($pinjaman->detail_angsuran->sum('jumlah_bayar'), 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Riwayat Angsuran -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Riwayat Pembayaran Angsuran</h2>
            @if($pinjaman->lunas === 'Belum')
            <a href="{{ route('pinjaman.data_angsuran.show', $pinjaman->id) }}"
                class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                + Bayar Angsuran
            </a>
            @endif
        </div>

        @if($pinjaman->detail_angsuran->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Angsuran Ke</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Tanggal Bayar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                            Jumlah Bayar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bunga
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Denda
                        </th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya
                            Adm</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pinjaman->detail_angsuran as $angsuran)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            {{ $angsuran->angsuran_ke }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ \Carbon\Carbon::parse($angsuran->tgl_bayar)->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp
                            {{ number_format($angsuran->jumlah_bayar, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp
                            {{ number_format($angsuran->bunga, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp
                            {{ number_format($angsuran->denda_rp, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp
                            {{ number_format($angsuran->biaya_adm, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $angsuran->user_name }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8">
            <p class="text-gray-500">Belum ada pembayaran angsuran</p>
        </div>
        @endif
    </div>

    <!-- Informasi Tambahan -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h2 class="text-lg font-semibold mb-4">Informasi Tambahan</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Keterangan</label>
                <p class="text-sm text-gray-900">{{ $pinjaman->keterangan ?: '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">User Name</label>
                <p class="text-sm text-gray-900">{{ $pinjaman->user_name }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Update Data</label>
                <p class="text-sm text-gray-900">
                    {{ $pinjaman->update_data ? \Carbon\Carbon::parse($pinjaman->update_data)->format('d/m/Y H:i') : '-' }}
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">ID Cabang</label>
                <p class="text-sm text-gray-900">{{ $pinjaman->id_cabang ?: '-' }}</p>
            </div>
        </div>
    </div>
</div>
@endsection