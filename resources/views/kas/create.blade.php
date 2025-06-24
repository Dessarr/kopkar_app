@extends('layouts.app')

@section('title', 'Tambah Transaksi Kas')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <h1 class="text-2xl font-bold">Tambah Transaksi Kas</h1>
    </div>

    <div class="bg-white rounded-lg shadow overflow-hidden">
        <form action="{{ route('kas.store') }}" method="POST" class="p-6">
            @csrf
            
            <!-- Jenis Transaksi -->
            <div class="mb-4">
                <label for="jenis_transaksi" class="block text-sm font-medium text-gray-700 mb-1">
                    Jenis Transaksi
                </label>
                <select name="jenis_transaksi" id="jenis_transaksi" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    required>
                    <option value="">Pilih Jenis Transaksi</option>
                    <option value="pemasukan">Pemasukan</option>
                    <option value="pengeluaran">Pengeluaran</option>
                    <option value="transfer">Transfer Antar Kas</option>
                </select>
                @error('jenis_transaksi')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kas Sumber -->
            <div class="mb-4">
                <label for="kas_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Kas Sumber
                </label>
                <select name="kas_id" id="kas_id" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    required>
                    <option value="">Pilih Kas</option>
                    @foreach($listKas as $kas)
                    <option value="{{ $kas->id }}">{{ $kas->nama }}</option>
                    @endforeach
                </select>
                @error('kas_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Kas Tujuan (untuk transfer) -->
            <div class="mb-4 hidden" id="kas_tujuan_div">
                <label for="untuk_kas_id" class="block text-sm font-medium text-gray-700 mb-1">
                    Kas Tujuan
                </label>
                <select name="untuk_kas_id" id="untuk_kas_id" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500">
                    <option value="">Pilih Kas Tujuan</option>
                    @foreach($listKas as $kas)
                    <option value="{{ $kas->id }}">{{ $kas->nama }}</option>
                    @endforeach
                </select>
                @error('untuk_kas_id')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Jumlah -->
            <div class="mb-4">
                <label for="jumlah" class="block text-sm font-medium text-gray-700 mb-1">
                    Jumlah
                </label>
                <div class="relative mt-1 rounded-md shadow-sm">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <span class="text-gray-500 sm:text-sm">Rp</span>
                    </div>
                    <input type="number" name="jumlah" id="jumlah" 
                        class="block w-full rounded-md border-gray-300 pl-12 focus:border-green-500 focus:ring-green-500"
                        placeholder="0" required min="0" step="0.01">
                </div>
                @error('jumlah')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Keterangan -->
            <div class="mb-6">
                <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-1">
                    Keterangan
                </label>
                <textarea name="keterangan" id="keterangan" rows="3" 
                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                    placeholder="Masukkan keterangan transaksi"></textarea>
                @error('keterangan')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Tombol Submit -->
            <div class="flex justify-end">
                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg">
                    Simpan Transaksi
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Toggle form kas tujuan berdasarkan jenis transaksi
    document.getElementById('jenis_transaksi').addEventListener('change', function() {
        const kasTujuanDiv = document.getElementById('kas_tujuan_div');
        const kasTujuanSelect = document.getElementById('untuk_kas_id');
        
        if (this.value === 'transfer') {
            kasTujuanDiv.classList.remove('hidden');
            kasTujuanSelect.required = true;
        } else {
            kasTujuanDiv.classList.add('hidden');
            kasTujuanSelect.required = false;
        }
    });
</script>
@endpush
@endsection 