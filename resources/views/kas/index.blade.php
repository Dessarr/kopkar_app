@extends('layouts.app')

@section('title', 'Manajemen Kas')

@section('content')
<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Manajemen Kas</h1>
        <a href="{{ route('kas.create') }}" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
            Tambah Transaksi
        </a>
    </div>

    <!-- Ringkasan Kas -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        @foreach($kas as $k)
        <div class="bg-white rounded-lg shadow p-4">
            <h3 class="font-semibold text-lg mb-2">{{ $k->nama }}</h3>
            <p class="text-gray-600">{{ ucfirst($k->tipe) }}</p>
            <p class="text-xl font-bold mt-2">Rp {{ number_format($k->getSaldo(), 2, ',', '.') }}</p>
        </div>
        @endforeach
    </div>

    <!-- Tabel Transaksi -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Riwayat Transaksi</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left">Tanggal</th>
                        <th class="px-4 py-2 text-left">Kas</th>
                        <th class="px-4 py-2 text-left">Jenis</th>
                        <th class="px-4 py-2 text-left">Jumlah</th>
                        <th class="px-4 py-2 text-left">Keterangan</th>
                        <th class="px-4 py-2 text-left">Oleh</th>
                        <th class="px-4 py-2 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
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
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t">
            {{ $transaksi->links() }}
        </div>
    </div>
</div>
@endsection 