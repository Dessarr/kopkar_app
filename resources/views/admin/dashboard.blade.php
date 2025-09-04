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
<div class="w-full">
    <main class="w-full grid grid-cols-3 grid-rows-7 gap-4">
        <!-- Card Pinjaman Kredit - Row Span 2 -->
        <div class="bg-white shadow-md rounded-lg flex flex-col row-span-2">
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

        <!-- Card Kas - Row Span 2 -->
        <div class="bg-white shadow-md rounded-lg flex flex-col row-span-2">
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


        <!-- Card Jatuh Tempo - Row Span 4, Col Start 3, Row Start 1 -->
        <div class="bg-white shadow-md rounded-lg flex flex-col col-start-3 row-start-1 row-span-4">

            <div class="w-full p-2 mt-2 mb-3 display flex-none flex flex-row place-content-between  rounded-lg">
                <h5 class="font-bold rounded-lg pt-1 align-center px-2">Jatuh Tempo</h5>
                <div
                    class="bg-gray-100 rounded-lg p-1 w-[20%] rounder-lg display flex flex-row gap-2 items-center justify-center">
                    <span class="text-gray-500">{{ count($jatuh_tempo) }}</span>
                    <i class="fa-solid fa-triangle-exclamation" style="color:red;"></i>

                </div>
            </div>

            {{-- Data jatuh tempo dari database --}}
            <div class="w-full px-3 flex-grow overflow-y-auto bg-white align-center justify-center jatuh-tempo-scroll"
                style="min-height: 400px;">
                <style>
                .jatuh-tempo-scroll::-webkit-scrollbar {
                    width: 6px;
                }

                .jatuh-tempo-scroll::-webkit-scrollbar-track {
                    background: #f1f1f1;
                    border-radius: 3px;
                }

                .jatuh-tempo-scroll::-webkit-scrollbar-thumb {
                    background: #c1c1c1;
                    border-radius: 3px;
                }

                .jatuh-tempo-scroll::-webkit-scrollbar-thumb:hover {
                    background: #a8a8a8;
                }
                </style>

                @if(count($jatuh_tempo) > 0)
                @foreach($jatuh_tempo as $item)
                <div class="flex flex-row place-content-around justify-center align-center items-center mb-2">
                    <div class="flex flex-col justify-center align-center w-[20%]">
                        @if($item->file_pic && Storage::disk('public')->exists('anggota/' . $item->file_pic))
                        <img src="{{ asset('storage/anggota/' . $item->file_pic) }}"
                            alt="Foto {{ $item->nama ?? 'N/A' }}"
                            class="w-8 h-8 rounded-full object-cover border-2 border-gray-200">
                        @else
                        <div
                            class="w-8 h-8 rounded-full bg-gray-100 flex items-center justify-center border-2 border-gray-200">
                            <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                                <path
                                    d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
                            </svg>
                        </div>
                        @endif
                    </div>
                    <div class="flex flex-col align-center justify-center w-[45%]">
                        <div class="text-[14px] font-semibold">{{ $item->nama ?? 'N/A' }}</div>
                        <div class="text-[12px] text-gray-400">
                            Jatuh tempo: {{ \Carbon\Carbon::parse($item->tgl_pinjam)->format('d M Y') }}
                        </div>
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

            <div class="p-3 justify-center align-center">
                <div
                    class="group relative bg-green-500 w-full rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out hover:bg-green-600 overflow-hidden">
                    <a href="{{ route('pinjaman.data_angsuran') }}"
                        class="group relative bg-green-500 w-full px-4 py-2 rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out group-hover:bg-green-600 overflow-hidden">
                        <span>More Info</span>
                        <span
                            class="transform transition-all duration-500 ease-in-out group-hover:translate-x-8 opacity-100 group-hover:opacity-0">>
                        </span>
                    </a>
                </div>
            </div>
        </div>

        <!-- Card Data Pinjaman - Row Span 2, Col Start 1, Row Start 3 -->
        <div class="bg-white shadow-md rounded-lg flex flex-col col-start-1 row-start-3 row-span-2">
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


        <!-- Card Data Anggota - Row Span 2, Col Start 2, Row Start 3 -->
        <div class="bg-white shadow-md rounded-lg flex flex-col col-start-2 row-start-3 row-span-2">
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



        <!-- Card Simpanan - Col Span 3, Row Span 2, Row Start 5 -->
        <div class="bg-white shadow-md rounded-lg flex flex-col col-span-3 row-start-5 row-span-2">

            <div class="border-b-2 p-2 mt-2 h-[15%] flex flex-col justify-center">
                <h3 class="font-bold">Simpanan</h3>
            </div>

            <!-- Header Tabel Simpanan -->
            <div class="w-full p-4">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="text-xs uppercase bg-blue-600 text-white">
                            <tr>
                                <th class="px-4 py-3">Jenis Simpanan</th>
                                <th class="px-4 py-3 text-center">Tagihan Bulan Lalu</th>
                                <th class="px-4 py-3 text-center">Tab. Perumahan</th>
                                <th class="px-4 py-3 text-center">Simpanan Sukarela</th>
                                <th class="px-4 py-3 text-center">Simpanan Pokok</th>
                                <th class="px-4 py-3 text-center">Simpanan Wajib</th>
                                <th class="px-4 py-3 text-center">Simpanan Khusus I</th>
                                <th class="px-4 py-3 text-center">Simpanan Khusus II</th>
                            </tr>
                        </thead>
                        <tbody class="text-gray-800">
                            <!-- Saldo Bulan Lalu -->
                            <tr class="bg-blue-50 border-b">
                                <td class="px-4 py-3 font-semibold">Saldo Bulan Lalu</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['tagihan_bulan_lalu'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">{{ number_format($simpanan['tab_perumahan'] ?? 0) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['simpanan_sukarela'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">{{ number_format($simpanan['saldo_pokok'] ?? 0) }}
                                </td>
                                <td class="px-4 py-3 text-center">{{ number_format($simpanan['saldo_wajib'] ?? 0) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['simpanan_khusus_1'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['simpanan_khusus_2'] ?? 0) }}</td>
                            </tr>
                            <!-- Penerimaan Bulan Ini -->
                            <tr class="bg-green-50 border-b">
                                <td class="px-4 py-3 font-semibold">Penerimaan Bulan Ini</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['penerimaan_tagihan'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['penerimaan_perumahan'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['penerimaan_sukarela'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['penerimaan_pokok'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['penerimaan_wajib'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['penerimaan_khusus_1'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['penerimaan_khusus_2'] ?? 0) }}</td>
                            </tr>
                            <!-- Penarikan Bulan Ini -->
                            <tr class="bg-red-50 border-b">
                                <td class="px-4 py-3 font-semibold">Penarikan Bulan Ini</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['penarikan_tagihan'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['penarikan_perumahan'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['penarikan_sukarela'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">{{ number_format($simpanan['penarikan_pokok'] ?? 0) }}
                                </td>
                                <td class="px-4 py-3 text-center">{{ number_format($simpanan['penarikan_wajib'] ?? 0) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['penarikan_khusus_1'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['penarikan_khusus_2'] ?? 0) }}</td>
                            </tr>
                            <!-- Saldo Bulan Ini -->
                            <tr class="bg-blue-100 border-b font-bold">
                                <td class="px-4 py-3">Saldo Bulan Ini</td>
                                <td class="px-4 py-3 text-center">{{ number_format($simpanan['saldo_tagihan'] ?? 0) }}
                                </td>
                                <td class="px-4 py-3 text-center">{{ number_format($simpanan['saldo_perumahan'] ?? 0) }}
                                </td>
                                <td class="px-4 py-3 text-center">{{ number_format($simpanan['saldo_sukarela'] ?? 0) }}
                                </td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['saldo_pokok_final'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">
                                    {{ number_format($simpanan['saldo_wajib_final'] ?? 0) }}</td>
                                <td class="px-4 py-3 text-center">{{ number_format($simpanan['saldo_khusus_1'] ?? 0) }}
                                </td>
                                <td class="px-4 py-3 text-center">{{ number_format($simpanan['saldo_khusus_2'] ?? 0) }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                    </div>

            <!-- More Info Button -->
            <div class="p-5 justify-center align-center">
                    <div
                        class="group relative bg-green-500 w-full rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out hover:bg-green-600 overflow-hidden">
                        <a href="#"
                        class="group relative bg-green-500 w-full px-4 py-3 rounded-full text-white flex flex-row place-content-between transition-all duration-300 ease-in-out group-hover:bg-green-600 overflow-hidden">
                            <span>More Info</span>
                            <span
                            class="transform transition-all duration-500 ease-in-out group-hover:translate-x-8 opacity-100 group-hover:opacity-0">></span>
                        </a>
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