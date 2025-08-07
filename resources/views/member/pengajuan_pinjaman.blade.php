@extends('layouts.member')

@section('title', 'Data Pengajuan Pinjaman')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
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
                    <tr>
                        <td class="py-3 px-4 text-center text-sm text-gray-500 border" colspan="9">Tidak ada data</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4 flex items-center justify-between">
            <div class="text-sm text-gray-500">
                1 - 0 dari total 0 data
            </div>
            <div class="flex items-center space-x-2">
                <select class="text-sm border rounded px-2 py-1">
                    <option>10</option>
                    <option>25</option>
                    <option>50</option>
                    <option>100</option>
                </select>
                <span class="text-sm text-gray-500">data per halaman</span>
            </div>
        </div>
    </div>
</div>
@endsection