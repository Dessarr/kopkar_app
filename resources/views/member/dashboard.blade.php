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

<!-- Financial Dashboard Grid Layout -->
<div class="grid grid-cols-9 grid-rows-6 gap-4 mt-8">
    <!-- 1. Saldo Simpanan (Top Left) - col-span-3 row-span-2 -->
    <div class="col-span-3 row-span-2 bg-orange-100 rounded-lg p-4 border border-orange-200 text-orange-800">
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
            <div class="flex justify-between">
                <span>Simpanan Wajib:</span>
                <span class="font-bold">{{ number_format($saldoSimpanan->simpanan_wajib ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Simpanan Sukarela:</span>
                <span class="font-bold">{{ number_format($saldoSimpanan->simpanan_sukarela ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Simpanan Khusus II:</span>
                <span class="font-bold">{{ number_format($saldoSimpanan->simpanan_khusus_2 ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Simpanan Pokok:</span>
                <span class="font-bold">{{ number_format($saldoSimpanan->simpanan_pokok ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Simpanan Khusus I:</span>
                <span class="font-bold">{{ number_format($saldoSimpanan->simpanan_khusus_1 ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tab. Perumahan:</span>
                <span class="font-bold">{{ number_format($saldoSimpanan->tab_perumahan ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="border-t border-white border-opacity-30 pt-1 mt-1">
                <div class="flex justify-between font-bold">
                    <span>Jumlah:</span>
                    <span>{{ number_format(($saldoSimpanan->simpanan_wajib ?? 0) + ($saldoSimpanan->simpanan_sukarela ?? 0) + ($saldoSimpanan->simpanan_khusus_2 ?? 0) + ($saldoSimpanan->simpanan_pokok ?? 0) + ($saldoSimpanan->simpanan_khusus_1 ?? 0) + ($saldoSimpanan->tab_perumahan ?? 0), 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- 2. Tagihan Kredit (Middle Left) - col-span-3 row-span-2 col-start-1 row-start-3 -->
    <div
        class="col-span-3 row-span-2 col-start-1 row-start-3 bg-purple-100 rounded-lg p-4 border border-purple-200 text-purple-800">
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
                <span class="font-bold">{{ number_format($tagihanKredit->pinjaman_biasa ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Sisa Pinjaman:</span>
                <span
                    class="font-bold">{{ number_format($tagihanKredit->sisa_pinjaman_biasa ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pinjaman Bank:</span>
                <span class="font-bold">{{ number_format($tagihanKredit->pinjaman_bank ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Sisa Pinjaman:</span>
                <span class="font-bold">{{ number_format($tagihanKredit->sisa_pinjaman_bank ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pinjaman Barang:</span>
                <span class="font-bold">{{ number_format($tagihanKredit->pinjaman_barang ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Sisa Pinjaman:</span>
                <span
                    class="font-bold">{{ number_format($tagihanKredit->sisa_pinjaman_barang ?? 0, 0, ',', '.') }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tagihan Takterbayar:</span>
                <span class="font-bold">{{ number_format(0, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>

    <!-- 3. Keterangan (Bottom Left) - col-span-3 row-span-2 col-start-1 row-start-5 -->
    <div
        class="col-span-3 row-span-2 col-start-1 row-start-5 bg-blue-100 rounded-lg p-4 border border-blue-200 text-blue-800">
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
                <span class="font-bold">{{ $keteranganPinjaman->jumlah_pinjaman ?? 0 }}</span>
            </div>
            <div class="flex justify-between">
                <span>Pinjaman Lunas:</span>
                <span class="font-bold">{{ $keteranganPinjaman->pinjaman_lunas ?? 0 }}</span>
            </div>
            <div class="flex justify-between items-center">
                <span>Pembayaran:</span>
                <span
                    class="bg-white px-1 py-0.5 rounded text-xs font-bold border {{ $keteranganPinjaman->status_pembayaran == 'Lancar' ? 'text-green-500 border-green-500' : 'text-red-500 border-red-500' }}">{{ $keteranganPinjaman->status_pembayaran ?? 'Lancar' }}</span>
            </div>
            <div class="flex justify-between">
                <span>Tanggal Tempo:</span>
                <span class="font-bold">{{ $keteranganPinjaman->tanggal_tempo ?? '-' }}</span>
            </div>
        </div>
    </div>

    <!-- 4. Tagihan Simpanan (Center - Long Card) - col-span-3 row-span-6 col-start-4 row-start-1 -->
    <div
        class="col-span-3 row-span-6 col-start-4 row-start-1 bg-green-100 rounded-lg p-4 border border-green-200 text-green-800">
        <div class="flex justify-between items-start mb-3">
            <h3 class="text-lg font-bold">Tagihan Simpanan</h3>
            <div class="flex items-center space-x-2">
                <!-- Filter Periode -->
                <input type="month" class="text-xs px-2 py-1 rounded border border-green-300 bg-white text-green-800"
                    value="{{ $periode }}" onchange="changePeriode(this.value)">
                <div class="bg-white bg-opacity-20 p-2 rounded-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                        </path>
                    </svg>
                </div>
            </div>
        </div>
        
        <!-- Layout Grid System -->
        <div class="grid grid-cols-10 grid-rows-13 gap-1 text-xs">
            <!-- Header -->
            <div class="col-span-3 font-semibold text-green-700">Item</div>
            <div class="col-span-5 font-semibold text-green-700 text-right">Nominal</div>
            <div class="col-span-2"></div>
            
            <!-- Simpanan Sukarela -->
            <div class="col-span-3">Simpanan Sukarela:</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($simpananList[0]['jumlah'] ?? 0, 0, ',', '.') }}</div>
            <div class="col-span-2"></div>
            
            <!-- Simpanan Pokok -->
            <div class="col-span-3">Simpanan Pokok:</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($simpananList[1]['jumlah'] ?? 0, 0, ',', '.') }}</div>
            <div class="col-span-2"></div>
            
            <!-- Simpanan Wajib -->
            <div class="col-span-3">Simpanan Wajib:</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($simpananList[2]['jumlah'] ?? 0, 0, ',', '.') }}</div>
            <div class="col-span-2"></div>
            
            <!-- Simpanan Khusus II -->
            <div class="col-span-3">Simpanan Khusus II:</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($simpananList[3]['jumlah'] ?? 0, 0, ',', '.') }}</div>
            <div class="col-span-2"></div>
            
            <!-- Jumlah (Total Simpanan) -->
            <div class="col-span-3 border-t border-green-200 pt-1 font-bold">Jumlah:</div>
            <div class="col-span-5 border-t border-green-200 pt-1 font-bold text-right">{{ number_format($jmlSimpans->jml_total ?? 0, 0, ',', '.') }}</div>
            <div class="col-span-2"></div>
            
            <!-- Pinjaman Biasa -->
            <div class="col-span-3">Pinjaman Biasa:</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($tagihanData['pinjaman_biasa']->jumlah ?? 0, 0, ',', '.') }}</div>
            <div class="col-span-2 text-center">
                <span class="bg-pink-500 text-white px-1 py-0.5 rounded text-xs">{{ $tagihanData['pinjaman_biasa']->angsuran ?? 0 }}/{{ $tagihanData['pinjaman_biasa']->lama_angsuran ?? 0 }}</span>
            </div>
            
            <!-- Jasa (1%) -->
            <div class="col-span-3">Jasa (1%):</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($tagihanData['pinjaman_biasa']->jasa ?? 0, 0, ',', '.') }}</div>
            <div class="col-span-2"></div>
            
            <!-- Pinjaman Barang -->
            <div class="col-span-3">Pinjaman Barang:</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($tagihanData['pinjaman_barang']->jumlah ?? 0, 0, ',', '.') }}</div>
            <div class="col-span-2 text-center">
                <span class="bg-pink-500 text-white px-1 py-0.5 rounded text-xs">{{ $tagihanData['pinjaman_barang']->angsuran ?? 0 }}/{{ $tagihanData['pinjaman_barang']->lama_angsuran ?? 0 }}</span>
            </div>
            
            <!-- Jasa (2%) -->
            <div class="col-span-3">Jasa (2%):</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($tagihanData['pinjaman_barang']->jasa ?? 0, 0, ',', '.') }}</div>
            <div class="col-span-2"></div>
            
            <!-- Pinjaman Bank BSM -->
            <div class="col-span-3">Pinjaman Bank BSM:</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($tagihanData['pinjaman_bank']->jumlah ?? 0, 0, ',', '.') }}</div>
            <div class="col-span-2 text-center">
                <span class="bg-pink-500 text-white px-1 py-0.5 rounded text-xs">{{ $tagihanData['pinjaman_bank']->angsuran ?? 0 }}/{{ $tagihanData['pinjaman_bank']->lama_angsuran ?? 0 }}</span>
            </div>
            
            <!-- Jasa (1%) -->
            <div class="col-span-3">Jasa (1%):</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($tagihanData['pinjaman_bank']->jasa ?? 0, 0, ',', '.') }}</div>
            <div class="col-span-2"></div>
            
            <!-- Toserda -->
            <div class="col-span-3">Toserda:</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($tagihanData['toserda']->jumlah_bayar ?? 0, 0, ',', '.') }}</div>
            <div class="col-span-2"></div>
            
            <!-- Lain-lain -->
            <div class="col-span-3">Lain-lain:</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($tagihanData['lain_lain']->jumlah_bayar ?? 0, 0, ',', '.') }}</div>
            <div class="col-span-2"></div>
            
            <!-- Tag Bulan Lalu -->
            <div class="col-span-3 border-t border-green-200 pt-1">Tag Bulan Lalu:</div>
            <div class="col-span-5 border-t border-green-200 pt-1 font-bold text-right">{{ number_format($tagihanBulanLaluNew, 0, ',', '.') }}</div>
            <div class="col-span-2"></div>
            
            <!-- Pot Gaji -->
            <div class="col-span-3 font-bold">Pot Gaji:</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($potGaji, 0, ',', '.') }}</div>
            <div class="col-span-2"></div>
            
            <!-- Pot Simpanan -->
            <div class="col-span-3 font-bold">Pot Simpanan:</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($potSimpanan->jml_total ?? 0, 0, ',', '.') }}</div>
            <div class="col-span-2"></div>
            
            <!-- Tag Harus Dibayar -->
            <div class="col-span-3">Tag Harus Dibayar:</div>
            <div class="col-span-5 font-bold text-right">{{ number_format($tagHarusDibayar, 0, ',', '.') }}</div>
            <div class="col-span-2"></div>
        </div>
    </div>

    <!-- 5. Pengajuan Pinjaman Terbaru (Top Right) - col-span-3 row-span-3 col-start-7 row-start-1 -->
    @if($pengajuanPinjaman)
    <div class="col-span-3 row-span-3 col-start-7 row-start-1 bg-blue-100 rounded-lg p-4 border border-blue-200">
        <div class="flex flex-col h-full">
            <div class="flex items-center mb-3">
                <svg class="w-6 h-6 text-blue-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                    </path>
                </svg>
                <h3 class="text-lg font-semibold text-blue-800">Pengajuan Pinjaman Terbaru</h3>
            </div>
            <div class="flex-1 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">ID Ajuan:</span>
                    <span class="font-semibold">{{ $pengajuanPinjaman->ajuan_id }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Tanggal:</span>
                    <span
                        class="font-semibold">{{ \Carbon\Carbon::parse($pengajuanPinjaman->tgl_input)->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Jumlah:</span>
                    <span class="font-semibold">Rp {{ number_format($pengajuanPinjaman->nominal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span
                        class="px-2 py-1 text-xs rounded border {{ $pengajuanPinjaman->status == 0 ? 'bg-yellow-100 text-yellow-800 border-yellow-300' : ($pengajuanPinjaman->status == 1 ? 'bg-green-100 text-green-800 border-green-300' : ($pengajuanPinjaman->status == 2 ? 'bg-red-100 text-red-800 border-red-300' : ($pengajuanPinjaman->status == 3 ? 'bg-blue-100 text-blue-800 border-blue-300' : 'bg-gray-100 text-gray-800 border-gray-300'))) }}">
                        {{ $pengajuanPinjaman->status == 0 ? 'Menunggu Konfirmasi' : ($pengajuanPinjaman->status == 1 ? 'Disetujui' : ($pengajuanPinjaman->status == 2 ? 'Ditolak' : ($pengajuanPinjaman->status == 3 ? 'Sudah Terlaksana' : 'Batal'))) }}
                    </span>
                </div>
            </div>
            <div class="mt-3 flex space-x-2">
                <a href="{{ route('member.pengajuan.pinjaman.show', $pengajuanPinjaman->id) }}"
                    class="flex-1 bg-blue-500 hover:bg-blue-600 text-white text-center py-2 px-3 rounded text-sm transition-colors duration-200">
                    <i class="fas fa-eye mr-1"></i> Detail
                </a>
                @if($pengajuanPinjaman->status == 0)
                <form action="{{ route('member.pengajuan.pinjaman.cancel', $pengajuanPinjaman->id) }}" method="POST"
                    class="flex-1">
                    @csrf
                    <button type="submit" onclick="return confirm('Yakin ingin membatalkan pengajuan?')"
                        class="w-full bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded text-sm transition-colors duration-200">
                        <i class="fas fa-times mr-1"></i> Batal
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>
    @else
    <div class="col-span-3 row-span-3 col-start-7 row-start-1 bg-blue-100 rounded-lg p-4 border border-blue-200">
        <div class="flex flex-col items-center justify-center h-full text-center">
            <svg class="w-12 h-12 text-blue-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            <h3 class="text-lg font-semibold text-blue-800 mb-2">Belum ada Pengajuan Pinjaman</h3>
            <a href="{{ route('member.tambah.pengajuan.pinjaman') }}"
                class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded text-sm transition-colors duration-200">
                <i class="fas fa-plus mr-1"></i> Ajukan Pinjaman
            </a>
        </div>
    </div>
    @endif

    <!-- 6. Notifikasi Penarikan Simpanan (Bottom Right) - col-span-3 row-span-3 col-start-7 row-start-4 -->
    @if($pengajuanPenarikan)
    <div class="col-span-3 row-span-3 col-start-7 row-start-4 bg-yellow-100 rounded-lg p-4 border border-yellow-200">
        <div class="flex flex-col h-full">
            <div class="flex items-center mb-3">
                <svg class="w-6 h-6 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                    </path>
                </svg>
                <h3 class="text-lg font-semibold text-yellow-800">Penarikan Simpanan Terbaru</h3>
            </div>
            <div class="flex-1 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-600">ID Ajuan:</span>
                    <span class="font-semibold">{{ $pengajuanPenarikan->ajuan_id ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Tanggal:</span>
                    <span
                        class="font-semibold">{{ \Carbon\Carbon::parse($pengajuanPenarikan->tgl_input)->format('d/m/Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Jumlah:</span>
                    <span class="font-semibold">Rp
                        {{ number_format($pengajuanPenarikan->nominal, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Jenis:</span>
                    <span class="font-semibold">{{ $pengajuanPenarikan->jenis }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-600">Status:</span>
                    <span
                        class="px-2 py-1 text-xs rounded border {{ $pengajuanPenarikan->status == 0 ? 'bg-yellow-100 text-yellow-800 border-yellow-300' : ($pengajuanPenarikan->status == 1 ? 'bg-green-100 text-green-800 border-green-300' : ($pengajuanPenarikan->status == 2 ? 'bg-red-100 text-red-800 border-red-300' : ($pengajuanPenarikan->status == 3 ? 'bg-blue-100 text-blue-800 border-blue-300' : 'bg-gray-100 text-gray-800 border-gray-300'))) }}">
                        {{ $pengajuanPenarikan->status == 0 ? 'Menunggu Konfirmasi' : ($pengajuanPinjaman->status == 1 ? 'Disetujui' : ($pengajuanPinjaman->status == 2 ? 'Ditolak' : ($pengajuanPinjaman->status == 3 ? 'Sudah Terlaksana' : 'Batal'))) }}
                    </span>
                </div>
            </div>
            <div class="mt-3">
                <a href="{{ route('member.pengajuan.penarikan') }}"
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-white text-center py-2 px-3 rounded text-sm transition-colors duration-200 block">
                    <i class="fas fa-eye mr-1"></i> Lihat Detail
                </a>
            </div>
        </div>
    </div>
    @else
    <div class="col-span-3 row-span-3 col-start-7 row-start-4 bg-yellow-100 rounded-lg p-4 border border-yellow-200">
        <div class="flex flex-col items-center justify-center h-full text-center">
            <svg class="w-12 h-12 text-yellow-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                </path>
            </svg>
            <h3 class="text-lg font-semibold text-yellow-800 mb-2">Belum ada Penarikan Simpanan</h3>
            <a href="{{ route('member.pengajuan.penarikan') }}"
                class="bg-yellow-500 hover:bg-yellow-600 text-white px-4 py-2 rounded text-sm transition-colors duration-200">
                <i class="fas fa-money mr-1"></i> Ajukan Penarikan
            </a>
        </div>
    </div>
    @endif
</div>

<script>
function changePeriode(periode) {
    window.location.href = '{{ route("member.dashboard") }}?periode=' + periode;
}
</script>
@endsection