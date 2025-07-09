@extends('layouts.app')

@section('title', 'Pembelian Toserda')
@section('sub-title', 'Form Pembelian')

@section('content')
<div class="container">
    <div class="bg-white rounded-lg shadow-lg p-6">
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        <form action="{{ route('toserda.store.pembelian') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="barang_id" class="block text-sm font-medium text-gray-700">Pilih Barang</label>
                    <select name="barang_id" id="barang_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        <option value="">Pilih Barang</option>
                        @foreach($barang as $item)
                        <option value="{{ $item->id }}" data-harga="{{ $item->harga_beli }}">
                            {{ $item->nama_barang }} - Stok: {{ $item->stok }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="jumlah" class="block text-sm font-medium text-gray-700">Jumlah</label>
                    <input type="number" name="jumlah" id="jumlah" min="1" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                </div>

                <div>
                    <label for="kas_id" class="block text-sm font-medium text-gray-700">Kas</label>
                    <select name="kas_id" id="kas_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        <option value="">Pilih Kas</option>
                        @foreach($kas as $item)
                        <option value="{{ $item->id }}">{{ $item->nama_kas }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50"></textarea>
                </div>

                <div>
                    <label for="total" class="block text-sm font-medium text-gray-700">Total</label>
                    <input type="text" id="total" readonly
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100">
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit"
                    class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50">
                    Simpan Pembelian
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('barang_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const hargaBeli = selectedOption.dataset.harga;
        hitungTotal();
    });

    document.getElementById('jumlah').addEventListener('input', hitungTotal);

    function hitungTotal() {
        const selectedOption = document.getElementById('barang_id').options[document.getElementById('barang_id').selectedIndex];
        const jumlah = document.getElementById('jumlah').value;
        const hargaBeli = selectedOption.dataset.harga;
        const total = jumlah * hargaBeli;
        document.getElementById('total').value = total.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR'
        });
    }
</script>
@endpush
@endsection 