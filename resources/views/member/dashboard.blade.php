@extends('layouts.app')

@section('title', 'Member Dashboard')

@section('sidebar')
<a href="#" class="flex items-center p-3 rounded-lg sidebar-item active">
    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
        </path>
    </svg>
    Home
</a>
<a href="{{ route('anggota.bayar.toserda') }}" class="flex items-center p-3 rounded-lg sidebar-item">
    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path>
    </svg>
    Bayar Toserda/Lain-lain
</a>
<a href="{{ route('member.logout') }}"
    onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
    class="flex items-center p-3 rounded-lg sidebar-item">
    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
    </svg>
    Logout
</a>
<form id="logout-form" action="{{ route('member.logout') }}" method="POST" class="hidden">
    @csrf
</form>
@endsection

@section('content')
<div class="bg-white p-8 rounded-lg shadow-md mb-6 flex flex-col items-center">
    <div class="flex flex-col items-center mb-6">
        @if($anggota->file_pic && Storage::disk('public')->exists('anggota/' . $anggota->file_pic))
        <img src="{{ asset('storage/anggota/' . $anggota->file_pic) }}" alt="Foto {{ $anggota->nama }}"
            class="w-32 h-32 object-cover rounded-full border-4 border-green-500 shadow-lg mb-4">
        @else
        <div
            class="w-32 h-32 rounded-full border-4 border-green-500 flex items-center justify-center bg-gray-100 shadow-lg mb-4">
            <svg class="w-14 h-14 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
            </svg>
        </div>
        @endif
        <h2 class="text-3xl font-extrabold text-gray-800 mb-1 text-center">{{ $anggota->nama ?? 'Member' }}</h2>
        <p class="text-green-700 font-semibold mb-2 text-center">Anggota</p>
    </div>
    <div class="w-full grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <div
            class="bg-gray-50 rounded-lg shadow p-3 text-xs backdrop-blur-sm transition-all duration-300 border border-gray-200 hover:backdrop-blur-none hover:bg-white hover:border-green-400">
            <h4 class="text-green-700 font-semibold text-xs mb-1">Data Pribadi</h4>
            <div class="flex flex-col gap-y-1">
                <p class="mb-0"><span class="font-semibold">ID Koperasi:</span> {{ $anggota->no_ktp ?? '-' }}</p>
                <p class="mb-0"><span class="font-semibold">Tempat, Tgl Lahir:</span> {{ $anggota->tmp_lahir ?? '-' }},
                    {{ $anggota->tgl_lahir ?? '-' }}</p>
                <p class="mb-0"><span class="font-semibold">Jenis Kelamin:</span>
                    {{ $anggota->jk == 'L' ? 'Laki-laki' : ($anggota->jk == 'P' ? 'Perempuan' : '-') }}</p>
                <p class="mb-0"><span class="font-semibold">Status:</span> {{ $anggota->status ?? '-' }}</p>
                <p class="mb-0"><span class="font-semibold">Agama:</span> {{ $anggota->agama ?? '-' }}</p>
            </div>
        </div>
        <div
            class="bg-gray-50 rounded-lg shadow p-3 text-xs backdrop-blur-sm transition-all duration-300 border border-gray-200 hover:backdrop-blur-none hover:bg-white hover:border-green-400">
            <h4 class="text-green-700 font-semibold text-xs mb-1">Kontak & Pekerjaan</h4>
            <div class="flex flex-col gap-y-1">
                <p class="mb-0"><span class="font-semibold">Alamat:</span> {{ $anggota->alamat ?? '-' }}</p>
                <p class="mb-0"><span class="font-semibold">Kota:</span> {{ $anggota->kota ?? '-' }}</p>
                <p class="mb-0"><span class="font-semibold">No. Telp:</span> {{ $anggota->notelp ?? '-' }}</p>
                <p class="mb-0"><span class="font-semibold">Departemen:</span> {{ $anggota->departement ?? '-' }}</p>
                <p class="mb-0"><span class="font-semibold">Jabatan:</span> {{ $anggota->jabatan_id ?? '-' }}</p>
            </div>
        </div>
        <div
            class="bg-gray-50 rounded-lg shadow p-3 text-xs backdrop-blur-sm transition-all duration-300 border border-gray-200 hover:backdrop-blur-none hover:bg-white hover:border-green-400">
            <h4 class="text-green-700 font-semibold text-xs mb-1">Bank & Rekening</h4>
            <div class="flex flex-col gap-y-1">
                <p class="mb-0"><span class="font-semibold">Bank:</span> {{ $anggota->bank ?? '-' }}</p>
                <p class="mb-0"><span class="font-semibold">No. Rekening:</span> {{ $anggota->no_rekening ?? '-' }}</p>
                <p class="mb-0"><span class="font-semibold">Nama Pemilik Rekening:</span>
                    {{ $anggota->nama_pemilik_rekening ?? '-' }}</p>
            </div>
        </div>
        <div
            class="bg-gray-50 rounded-lg shadow p-3 text-xs backdrop-blur-sm transition-all duration-300 border border-gray-200 hover:backdrop-blur-none hover:bg-white hover:border-green-400">
            <h4 class="text-green-700 font-semibold text-xs mb-1">Status Keanggotaan</h4>
            <div class="flex flex-col gap-y-1">
                <p class="mb-0"><span class="font-semibold">Tanggal Daftar:</span> {{ $anggota->tgl_daftar ?? '-' }}</p>
                <p class="mb-0"><span class="font-semibold">Status Aktif:</span>
                    {{ $anggota->aktif == 'Y' ? 'Aktif' : 'Tidak Aktif' }}</p>
            </div>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <!-- Card Pinjaman Kredit -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="font-bold text-gray-800 mb-4">Pinjaman Kredit</h3>
        <ul class="space-y-2 text-sm text-gray-600">
            <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>Tagihan belum
                lunas: <span
                    class="font-bold ml-auto">{{ number_format($anggota->total_pinjaman ?? 0, 0, ',', '.') }}</span>
            </li>
            <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>Tagihan belum lunas
                bulan lalu: <span
                    class="font-bold ml-auto">{{ number_format($anggota->tagihan_bulan_lalu ?? 0, 0, ',', '.') }}</span>
            </li>
            <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>Pembayaran bulan
                ini: <span
                    class="font-bold ml-auto">{{ number_format($anggota->pembayaran_bulan_ini ?? 0, 0, ',', '.') }}</span>
            </li>
        </ul>
    </div>

    <!-- Card Simpanan -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="font-bold text-gray-800 mb-4">Simpanan</h3>
        <ul class="space-y-2 text-sm text-gray-600">
            <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>Total simpanan:
                <span class="font-bold ml-auto">{{ number_format($anggota->total_simpanan ?? 0, 0, ',', '.') }}</span>
            </li>
            <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>Simpanan wajib:
                <span class="font-bold ml-auto">{{ number_format($anggota->simpanan_wajib ?? 0, 0, ',', '.') }}</span>
            </li>
            <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>Simpanan sukarela:
                <span
                    class="font-bold ml-auto">{{ number_format($anggota->simpanan_sukarela ?? 0, 0, ',', '.') }}</span>
            </li>
        </ul>
    </div>

    <!-- Card Transaksi -->
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h3 class="font-bold text-gray-800 mb-4">Transaksi Terakhir</h3>
        <ul class="space-y-2 text-sm text-gray-600">
            <li class="flex items-center">
                <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                <a href="{{ route('anggota.bayar.toserda') }}" class="text-blue-600 hover:underline">Toserda:</a>
                <span
                    class="font-bold ml-auto">{{ number_format($anggota->transaksi_toserda ?? 0, 0, ',', '.') }}</span>
            </li>
            <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>Angkutan: <span
                    class="font-bold ml-auto">{{ number_format($anggota->transaksi_angkutan ?? 0, 0, ',', '.') }}</span>
            </li>
            <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>Lainnya: <span
                    class="font-bold ml-auto">{{ number_format($anggota->transaksi_lainnya ?? 0, 0, ',', '.') }}</span>
            </li>
        </ul>
        <div class="mt-4 text-center">
            <a href="{{ route('anggota.bayar.toserda') }}" class="text-blue-600 hover:underline text-sm">Bayar Tagihan
                Toserda</a>
        </div>
    </div>
</div>
@endsection