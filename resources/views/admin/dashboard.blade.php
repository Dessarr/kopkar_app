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
                <!-- CSS Donut Chart untuk Pinjaman Kredit -->
                <div class="relative mx-auto w-32 h-32">
                    @php
                    $total = ($pinjaman_kredit['tagihan_belum_lunas'] ?? 0) +
                    ($pinjaman_kredit['tagihan_bulan_lalu'] ?? 0) +
                    ($pinjaman_kredit['pinjaman_bulan_ini'] ?? 0) +
                    ($pinjaman_kredit['pembayaran_bulan_ini'] ?? 0);

                    if ($total > 0) {
                    $angle1 = (($pinjaman_kredit['tagihan_belum_lunas'] ?? 0) / $total) * 360;
                    $angle2 = (($pinjaman_kredit['tagihan_bulan_lalu'] ?? 0) / $total) * 360;
                    $angle3 = (($pinjaman_kredit['pinjaman_bulan_ini'] ?? 0) / $total) * 360;
                    $angle4 = (($pinjaman_kredit['pembayaran_bulan_ini'] ?? 0) / $total) * 360;
                    } else {
                    $angle1 = $angle2 = $angle3 = $angle4 = 0;
                    }
                    @endphp

                    <div class="w-full h-full rounded-full" style="--angle1: {{ $angle1 }}deg; 
                                 --angle2: {{ $angle2 }}deg; 
                                 --angle3: {{ $angle3 }}deg; 
                                 --angle4: {{ $angle4 }}deg; 
                                 background: conic-gradient(
                                     #22c55e 0deg var(--angle1),
                                     #3b82f6 var(--angle1) calc(var(--angle1) + var(--angle2)),
                                     #eab308 calc(var(--angle1) + var(--angle2)) calc(var(--angle1) + var(--angle2) + var(--angle3)),
                                     #ef4444 calc(var(--angle1) + var(--angle2) + var(--angle3)) 360deg
                                 );"></div>
                </div>
                <ul class="space-y-2 text-[11px] text-gray-600 px-4 w-full max-w-md mx-auto">
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-green-500"></span>
                            <span>Tagihan belum lunas:</span>
                        </div>
                        <span
                            class="font-bold text-right">{{ number_format($pinjaman_kredit['tagihan_belum_lunas'] ?? 0) }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-blue-500"></span>
                            <span>Tagihan belum lunas bulan lalu:</span>
                        </div>
                        <span
                            class="font-bold text-right">{{ number_format($pinjaman_kredit['tagihan_bulan_lalu'] ?? 0) }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-yellow-500"></span>
                            <span>Pinjaman bulan ini:</span>
                        </div>
                        <span
                            class="font-bold text-right">{{ number_format($pinjaman_kredit['pinjaman_bulan_ini'] ?? 0) }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-red-500"></span>
                            <span>Pembayaran bulan ini:</span>
                        </div>
                        <span
                            class="font-bold text-right">{{ number_format($pinjaman_kredit['pembayaran_bulan_ini'] ?? 0) }}</span>
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
                <!-- CSS Donut Chart untuk Kas -->
                <div class="relative mx-auto w-32 h-32">
                    @php
                    $total_kas = ($kas['penerimaan_bulan_ini'] ?? 0) +
                    ($kas['pengeluaran_bulan_ini'] ?? 0) +
                    ($kas['total_saldo'] ?? 0);

                    if ($total_kas > 0) {
                    $kas_angle1 = (($kas['penerimaan_bulan_ini'] ?? 0) / $total_kas) * 360;
                    $kas_angle2 = (($kas['pengeluaran_bulan_ini'] ?? 0) / $total_kas) * 360;
                    $kas_angle3 = (($kas['total_saldo'] ?? 0) / $total_kas) * 360;
                    } else {
                    $kas_angle1 = $kas_angle2 = $kas_angle3 = 0;
                    }
                    @endphp

                    <div class="w-full h-full rounded-full" style="--kas-angle1: {{ $kas_angle1 }}deg; 
                                 --kas-angle2: {{ $kas_angle2 }}deg; 
                                 --kas-angle3: {{ $kas_angle3 }}deg; 
                                 background: conic-gradient(
                                     #3b82f6 0deg var(--kas-angle1),
                                     #ef4444 var(--kas-angle1) calc(var(--kas-angle1) + var(--kas-angle2)),
                                     #22c55e calc(var(--kas-angle1) + var(--kas-angle2)) 360deg
                                 );"></div>
                </div>

                <ul class="space-y-2 text-[11px] text-gray-600 px-4 w-full max-w-md mx-auto">
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-green-500"></span>
                            <span>Total saldo:</span>
                        </div>
                        <span class="font-bold text-right">{{ number_format($kas['total_saldo'] ?? 0) }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-blue-500"></span>
                            <span>Penerimaan bulan ini:</span>
                        </div>
                        <span class="font-bold text-right">{{ number_format($kas['penerimaan_bulan_ini'] ?? 0) }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-red-500"></span>
                            <span>Pengeluaran bulan ini:</span>
                        </div>
                        <span
                            class="font-bold text-right">{{ number_format($kas['pengeluaran_bulan_ini'] ?? 0) }}</span>
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
                    <span class="text-gray-500">{{ count($jatuh_tempo) }}</span>
                    <i class="fa-solid fa-triangle-exclamation" style="color:red;"></i>

                </div>
            </div>
            {{-- Data jatuh tempo dari database --}}
            <div class="w-full px-3 h-1/2 flex-1 overflow-x-auto bg-white align-center justify-center">
                @if(count($jatuh_tempo) > 0)
                @foreach($jatuh_tempo as $item)
                <div class="flex flex-row place-content-around justify-center align-center items-center mb-2">
                    <div class="flex flex-col justify-center align-center w-[20%]">
                        <div class="text-center text-[34px]">O</div>
                    </div>
                    <div class="flex flex-col align-center justify-center w-[45%]">
                        <div class="text-[14px]">{{ $item->nama ?? 'N/A' }}</div>
                        <div class="text-[12px] text-gray-400">
                            {{ \Carbon\Carbon::parse($item->tgl_pinjam)->format('m/d/Y') }}</div>
                    </div>
                    <div
                        class="w-[33%] text-[12px] flex flex-col justify-center text-center bg-gray-100 h-3/4 rounded-lg py-0.5">
                        {{ number_format($item->jumlah ?? 0) }}
                    </div>
                </div>
                @endforeach
                @else
                <div class="text-center text-gray-500 py-4">
                    Tidak ada data jatuh tempo
                </div>
                @endif
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
                <!-- CSS Donut Chart untuk Data Pinjaman -->
                <div class="relative mx-auto w-32 h-32">
                    @php
                    $total_pinjaman = ($data_pinjaman['peminjam_bulan_lalu'] ?? 0) +
                    ($data_pinjaman['peminjam_bulan_ini'] ?? 0) +
                    ($data_pinjaman['sudah_lunas'] ?? 0) +
                    ($data_pinjaman['belum_lunas'] ?? 0);

                    if ($total_pinjaman > 0) {
                    $pin_angle1 = (($data_pinjaman['peminjam_bulan_lalu'] ?? 0) / $total_pinjaman) * 360;
                    $pin_angle2 = (($data_pinjaman['peminjam_bulan_ini'] ?? 0) / $total_pinjaman) * 360;
                    $pin_angle3 = (($data_pinjaman['sudah_lunas'] ?? 0) / $total_pinjaman) * 360;
                    $pin_angle4 = (($data_pinjaman['belum_lunas'] ?? 0) / $total_pinjaman) * 360;
                    } else {
                    $pin_angle1 = $pin_angle2 = $pin_angle3 = $pin_angle4 = 0;
                    }
                    @endphp

                    <div class="w-full h-full rounded-full" style="--pin-angle1: {{ $pin_angle1 }}deg; 
                                 --pin-angle2: {{ $pin_angle2 }}deg; 
                                 --pin-angle3: {{ $pin_angle3 }}deg; 
                                 --pin-angle4: {{ $pin_angle4 }}deg; 
                                 background: conic-gradient(
                                     #22c55e 0deg var(--pin-angle1),
                                     #3b82f6 var(--pin-angle1) calc(var(--pin-angle1) + var(--pin-angle2)),
                                     #eab308 calc(var(--pin-angle1) + var(--pin-angle2)) calc(var(--pin-angle1) + var(--pin-angle2) + var(--pin-angle3)),
                                     #ef4444 calc(var(--pin-angle1) + var(--pin-angle2) + var(--pin-angle3)) 360deg
                                 );"></div>
                </div>
                <ul class="space-y-2 text-[11px] text-gray-600 px-4 w-full max-w-md mx-auto">
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-green-500"></span>
                            <span>Peminjam Bulan Lalu</span>
                        </div>
                        <span class="font-bold text-right">{{ $data_pinjaman['peminjam_bulan_lalu'] ?? 0 }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-blue-500"></span>
                            <span>Peminjam Bulan Ini</span>
                        </div>
                        <span class="font-bold text-right">{{ $data_pinjaman['peminjam_bulan_ini'] ?? 0 }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-yellow-500"></span>
                            <span>Sudah Lunas</span>
                        </div>
                        <span class="font-bold text-right">{{ $data_pinjaman['sudah_lunas'] ?? 0 }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-red-500"></span>
                            <span>Belum Lunas</span>
                        </div>
                        <span class="font-bold text-right">{{ $data_pinjaman['belum_lunas'] ?? 0 }}</span>
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
                <!-- CSS Donut Chart untuk Data Anggota -->
                <div class="relative mx-auto w-32 h-32">
                    @php
                    $total_anggota_chart = ($data_anggota['anggota_aktif'] ?? 0) +
                    ($data_anggota['anggota_tidak_aktif'] ?? 0);

                    if ($total_anggota_chart > 0) {
                    $ang_angle1 = (($data_anggota['anggota_aktif'] ?? 0) / $total_anggota_chart) * 360;
                    $ang_angle2 = (($data_anggota['anggota_tidak_aktif'] ?? 0) / $total_anggota_chart) * 360;
                    } else {
                    $ang_angle1 = $ang_angle2 = 0;
                    }
                    @endphp

                    <div class="w-full h-full rounded-full" style="--ang-angle1: {{ $ang_angle1 }}deg; 
                                 --ang-angle2: {{ $ang_angle2 }}deg; 
                                 background: conic-gradient(
                                     #22c55e 0deg var(--ang-angle1),
                                     #3b82f6 var(--ang-angle1) 360deg
                                 );"></div>
                </div>
                <ul class="space-y-2 text-[11px] text-gray-600 px-4 w-full max-w-md mx-auto">
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-green-500"></span>
                            <span>Anggota Aktif</span>
                        </div>
                        <span class="font-bold text-right">{{ $data_anggota['anggota_aktif'] ?? 0 }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-blue-500"></span>
                            <span>Anggota Tidak Aktif</span>
                        </div>
                        <span class="font-bold text-right">{{ $data_anggota['anggota_tidak_aktif'] ?? 0 }}</span>
                    </li>
                    <li class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <span class="w-2.5 h-2.5 min-w-[10px] min-h-[10px] rounded-full bg-red-500"></span>
                            <span>Total Anggota</span>
                        </div>
                        <span class="font-bold text-right">{{ $data_anggota['total_anggota'] ?? 0 }}</span>
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
                        <div class="text-[10px]">Simpanan Pokok</div>
                        <div class="text-[12px]  text-gray-400 ">{{ now()->format('m/d/Y') }}</div>
                    </div>
                    <div
                        class=" w-[33%] text-[12px] flex flex-col justify-center text-center bg-gray-100 h-3/4 rounded-lg py-0.5">
                        {{ number_format($simpanan['saldo_pokok'] ?? 0) }}
                    </div>
                </div>
                <div
                    class="flex flex-row place-content-around  justify-center align-center items-center bg-gray-100 mx-4 rounded-lg">
                    <div class=" flex flex-col justify-center align-center w-[20%]">
                        <div class=" text-center text-[24px] bg-[#14AE5C] mx-2 my-2 rounded-lg"><i
                                class="fa-solid fa-file-lines" style="color: #ffffff;"></i></div>
                    </div>

                    <div class=" flex flex-col align-center justify-center w-[45%] ">
                        <div class="text-[10px]">Simpanan Wajib</div>
                        <div class="text-[12px]  text-gray-400 ">{{ now()->format('m/d/Y') }}</div>
                    </div>
                    <div
                        class=" w-[33%] text-[12px] flex flex-col justify-center text-center bg-gray-100 h-3/4 rounded-lg py-0.5">
                        {{ number_format($simpanan['saldo_wajib'] ?? 0) }}
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
        </div>
    </main>

</div>

<script>
// Chart sudah diganti dengan CSS Donut Charts yang lebih elegan dan tidak ada error!
// Data langsung dari PHP ke CSS conic-gradient
// Tidak perlu JavaScript lagi - dashboard lebih cepat dan stabil
</script>
@endsection