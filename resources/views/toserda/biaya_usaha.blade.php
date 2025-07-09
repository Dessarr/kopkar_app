@extends('layouts.app')

@section('title', 'Biaya Usaha Toserda')
@section('sub-title', 'Form Biaya Usaha')

@section('content')
<div class="container">
    <div class="bg-white rounded-lg shadow-lg p-6">
        @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('success') }}</span>
        </div>
        @endif

        <form action="{{ route('toserda.store.biaya-usaha') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan Biaya</label>
                    <textarea name="keterangan" id="keterangan" rows="3" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50"
                        placeholder="Masukkan keterangan biaya..."></textarea>
                </div>

                <div>
                    <label for="jumlah" class="block text-sm font-medium text-gray-700">Jumlah Biaya</label>
                    <input type="number" name="jumlah" id="jumlah" min="0" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50"
                        placeholder="0">
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
                    <label for="total_format" class="block text-sm font-medium text-gray-700">Total</label>
                    <input type="text" id="total_format" readonly
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm bg-gray-100">
                </div>
            </div>

            <div class="flex justify-end mt-6">
                <button type="submit"
                    class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50">
                    Simpan Biaya Usaha
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('jumlah').addEventListener('input', function() {
        const jumlah = this.value;
        document.getElementById('total_format').value = parseFloat(jumlah).toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR'
        });
    });
</script>
@endpush
@endsection 