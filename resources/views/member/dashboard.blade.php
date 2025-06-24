@extends('layouts.app')

@section('title', 'Member Dashboard')

@section('sidebar')
    <a href="#" class="flex items-center p-3 rounded-lg sidebar-item active">
        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        Home
    </a>
    {{-- Tambahkan menu member lainnya di sini --}}
@endsection

@section('content')
    <div class="bg-white p-8 rounded-lg shadow-md mb-6">
        <div class="flex items-center">
            <img src="https://i.pravatar.cc/150?u={{ auth()->id() }}" alt="Profile" class="w-24 h-24 rounded-full mr-8">
            <div>
                <h2 class="text-3xl font-bold text-gray-800"></h2>
                <p class="text-gray-600">Anggota</p>
                <div class="mt-4 text-gray-700">
                    <p><strong>Email:</strong> </p>
                    {{-- Tambahkan data profil lainnya dari relasi akun_member -> anggota --}}
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
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>Tagihan belum lunas: <span class="font-bold ml-auto">282,902,700</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>Tagihan belum lunas bulan lalu: <span class="font-bold ml-auto">0</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>Pembayaran bulan ini: <span class="font-bold ml-auto">0</span></li>
            </ul>
        </div>

        <!-- Card Kas -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="font-bold text-gray-800 mb-4">Kas</h3>
             <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>Total saldo: <span class="font-bold ml-auto">1,751,843,000</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>Penerimaan bulan ini: <span class="font-bold ml-auto">0</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>Pengeluaran bulan ini: <span class="font-bold ml-auto">0</span></li>
            </ul>
        </div>
        
        <!-- Placeholder Card -->
        <div class="bg-white p-6 rounded-lg shadow-md">
            <h3 class="font-bold text-gray-800 mb-4">Kas</h3>
             <ul class="space-y-2 text-sm text-gray-600">
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>Total saldo: <span class="font-bold ml-auto">1,751,843,000</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>Penerimaan bulan ini: <span class="font-bold ml-auto">0</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>Pengeluaran bulan ini: <span class="font-bold ml-auto">0</span></li>
            </ul>
        </div>
    </div>
@endsection 