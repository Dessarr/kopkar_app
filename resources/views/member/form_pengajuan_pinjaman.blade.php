@extends('layouts.member')

@section('title', 'Form Pengajuan Pinjaman')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Formulir Pengajuan Pinjaman</h1>
            <a href="{{ route('member.pengajuan.pinjaman') }}" class="text-gray-600 hover:text-gray-800">
                <i class="fas fa-arrow-left mr-2"></i> Kembali
            </a>
        </div>

        <div class="bg-white p-6">
            @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}
            </div>
            @endif
            @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif
            <form id="loan-form" method="POST" action="{{ route('member.pengajuan.pinjaman.store') }}">
                @csrf
                <meta name="csrf-token" content="{{ csrf_token() }}">

                <!-- Jenis Pinjaman -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="jenis_pinjaman">
                        Jenis Pinjaman
                    </label>
                    <select
                        class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                        id="jenis_pinjaman" name="jenis_pinjaman" required>
                        <option value="">-PILIH-</option>
                        @foreach($jenisPinjaman as $jp)
                        <option value="{{ $jp->id }}">{{ $jp->pinjaman }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Bunga % -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="bunga">
                        Bunga %
                    </label>
                    <input type="text"
                        class="form-input w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 cursor-not-allowed"
                        id="bunga" name="bunga" value="0" readonly disabled>
                </div>


                <!-- Lama Angsuran -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="lama_angsuran">
                        Lama Angsuran
                    </label>
                    <select
                        class="form-select w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                        id="lama_angsuran" name="lama_angsuran" required>
                        <option value="3">3 bulan</option>
                        <option value="6">6 bulan</option>
                        <option value="12">12 bulan</option>
                        <option value="24">24 bulan</option>
                        <option value="36">36 bulan</option>
                        <option value="48">48 bulan</option>
                        <option value="60">60 bulan</option>
                    </select>
                </div>

                <!-- Nominal -->
                <div class="mb-4">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="nominal">
                        Nominal
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                        <input type="text"
                            class="form-input w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                            id="nominal" name="nominal" placeholder="Masukkan nominal pinjaman" required>
                    </div>
                </div>



                <!-- Keterangan -->
                <div class="mb-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="keterangan">
                        Keterangan
                    </label>
                    <textarea
                        class="form-textarea w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:border-transparent"
                        id="keterangan" name="keterangan" rows="4" placeholder="Masukkan keterangan"
                        required></textarea>
                    <p class="text-sm text-gray-600 mt-1">*Harus diisi</p>
                </div>

                <!-- Submit Button (always visible) -->
                <div class="flex justify-end" id="submit-button-container">
                    <button type="submit"
                        class="bg-[#14AE5C] text-white px-4 py-2 rounded-lg hover:bg-[#14AE5C]/80 transition duration-200">
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        </div>

        <!-- Loan Simulation Table (hidden initially) -->
        <div id="loan-simulation-table" class="hidden mt-8">
            <div class="bg-gradient-to-r from-[#14AE5C] to-[#14AE5C]/80 rounded-t-lg p-4">
                <h2 class="text-xl font-semibold text-white flex items-center">
                    <i class="fas fa-calculator mr-2"></i>
                    Simulasi Pinjaman
                </h2>
            </div>
            <div class="overflow-x-auto border border-gray-200 rounded-b-lg shadow-lg">
                <table class="min-w-full bg-white">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                Angsuran Ke</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                Tanggal Tempo</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                Angsuran Pokok</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                Biaya Bunga</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider border-r border-gray-200">
                                Biaya Admin</th>
                            <th
                                class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Jumlah Tagihan</th>
                        </tr>
                    </thead>
                    <tbody id="simulation-table-body" class="bg-white divide-y divide-gray-200">
                        <!-- Simulation data will be populated here -->
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script src="{{ asset('js/loan-simulation.js') }}" defer></script>
@endpush