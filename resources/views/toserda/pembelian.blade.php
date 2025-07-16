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

        @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
            <span class="block sm:inline">{{ session('error') }}</span>
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
                        <option value="{{ $item->id }}" data-harga="{{ $item->harga_beli ?? $item->harga ?? 0 }}">
                            {{ $item->nm_barang ?? 'Tanpa Nama' }} - Stok: {{ $item->stok ?? $item->jml_brg ?? 0 }}</option>
                        @endforeach
                    </select>
                    @error('barang_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="jumlah" class="block text-sm font-medium text-gray-700">Jumlah</label>
                    <input type="number" name="jumlah" id="jumlah" min="1" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                    @error('jumlah')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="kas_id" class="block text-sm font-medium text-gray-700">Kas</label>
                    <select name="kas_id" id="kas_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        <option value="">Pilih Kas</option>
                        @foreach($kas as $item)
                        <option value="{{ $item->id }}">{{ $item->nama ?? 'Tanpa Nama' }}</option>
                        @endforeach
                    </select>
                    @error('kas_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="2"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50"></textarea>
                    @error('keterangan')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
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

    <!-- Tabel Riwayat Transaksi Pembelian -->
    <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Riwayat Transaksi Pembelian</h2>
        
        <!-- Filter -->
        <div class="mb-6 p-4 border rounded-lg bg-gray-50">
            <form action="{{ route('toserda.pembelian') }}" method="GET" class="flex flex-wrap items-end gap-4">
                <div class="w-full sm:w-auto">
                    <label for="filter_tanggal_awal" class="block text-sm font-medium text-gray-700">Tanggal Awal</label>
                    <input type="date" name="tanggal_awal" id="filter_tanggal_awal" value="{{ request('tanggal_awal', date('Y-m-01')) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                </div>

                <div class="w-full sm:w-auto">
                    <label for="filter_tanggal_akhir" class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                    <input type="date" name="tanggal_akhir" id="filter_tanggal_akhir" value="{{ request('tanggal_akhir', date('Y-m-d')) }}"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                </div>

                <div class="w-full sm:w-auto">
                    <label for="filter_barang" class="block text-sm font-medium text-gray-700">Barang</label>
                    <select name="barang_id" id="filter_barang"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        <option value="">Semua Barang</option>
                        @foreach($barang as $item)
                        <option value="{{ $item->id }}" {{ request('barang_id') == $item->id ? 'selected' : '' }}>
                            {{ $item->nm_barang ?? 'Tanpa Nama' }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex-shrink-0">
                    <button type="submit"
                        class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50">
                        Filter
                    </button>
                    <a href="{{ route('toserda.pembelian') }}"
                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50 ml-2">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Tabel Data -->
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b text-left">Tanggal</th>
                        <th class="px-4 py-2 border-b text-left">Barang</th>
                        <th class="px-4 py-2 border-b text-left">Jumlah</th>
                        <th class="px-4 py-2 border-b text-left">Total</th>
                        <th class="px-4 py-2 border-b text-left">Kas</th>
                        <th class="px-4 py-2 border-b text-left">Keterangan</th>
                        <th class="px-4 py-2 border-b text-left">User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi ?? [] as $tr)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border-b">{{ $tr->tgl_transaksi ? $tr->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                        <td class="px-4 py-2 border-b">
                            {{ optional($tr->barang)->nm_barang ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-b">
                            {{ number_format($tr->jumlah ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 border-b">
                            {{ number_format(($tr->jumlah ?? 0) * (optional($tr->barang)->harga_beli ?? optional($tr->barang)->harga ?? 0), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 border-b">
                            {{ optional($tr->kas)->nama ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-b">{{ $tr->keterangan ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-4 py-4 text-center text-gray-500">Belum ada data transaksi pembelian</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            @if(isset($transaksi) && $transaksi instanceof \Illuminate\Pagination\LengthAwarePaginator)
                {{ $transaksi->links() }}
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.getElementById('barang_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        const hargaBeli = selectedOption ? (selectedOption.dataset.harga || 0) : 0;
        hitungTotal();
    });

    document.getElementById('jumlah').addEventListener('input', hitungTotal);

    function hitungTotal() {
        const selectedOption = document.getElementById('barang_id').options[document.getElementById('barang_id').selectedIndex];
        if (!selectedOption || selectedOption.value === '') {
            document.getElementById('total').value = 'Rp 0';
            return;
        }
        
        const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
        const hargaBeli = parseFloat(selectedOption.dataset.harga) || 0;
        const total = jumlah * hargaBeli;
        
        document.getElementById('total').value = total.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR'
        });
    }
</script>
@endpush
@endsection 