@extends('layouts.app')

@section('title', 'Member Dashboard')

@section('sidebar')
    <a href="#" class="flex items-center p-3 rounded-lg sidebar-item active">
        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        Home
    </a>
    <a href="{{ route('anggota.bayar.toserda') }}" class="flex items-center p-3 rounded-lg sidebar-item">
        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
        Bayar Toserda/Lain-lain
    </a>
    <a href="{{ route('member.logout') }}" onclick="event.preventDefault(); document.getElementById('logout-form').submit();" class="flex items-center p-3 rounded-lg sidebar-item">
        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
        Logout
    </a>
    <form id="logout-form" action="{{ route('member.logout') }}" method="POST" class="hidden">
        @csrf
    </form>
@endsection

@section('content')
    <div class="bg-white p-8 rounded-lg shadow-md mb-6">
        <div class="flex items-center">
            <div>
                <h2 class="text-3xl font-bold text-gray-800">{{ $anggota->nama_anggota ?? 'Member' }}</h2>
                <p class="text-gray-600">Anggota</p>
                <div class="mt-4 text-gray-700">
                    <p><strong>No. KTP:</strong> {{ $anggota->no_ktp ?? '-' }}</p>
                    <p><strong>Email:</strong> {{ $anggota->email ?? '-' }}</p>
                    <p><strong>Alamat:</strong> {{ $anggota->alamat ?? '-' }}</p>
                </div>
            </div>
            <a href="#" class="ml-auto bg-[#1abc9c] text-white font-bold py-2 px-6 rounded-lg hover:bg-[#16a085] transition-colors">
                Edit Info
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Card Pinjaman Kredit -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="font-bold text-gray-800 mb-4">Pinjaman Kredit</h3>
            <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>Tagihan belum lunas: <span class="font-bold ml-auto">{{ number_format($anggota->total_pinjaman ?? 0, 0, ',', '.') }}</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>Tagihan belum lunas bulan lalu: <span class="font-bold ml-auto">{{ number_format($anggota->tagihan_bulan_lalu ?? 0, 0, ',', '.') }}</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>Pembayaran bulan ini: <span class="font-bold ml-auto">{{ number_format($anggota->pembayaran_bulan_ini ?? 0, 0, ',', '.') }}</span></li>
            </ul>
        </div>

        <!-- Card Simpanan -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="font-bold text-gray-800 mb-4">Simpanan</h3>
             <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>Total simpanan: <span class="font-bold ml-auto">{{ number_format($anggota->total_simpanan ?? 0, 0, ',', '.') }}</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>Simpanan wajib: <span class="font-bold ml-auto">{{ number_format($anggota->simpanan_wajib ?? 0, 0, ',', '.') }}</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>Simpanan sukarela: <span class="font-bold ml-auto">{{ number_format($anggota->simpanan_sukarela ?? 0, 0, ',', '.') }}</span></li>
            </ul>
        </div>
        
        <!-- Card Transaksi -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="font-bold text-gray-800 mb-4">Transaksi Terakhir</h3>
             <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-center">
                    <span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>
                    <a href="{{ route('anggota.bayar.toserda') }}" class="text-blue-600 hover:underline">Toserda:</a>
                    <span class="font-bold ml-auto">{{ number_format($anggota->transaksi_toserda ?? 0, 0, ',', '.') }}</span>
                </li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>Angkutan: <span class="font-bold ml-auto">{{ number_format($anggota->transaksi_angkutan ?? 0, 0, ',', '.') }}</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>Lainnya: <span class="font-bold ml-auto">{{ number_format($anggota->transaksi_lainnya ?? 0, 0, ',', '.') }}</span></li>
            </ul>
            <div class="mt-4 text-center">
                <a href="{{ route('anggota.bayar.toserda') }}" class="text-blue-600 hover:underline text-sm">Bayar Tagihan Toserda</a>
            </div>
        </div>
    </div>
@endsection 