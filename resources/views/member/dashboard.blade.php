@extends('layouts.member')

@section('title', 'Member Dashboard')

@section('content')
<!-- Date Selector -->
<div class="flex items-center mb-6">
    <div class="bg-gray-100 px-4 py-2 rounded-lg flex items-center">
        <svg class="w-5 h-5 text-gray-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        <span class="text-gray-700 font-medium">{{ now()->format('F Y') }}</span>
    </div>
</div>

<!-- Page Title -->
<div class="text-center mb-8">
    <h1 class="text-3xl font-bold text-gray-800">Data Anggota</h1>
</div>

<!-- Main Content Container with Background -->
<div class="bg-gradient-to-br from-blue-50 to-indigo-100 rounded-xl p-8 shadow-lg">
    <!-- Main Content Grid with Golden Ratio -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-8">
        <!-- Left Side - 4 Cards in 2x2 Grid (Golden Ratio: ~3/5) -->
        <div class="lg:col-span-3">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Card 1: Data Pribadi -->
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-lg shadow-md p-6 transition-all duration-300 hover:backdrop-blur-none hover:bg-white hover:shadow-xl hover:scale-105 blur-sm hover:blur-none">
                    <h3 class="text-lg font-semibold text-green-700 mb-4">Data Pribadi</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">ID Koperasi:</span>
                            <span class="font-semibold">{{ $anggota->no_ktp ?? '1234567890123456' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tempat, Tgl Lahir:</span>
                            <span class="font-semibold">{{ $anggota->tmp_lahir ?? 'Jakarta' }},
                                {{ $anggota->tgl_lahir ?? '2000-01-01' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Jenis Kelamin:</span>
                            <span
                                class="font-semibold">{{ $anggota->jk == 'L' ? 'Laki-laki' : ($anggota->jk == 'P' ? 'Perempuan' : 'Laki-laki') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status:</span>
                            <span class="font-semibold">{{ $anggota->status ?? 'Belum Menikah' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Agama:</span>
                            <span class="font-semibold">{{ $anggota->agama ?? 'Islam' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Kontak & Pekerjaan -->
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-lg shadow-md p-6 transition-all duration-300 hover:backdrop-blur-none hover:bg-white hover:shadow-xl hover:scale-105 blur-sm hover:blur-none">
                    <h3 class="text-lg font-semibold text-green-700 mb-4">Kontak & Pekerjaan</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Alamat:</span>
                            <span class="font-semibold">{{ $anggota->alamat ?? 'Jl. Contoh No.1' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Kota:</span>
                            <span class="font-semibold">{{ $anggota->kota ?? 'Jakarta' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">No. Telp:</span>
                            <span class="font-semibold">{{ $anggota->notelp ?? '08123456789' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Departemen:</span>
                            <span class="font-semibold">{{ $anggota->departement ?? 'IT' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Jabatan:</span>
                            <span class="font-semibold">{{ $anggota->jabatan_id ?? '1' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Bank & Rekening -->
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-lg shadow-md p-6 transition-all duration-300 hover:backdrop-blur-none hover:bg-white hover:shadow-xl hover:scale-105 blur-sm hover:blur-none">
                    <h3 class="text-lg font-semibold text-green-700 mb-4">Bank & Rekening</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Bank:</span>
                            <span class="font-semibold">{{ $anggota->bank ?? 'BCA' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">No. Rekening:</span>
                            <span class="font-semibold">{{ $anggota->no_rekening ?? '1234567890' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Nama Pemilik Rekening:</span>
                            <span class="font-semibold">{{ $anggota->nama_pemilik_rekening ?? 'prakerinmember' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Status Keanggotaan -->
                <div
                    class="bg-white/80 backdrop-blur-sm rounded-lg shadow-md p-6 transition-all duration-300 hover:backdrop-blur-none hover:bg-white hover:shadow-xl hover:scale-105 blur-sm hover:blur-none">
                    <h3 class="text-lg font-semibold text-green-700 mb-4">Status Keanggotaan</h3>
                    <div class="space-y-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Tanggal Daftar:</span>
                            <span class="font-semibold">{{ $anggota->tgl_daftar ?? '2025-07-11' }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Status Aktif:</span>
                            <span class="font-semibold">{{ $anggota->aktif == 'Y' ? 'Aktif' : 'Tidak Aktif' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Profile Photo and Identity (Golden Ratio: ~2/5) -->
        <div class="lg:col-span-2">
            <div class="bg-white/90 backdrop-blur-sm rounded-lg shadow-md p-6 h-fit">
    <div class="flex flex-col items-center mb-6">
                    <!-- Profile Photo -->
                    <div class="mb-4">
        @if($anggota->file_pic && Storage::disk('public')->exists('anggota/' . $anggota->file_pic))
        <img src="{{ asset('storage/anggota/' . $anggota->file_pic) }}" alt="Foto {{ $anggota->nama }}"
                            class="w-24 h-24 object-cover rounded-full border-4 border-green-500 shadow-lg">
        @else
        <div
                            class="w-24 h-24 rounded-full border-4 border-green-500 flex items-center justify-center bg-gray-100 shadow-lg">
                            <svg class="w-10 h-10 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                <path
                    d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z" />
            </svg>
        </div>
        @endif
                    </div>

                    <!-- Name and Status -->
                    <h2 class="text-xl font-bold text-gray-800 mb-1">{{ $anggota->nama ?? 'prakerinmember' }}</h2>
                    <p class="text-green-700 font-semibold text-sm">Anggota</p>
                </div>

                <!-- Identity Details -->
                <div class="space-y-2 text-sm">
                    <div class="flex border-b border-gray-200 pb-2">
                        <span class="font-semibold text-gray-600 w-24">ID Anggota:</span>
                        <span class="text-gray-800">{{ $anggota->no_ktp ?? '1234567890123456' }}</span>
                    </div>
                    <div class="flex border-b border-gray-200 pb-2">
                        <span class="font-semibold text-gray-600 w-24">Nama:</span>
                        <span class="text-gray-800 font-bold">{{ $anggota->nama ?? 'prakerinmember' }}</span>
                    </div>
                    <div class="flex border-b border-gray-200 pb-2">
                        <span class="font-semibold text-gray-600 w-24">Jenis Kelamin:</span>
                        <span
                            class="text-gray-800">{{ $anggota->jk == 'L' ? 'Laki-Laki' : ($anggota->jk == 'P' ? 'Perempuan' : 'Laki-Laki') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Financial Summary Cards with New Grid Layout -->
<div class="grid grid-cols-7 grid-rows-6 gap-4 mt-8">
    <!-- 1. Saldo Simpanan (Top Left) -->
    <div class="col-span-2 row-span-2 bg-orange-500 rounded-lg shadow-md p-4 text-white">
        <div class="flex justify-between items-start mb-3">
            <h3 class="text-lg font-bold">Saldo Simpanan</h3>
            <div class="bg-white bg-opacity-20 p-2 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
            </div>
        </div>
        <div class="space-y-1 text-xs">
            @foreach($simpananList as $simpanan)
            <div class="flex justify-between">
                <span>{{ $simpanan['nama'] }}:</span>
                <span class="font-bold">{{ number_format($simpanan['jumlah'], 0, ',', '.') }}</span>
            </div>
            @endforeach
            <div class="border-t border-white border-opacity-30 pt-1 mt-1">
                <div class="flex justify-between font-bold">
                    <span>Jumlah:</span>
                    <span>{{ number_format($totalSimpanan, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Tagihan Kredit (Middle Left) -->
    <div class="col-span-2 row-span-2 col-start-1 row-start-3 bg-purple-500 rounded-lg shadow-md p-4 text-white">
        <div class="flex justify-between items-start mb-3">
            <h3 class="text-lg font-bold">Tagihan Kredit</h3>
            <div class="bg-white bg-opacity-20 p-2 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253">
                    </path>
                </svg>
            </div>
        </div>
        <div class="space-y-1 text-xs">
            <div class="flex justify-between">
                <span>Pinjaman Biasa:</span>
                <span class="font-bold">{{ number_format($totalPinjaman, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Sisa Pinjaman:</span>
                <span class="font-bold">{{ number_format($sisaPinjaman, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pinjaman Barang:</span>
                <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Sisa Pinjaman:</span>
                <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pinjaman Bank:</span>
                <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tagihan Takterbayar:</span>
                <span class="font-bold">{{ number_format($totalTagihan, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- 3. Keterangan (Bottom Left) -->
    <div class="col-span-2 row-span-2 col-start-1 row-start-5 bg-blue-500 rounded-lg shadow-md p-4 text-white">
        <div class="flex justify-between items-start mb-3">
            <h3 class="text-lg font-bold">Keterangan</h3>
            <div class="bg-white bg-opacity-20 p-2 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                    </path>
                </svg>
            </div>
        </div>
        <div class="space-y-1 text-xs">
            <div class="flex justify-between">
                <span>Jumlah Pinjaman:</span>
                <span class="font-bold">{{ number_format($totalPinjaman, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pinjaman Lunas:</span>
                <span class="font-bold">{{ $pinjamanLunas }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span>Pembayaran:</span>
                <span
                    class="bg-white text-red-500 px-1 py-0.5 rounded text-xs font-bold border border-red-500">Lancar</span>
            </div>
            <div class="flex justify-between">
                <span>Tanggal Tempo:</span>
                <span class="font-bold">{{ $anggota->tanggal_tempo ?? '-' }}</span>
        </div>
    </div>
</div>

    <!-- 4. Tagihan Simpanan (Center - Long Card) -->
    <div class="col-span-2 row-span-6 col-start-3 row-start-1 bg-green-500 rounded-lg shadow-md p-4 text-white">
        <div class="flex justify-between items-start mb-3">
            <h3 class="text-lg font-bold">Tagihan Simpanan</h3>
            <div class="bg-white bg-opacity-20 p-2 rounded-full">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
            </div>
        </div>
        <div class="space-y-1 text-xs">
            @foreach($simpananList as $simpanan)
            <div class="flex justify-between">
                <span>{{ $simpanan['nama'] }}:</span>
                <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
            </div>
            @endforeach
            <div class="flex justify-between">
                <span>Pinjaman Biasa:</span>
                <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
                <span class="bg-pink-500 text-white px-1 py-0.5 rounded text-xs">0/0</span>
            </div>
            <div class="flex justify-between">
                <span>Jasa (1%):</span>
                <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pinjaman Barang:</span>
                <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
                <span class="bg-pink-500 text-white px-1 py-0.5 rounded text-xs">0/0</span>
            </div>
            <div class="flex justify-between">
                <span>Jasa (2%):</span>
                <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pinjaman Bank BSM:</span>
                <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
                <span class="bg-pink-500 text-white px-1 py-0.5 rounded text-xs">0/0</span>
            </div>
            <div class="flex justify-between">
                <span>Jasa (1%):</span>
                <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Toserda:</span>
                <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Lain-lain:</span>
                <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
            </div>
            <div class="border-t border-white border-opacity-30 pt-1 mt-1">
                <div class="space-y-1">
                    <div class="flex justify-between">
                        <span>Jumlah:</span>
                        <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tag Bulan Lalu:</span>
                        <span class="font-bold">{{ number_format($tagihanBulanLalu, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-bold">Pot Gaji:</span>
                        <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="font-bold">Pot Simpanan:</span>
                        <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span>Tag Harus Dibayar:</span>
                        <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 5. Notifikasi Pengajuan Pinjaman (Top Right) -->
    <div class="col-span-3 row-span-3 col-start-5 row-start-1 bg-blue-100 rounded-lg p-4 border border-blue-200">
        <div class="flex items-center h-full">
            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            <h3 class="text-lg font-semibold text-blue-800">Belum ada Pengajuan Pinjaman</h3>
        </div>
    </div>

    <!-- 6. Notifikasi Penarikan Simpanan (Bottom Right) -->
    <div class="col-span-3 row-span-3 col-start-5 row-start-4 bg-blue-100 rounded-lg p-4 border border-blue-200">
        <div class="flex items-center h-full">
            <svg class="w-8 h-8 text-blue-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
            <h3 class="text-lg font-semibold text-blue-800">Belum ada Penarikan Simpanan</h3>
        </div>
    </div>
</div>
@endsection