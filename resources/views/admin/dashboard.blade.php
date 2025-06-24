@extends('layouts.app')

@section('title', 'Admin Dashboard')

@section('sidebar')
    <a href="#" class="flex items-center p-3 rounded-lg sidebar-item active">
        <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
        Home
    </a>
    {{-- Tambahkan menu admin lainnya di sini --}}
@endsection

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Card Pinjaman Kredit -->
        <div class="bg-white p-6 rounded-lg shadow-md col-span-1 md:col-span-2 lg:col-span-1">
            <h3 class="font-bold text-gray-800 mb-4">Pinjaman Kredit</h3>
            <canvas id="pinjamanChart" class="mx-auto" style="max-width: 150px; max-height: 150px;"></canvas>
            <ul class="mt-4 space-y-2 text-sm text-gray-600">
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>Tagihan belum lunas: <span class="font-bold ml-auto">282,902,700</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>Tagihan belum lunas bulan lalu: <span class="font-bold ml-auto">0</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-yellow-500 mr-2"></span>Pinjaman bulan ini: <span class="font-bold ml-auto">0</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>Pembayaran bulan ini: <span class="font-bold ml-auto">0</span></li>
            </ul>
        </div>

        <!-- Card Kas -->
        <div class="bg-white p-6 rounded-lg shadow-md col-span-1 md:col-span-2 lg:col-span-1">
            <h3 class="font-bold text-gray-800 mb-4">Kas</h3>
            <canvas id="kasChart" class="mx-auto" style="max-width: 150px; max-height: 150px;"></canvas>
             <ul class="mt-4 space-y-2 text-sm text-gray-600">
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-green-500 mr-2"></span>Total saldo: <span class="font-bold ml-auto">1,751,843,000</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>Penerimaan bulan ini: <span class="font-bold ml-auto">0</span></li>
                <li class="flex items-center"><span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span>Pengeluaran bulan ini: <span class="font-bold ml-auto">0</span></li>
            </ul>
        </div>

        <!-- Card Jatuh Tempo -->
        <div class="bg-white p-6 rounded-lg shadow-md col-span-1 md:col-span-2 lg:col-span-2">
            <h3 class="font-bold text-gray-800 mb-4">Jatuh Tempo</h3>
            {{-- Data jatuh tempo bisa di-loop di sini --}}
        </div>
    </div>
    
<script>
    // Data dummy untuk chart
    const pinjamanData = {
        datasets: [{ data: [70, 30], backgroundColor: ['#1abc9c', '#e2e8f0'], borderWidth: 0, cutout: '70%' }]
    };
    const kasData = {
        datasets: [{ data: [85, 15], backgroundColor: ['#3498db', '#e2e8f0'], borderWidth: 0, cutout: '70%' }]
    };

    // Render Chart
    new Chart(document.getElementById('pinjamanChart'), { type: 'doughnut', data: pinjamanData });
    new Chart(document.getElementById('kasChart'), { type: 'doughnut', data: kasData });
</script>
@endsection 