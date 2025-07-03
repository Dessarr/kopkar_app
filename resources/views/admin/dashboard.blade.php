@extends('layouts.app')

@section('title', 'Admin Dashboard')
@section('sub-title', 'Menu Utama')

@section('sidebar')
<a href="#" class="flex items-center p-3 rounded-lg sidebar-item active">
    <svg class="w-6 h-6 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6">
        </path>
    </svg>
    Home
</a>
{{-- Tambahkan menu admin lainnya di sini --}}
@endsection

@section('content')
<div class="w-full rows-2 flex flex-col lg:flex-row gap-4">
    <main class="w-full grid grid-cols-1 lg:grid-cols-3 gap-7">
        <!-- Card Pinjaman Kredit -->
        <div class="bg-white shadow-md rounded-lg flex flex-col">
            <div class="ww-full p-2 mt-2 mb-3 display flex-none flex flex-row place-content-between  rounded-lg">
                <h3 class="font-bold rounded-lg pt-1 align-center px-2 ">Pinjaman Kredit</h3>
            </div>
            <div
                class="w-full h-1/2 space-y-4 flex-1 overflow-x-auto overflow-y-hidden bg-white align-center justify-center ">
                <canvas id="pinjamanChart" class="mx-auto w-full" style="max-height: 130px;"></canvas>
                <ul class="space-y-2 text-[11px] text-gray-600 px-4 w-full max-w-md mx-auto">
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-green-500"></span>
                            <span>Tagihan belum lunas:</span>
                        </div>
                        <span class="font-bold text-right">282,902,700</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-blue-500"></span>
                            <span>Tagihan belum lunas bulan lalu:</span>
                        </div>
                        <span class="font-bold text-right">382,902,700</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-yellow-500"></span>
                            <span>Pinjaman bulan ini:</span>
                        </div>
                        <span class="font-bold text-right">252,602,700</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-red-500"></span>
                            <span>Pembayaran bulan ini:</span>
                        </div>
                        <span class="font-bold text-right">100,000,000</span>
                    </li>
                </ul>
            </div>
            <div class=" p-5 justify-center align-center ">
                <div
                    class="group relative bg-green-500 w-full rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out hover:bg-green-600 overflow-hidden">
                    <a href="#"
                        class="group relative bg-green-500 w-full px-4 py-1 rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out group-hover:bg-green-600 overflow-hidden">
                        <span>More Info</span>
                        <span
                            class="transform transition-all duration-500 ease-in-out group-hover:translate-x-8 opacity-100 group-hover:opacity-0">>
                        </span>
                    </a>
                </div>

            </div>
        </div>

        <!-- Card Kas -->
        <div class="bg-white shadow-md rounded-lg flex flex-col">
            <div class="w-full p-2 mt-2 mb-3 flex-none flex flex-row place-content-between rounded-lg">
                <h3 class="font-bold pt-1 px-2">Kas</h3>
            </div>
            <div class="w-full h-1/2 space-y-4 flex-1 overflow-x-auto bg-white justify-center">
                <canvas id="kasChart" class="mx-auto w-full" style=" max-height: 130px;"></canvas>

                <ul class="space-y-2 text-[11px] text-gray-600 px-4 w-full max-w-md mx-auto">
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-green-500"></span>
                            <span>Total saldo:</span>
                        </div>
                        <span class="font-bold text-right">1,751,843,000</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-blue-500"></span>
                            <span>Penerimaan bulan ini:</span>
                        </div>
                        <span class="font-bold text-right">352,900,000</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-red-500"></span>
                            <span>Pengeluaran bulan ini:</span>
                        </div>
                        <span class="font-bold text-right">127,500,000</span>
                    </li>
                </ul>
            </div>
            <div class="p-5 justify-center">
                <div
                    class="group relative bg-green-500 w-full  rounded-full text-white flex flex-row place-content-between overflow-hidden hover:bg-green-600 transition-all duration-300 ease-in-out">
                    <a href="{{ route('kas.pemasukan') }}"
                        class="group relative bg-green-500 w-full px-4 py-1 rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out group-hover:bg-green-600 overflow-hidden">
                        <span>More Info</span>
                        <span
                            class="transform transition-all duration-500 ease-in-out group-hover:translate-x-8 opacity-100 group-hover:opacity-0">&gt;
                        </span>
                    </a>
                </div>

            </div>
        </div>


        <!-- Card Jatuh Tempo -->
        <div class="bg-white shadow-md rounded-lg flex flex-col ">

            <div class="w-full p-2 mt-2 mb-3 display flex-none flex flex-row place-content-between  rounded-lg">
                <h5 class="font-bold rounded-lg pt-1 align-center px-2">Jatuh Tempo</h5>
                <div
                    class="bg-gray-100 rounded-lg p-1 w-[20%] rounder-lg display flex flex-row gap-2 items-center justify-center">
                    <span class="text-gray-500">2</span>
                    <i class="fa-solid fa-triangle-exclamation" style="color:red;"></i>

                </div>
            </div>
            {{-- Data jatuh tempo bisa di-loop di sini --}}
            <div class="w-full px-3 h-1/2 flex-1 overflow-x-auto bg-white align-center justify-center  ">

                <div class="flex flex-row place-content-around  justify-center align-center items-center">
                    <div class=" flex flex-col justify-center align-center w-[20%]">
                        <div class=" text-center text-[34px]">O</div>
                    </div>

                    <div class=" flex flex-col align-center justify-center w-[45%] ">
                        <div class="text-[14px]">John</div>
                        <div class="text-[12px]  text-gray-400 ">12/24/2024</div>
                    </div>
                    <div
                        class=" w-[33%] text-[12px] flex flex-col justify-center text-center bg-gray-100 h-3/4 rounded-lg py-0.5">
                        -20.001,000
                    </div>
                </div>
                <div class="flex flex-row place-content-around justify-center align-center items-center">
                    <div class=" flex flex-col justify-center align-center w-[20%]">
                        <div class=" text-center text-[34px]">O</div>
                    </div>

                    <div class=" flex flex-col align-center justify-center w-[45%] ">
                        <div class="text-[14px]">John</div>
                        <div class="text-[12px] text-gray-400">12/24/2024</div>
                    </div>
                    <div
                        class=" w-[33%] text-[12px] flex flex-col justify-center text-center bg-gray-100 h-3/4 rounded-lg py-0.5">
                        -20.001,000
                    </div>
                </div>
            </div>

            <div class=" p-5 justify-center align-center ">
                <div
                    class="group relative bg-green-500 w-full rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out hover:bg-green-600 overflow-hidden">
                    <a href="#"
                        class="group relative bg-green-500 w-full px-4 py-1 rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out group-hover:bg-green-600 overflow-hidden">
                        <span>More Info</span>
                        <span
                            class="transform transition-all duration-500 ease-in-out group-hover:translate-x-8 opacity-100 group-hover:opacity-0">>
                        </span>
                    </a>
                </div>

            </div>
        </div>

        <!-- Card Data Pinjaman -->
        <div class="bg-white shadow-md rounded-lg flex flex-col">
            <div class="w-full p-2 mt-2 mb-3 flex-none flex flex-row place-content-between rounded-lg">
                <h3 class="font-bold pt-1 px-2">Data Pinjaman</h3>
            </div>
            <div class="w-full h-1/2 space-y-4 flex-1 overflow-x-auto overflow-y-hidden bg-white justify-center">
                <canvas id="pinjamanCharts" class="mx-auto w-full" style="max-height: 130px;"></canvas>
                <ul class="space-y-2 text-[11px] text-gray-600 px-4 w-full max-w-md mx-auto">
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-green-500"></span>
                            <span>Peminjam Bulan Lalu</span>
                        </div>
                        <span class="font-bold text-right">15</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-blue-500"></span>
                            <span>Peminjam Bulan Ini</span>
                        </div>
                        <span class="font-bold text-right">18</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-yellow-500"></span>
                            <span>Sudah Lunas</span>
                        </div>
                        <span class="font-bold text-right">17</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-red-500"></span>
                            <span>Belum Lunas</span>
                        </div>
                        <span class="font-bold text-right">23</span>
                    </li>
                </ul>
            </div>
            <div class=" p-5 justify-center align-center ">
                <div
                    class="group relative bg-green-500 w-full rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out hover:bg-green-600 overflow-hidden">
                    <a href="#"
                        class="group relative bg-green-500 w-full px-4 py-1 rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out group-hover:bg-green-600 overflow-hidden">
                        <span>More Info</span>
                        <span
                            class="transform transition-all duration-500 ease-in-out group-hover:translate-x-8 opacity-100 group-hover:opacity-0">>
                        </span>
                    </a>
                </div>

            </div>
        </div>


        <!-- Card Data Anggota -->
        <div class="bg-white shadow-md rounded-lg flex flex-col">
            <div class="w-full p-2 mt-2 mb-3 flex-none flex flex-row place-content-between rounded-lg">
                <h3 class="font-bold pt-1 px-2">Data Anggota</h3>
            </div>
            <div class="w-full h-1/2 space-y-4 flex-1 overflow-x-auto overflow-y-hidden bg-white justify-center">
                <canvas id="anggotaCharts" class="mx-auto w-full" style="max-height: 130px;"></canvas>
                <ul class="space-y-2 text-[11px] text-gray-600 px-4 w-full max-w-md mx-auto">
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-green-500"></span>
                            <span>Anggota Aktif</span>
                        </div>
                        <span class="font-bold text-right">12</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-blue-500"></span>
                            <span>Anggota Tidak Aktif</span>
                        </div>
                        <span class="font-bold text-right">20</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-red-500"></span>
                            <span>Total Anggota</span>
                        </div>
                        <span class="font-bold text-right">22</span>
                    </li>
                </ul>
            </div>
            <div class=" p-5 justify-center align-center ">
                <div
                    class="group relative bg-green-500 w-full rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out hover:bg-green-600 overflow-hidden">
                    <a href="#"
                        class="group relative bg-green-500 w-full px-4 py-1 rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out group-hover:bg-green-600 overflow-hidden">
                        <span>More Info</span>
                        <span
                            class="transform transition-all duration-500 ease-in-out group-hover:translate-x-8 opacity-100 group-hover:opacity-0">>
                        </span>
                    </a>
                </div>

            </div>
        </div>



        <!-- Card Simpanan -->
        <div class="bg-white shadow-md rounded-lg flex flex-col">

            <div class="border-b-2 p-2 mt-2 h-[15%] flex flex-col justify-center">
                <h3 class="font-bold">Simpanan</h3>
            </div>

            <div class="w-full p-2 display flex-none flex flex-row place-content-around  rounded-lg mt-2  ">
                <h5 class="rounded-lg pt-1 align-center">Jenis Simpanan</h5>
                <h5 class="pt-1 align-center">Info Total</h5>
            </div>
            {{-- Simpanan --}}
            <div class="w-full my-2 h-1/2 flex-1 overflow-x-auto bg-white align-center justify-center">

                <div
                    class="flex flex-row place-content-around mb-3 justify-center align-center items-center bg-gray-100 mx-4 rounded-lg">
                    <div class=" flex flex-col justify-center align-center w-[20%]">
                        <div class=" text-center text-[24px] bg-[#14AE5C] mx-2 my-2 rounded-lg"><i
                                class="fa-solid fa-file-lines" style="color: #ffffff;"></i></div>
                    </div>

                    <div class=" flex flex-col align-center justify-center w-[45%] ">
                        <div class="text-[10px]">Saldo Bulan Ini</div>
                        <div class="text-[12px]  text-gray-400 ">12/24/2024</div>
                    </div>
                    <div
                        class=" w-[33%] text-[12px] flex flex-col justify-center text-center bg-gray-100 h-3/4 rounded-lg py-0.5">
                        -20.001,000
                    </div>
                </div>
                <div
                    class="flex flex-row place-content-around  justify-center align-center items-center bg-gray-100 mx-4 rounded-lg">
                    <div class=" flex flex-col justify-center align-center w-[20%]">
                        <div class=" text-center text-[24px] bg-[#14AE5C] mx-2 my-2 rounded-lg"><i
                                class="fa-solid fa-file-lines" style="color: #ffffff;"></i></div>
                    </div>

                    <div class=" flex flex-col align-center justify-center w-[45%] ">
                        <div class="text-[10px]">Saldo Bulan Ini</div>
                        <div class="text-[12px]  text-gray-400 ">12/24/2024</div>
                    </div>
                    <div
                        class=" w-[33%] text-[12px] flex flex-col justify-center text-center bg-gray-100 h-3/4 rounded-lg py-0.5">
                        -20.001,000
                    </div>
                </div>
            </div>

            <div class=" p-5 justify-center align-center ">
                <div
                    class="group relative bg-green-500 w-full rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out hover:bg-green-600 overflow-hidden">
                    <a href="#"
                        class="group relative bg-green-500 w-full px-4 py-1 rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out group-hover:bg-green-600 overflow-hidden">
                        <span>More Info</span>
                        <span
                            class="transform transition-all duration-500 ease-in-out group-hover:translate-x-8 opacity-100 group-hover:opacity-0">>
                        </span>
                    </a>
                </div>

            </div>
        </div>
    </main>

</div>

<script>
// Data Pinjaman Data
// Data sesuai dengan tampilan angka
const tagihanBelumLunas = 282902700;
const tagihanBulanLalu = 382902700;
const pinjamanBulanIni = 252602700;
const pembayaranBulanIni = 100000000;

// Data untuk chart
const pinjamanData = {
    labels: [
        'Tagihan belum lunas',
        'Tagihan bulan lalu',
        'Pinjaman bulan ini',
        'Pembayaran bulan ini'
    ],
    datasets: [{
        data: [
            tagihanBelumLunas,
            tagihanBulanLalu,
            pinjamanBulanIni,
            pembayaranBulanIni
        ],
        backgroundColor: [
            '#22c55e', // Green
            '#3b82f6', // Blue
            '#eab308', // Yellow
            '#ef4444' // Red
        ],
        borderWidth: 0,
        cutout: '70%' // Donut-style
    }]
};

// Render Chart
new Chart(document.getElementById('pinjamanChart'), {
    type: 'doughnut',
    data: pinjamanData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let value = context.parsed;
                        return `${context.label}: Rp ${value.toLocaleString()}`;
                    }
                }
            }
        }
    }
});

// Data dari list di atas (dummy realistis)
const totalSaldo = 1751843000;
const penerimaan = 352900000;
const pengeluaran = 127500000;

const kasData = {
    labels: ['Penerimaan', 'Pengeluaran', 'Sisa Saldo'],
    datasets: [{
        data: [penerimaan, pengeluaran, totalSaldo - penerimaan - pengeluaran],
        backgroundColor: [
            '#3b82f6', // Penerimaan (blue)
            '#ef4444', // Pengeluaran (red)
            '#22c55e' // Sisa saldo (green)
        ],
        borderWidth: 0,
        cutout: '70%'
    }]
};

new Chart(document.getElementById('kasChart'), {
    type: 'doughnut',
    data: kasData,
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        let value = context.parsed;
                        return `${context.label}: Rp ${value.toLocaleString()}`;
                    }
                }
            }
        }
    }
});

// === Chart untuk Data Pinjaman ===
const peminjamBulanLalu = 15;
const peminjamBulanIni = 18;
const sudahLunas = 17;
const belumLunas = 23;

const pinjamanCharts = {
    labels: [
        'Peminjam Bulan Lalu',
        'Peminjam Bulan Ini',
        'Sudah Lunas',
        'Belum Lunas'
    ],
    datasets: [{
        data: [peminjamBulanLalu, peminjamBulanIni, sudahLunas, belumLunas],
        backgroundColor: [
            '#22c55e', // green
            '#3b82f6', // blue
            '#eab308', // yellow
            '#ef4444' // red
        ],
        borderWidth: 0,
        cutout: '70%'
    }]
};

new Chart(document.getElementById('pinjamanCharts'), {
    type: 'doughnut',
    data: pinjamanCharts,
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `${context.label}: ${context.parsed}`;
                    }
                }
            }
        }
    }
});

// === Chart untuk Data Anggota ===
const anggotaAktif = 12;
const anggotaTidakAktif = 20;
const totalAnggota = 22;

const anggotaCharts = {
    labels: [
        'Anggota Aktif',
        'Anggota Tidak Aktif',
        'Total Anggota'
    ],
    datasets: [{
        data: [anggotaAktif, anggotaTidakAktif, totalAnggota],
        backgroundColor: [
            '#22c55e', // green
            '#3b82f6', // blue
            '#ef4444' // red (sisanya agar match list terakhir)
        ],
        borderWidth: 0,
        cutout: '70%'
    }]
};

new Chart(document.getElementById('anggotaCharts'), {
    type: 'doughnut',
    data: anggotaCharts,
    options: {
        responsive: true,
        plugins: {
            legend: {
                display: false
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        return `${context.label}: ${context.parsed}`;
                    }
                }
            }
        }
    }
});
</script>
@endsection