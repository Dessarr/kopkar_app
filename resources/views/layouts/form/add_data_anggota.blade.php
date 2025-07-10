@extends('layouts.app')

@section('title', 'Data Anggota')
@section('sub-title', 'Data Anggota')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Data Anggota</h1>
        <div class="flex place-content-around items-center w-1/2">
            <div class="bg-green-100 p-2 rounded-lg border-2 border-green-400 space-x-2 flex justify-around">
                <p class="text-sm">Export</p> <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}"
                    class="h-auto w-[20px]">
            </div>
            <div class="bg-gray-100 p-2 flex flex-row space-x-2 item-center rounded-lg border-2 border-gray-300">
                <i class="fa-solid fa-magnifying-glass  " style="color:gray;"></i>
                <p class="text-sm text-gray-500  ">Kode Anggota</p>
            </div>

            <div class="bg-gray-100 p-3 flex flex-row item-center rounded-lg border-2 border-gray-300">
                <img src="{{ asset('img/icons-bootstrap/calendar/calendar4.svg') }}">
            </div>

            <div class="bg-green-100 py-2 px-5 rounded-lg border-2 border-green-400">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </div>
        </div>
    </div>

</div>


<!-- Form Setoran Tunai -->
<div class="bg-white rounded-lg shadow overflow-hidden mb-6 mt-5">
    <div class="p-4 border-b">
        <h2 class="text-lg font-semibold">Form Setoran Tunai</h2>
    </div>
    <div class="p-6">
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
            {{ session('success') }}
        </div>
        @endif

        <form action="{{ route('simpanan.setoran.store') }}" method="POST">
            @csrf
            <div class="flex flex-col gap-4">
                <div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                        <input type="date" name="tgl_transaksi" value="{{ date('Y-m-d') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>

                <div class="gap-4 flex flex-col">
                    <h2 class="font-bold">Identitas Penyetor</h2>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Penyetor</label>
                            <input type="text" name=" " id=" " required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="Masukkan Nama">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">No KTP</label>
                            <input type="number" name=" " id=" " required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="Masukkan No KTP">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                            <textarea
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">

                            </textarea>
                        </div>
                    </div>

                </div>

                <div class="gap-4 flex flex-col">
                    <h2 class="font-bold">Identitas Penerima</h2>
                    <div class="grid grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Anggota</label>
                            <select name=" " id=" " required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">Pilih Anggota</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Simpanan</label>
                            <select name=" " id=" " required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">Pilih Simpanan</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah Simpanan</label>
                            <input type="number"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="Masukan Jumlah Simpanan">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                            <input type="text" name=" " id=" " required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                                placeholder="Ket.">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Simpan ke Kas</label>
                            <select name=" " id=" " required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                                <option value="">-- pilih kas --</option>
                            </select>
                        </div>
                    </div>

                </div>


            </div>

            <div class="mt-6 flex justify-end gap-4">
                <button type="submit"
                    class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Simpan
                </button>
                <button type="submit"
                    class="bg-green-500 text-white px-6 py-2 rounded-md hover:bg-green-600 focus:outline-none focus:ring-2 focus:ring-green-500">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

@endsection