@extends('layouts.app')

@section('title', 'Penjualan Toserda')
@section('sub-title', 'Form Penjualan')

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

        <form action="{{ route('toserda.store.penjualan') }}" method="POST" class="space-y-6">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="anggota_id" class="block text-sm font-medium text-gray-700">Pilih Anggota</label>
                    <select name="anggota_id" id="anggota_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        <option value="">Pilih Anggota</option>
                        @foreach($anggota as $item)
                        <option value="{{ $item->id }}">{{ $item->nama ?? 'Tanpa Nama' }} - {{ $item->no_ktp }}</option>
                        @endforeach
                    </select>
                    @error('anggota_id')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="barang_id" class="block text-sm font-medium text-gray-700">Pilih Barang</label>
                    <select name="barang_id" id="barang_id" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        <option value="">Pilih Barang</option>
                        @foreach($barang as $item)
                        <option value="{{ $item->id }}" data-harga="{{ $item->harga_jual ?? $item->harga ?? 0 }}" data-stok="{{ $item->stok ?? $item->jml_brg ?? 0 }}">
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
                    <p class="text-sm text-gray-500 mt-1">Stok tersedia: <span id="stok-tersedia">0</span></p>
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
                    Simpan Penjualan
                </button>
            </div>
        </form>
    </div>

    <!-- Tabel Riwayat Transaksi Penjualan -->
    <div class="mt-8 bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Riwayat Transaksi Penjualan</h2>
        
        <!-- Filter -->
        <div class="mb-6 p-4 border rounded-lg bg-gray-50">
            <form action="{{ route('toserda.penjualan') }}" method="GET" class="flex flex-wrap items-end gap-4">
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
                    <label for="search" class="block text-sm font-medium text-gray-700">Cari Anggota</label>
                    <input type="text" name="search" id="search" value="{{ request('search') }}" placeholder="Nama atau No KTP"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                </div>

                <div class="flex-shrink-0">
                    <button type="submit"
                        class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50">
                        Filter
                    </button>
                    <a href="{{ route('toserda.penjualan') }}"
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
                        <th class="px-4 py-2 border-b text-left">No KTP</th>
                        <th class="px-4 py-2 border-b text-left">Nama Anggota</th>
                        <th class="px-4 py-2 border-b text-left">Barang</th>
                        <th class="px-4 py-2 border-b text-left">Jumlah</th>
                        <th class="px-4 py-2 border-b text-left">Total</th>
                        <th class="px-4 py-2 border-b text-left">Keterangan</th>
                        <th class="px-4 py-2 border-b text-left">Status Billing</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi ?? [] as $tr)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border-b">{{ $tr->tgl_transaksi ? $tr->tgl_transaksi->format('d/m/Y') : '-' }}</td>
                        <td class="px-4 py-2 border-b">
                            {{ optional($tr->anggota)->no_ktp ?? $tr->no_ktp ?? '-' }}
                        </td>
                        <td class="px-4 py-2 border-b">
                            {{ optional($tr->anggota)->nama ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-b">
                            {{ optional($tr->barang)->nm_barang ?? 'N/A' }}
                        </td>
                        <td class="px-4 py-2 border-b">
                            {{ number_format($tr->jumlah ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 border-b">
                            {{ number_format(($tr->jumlah ?? 0) * (optional($tr->barang)->harga_jual ?? optional($tr->barang)->harga ?? 0), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-2 border-b">{{ $tr->keterangan ?? '-' }}</td>
                        <td class="px-4 py-2 border-b">
                            @if($tr->billing && $tr->billing->count() > 0)
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Sudah Billing</span>
                            @else
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Belum Billing</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-4 py-4 text-center text-gray-500">Belum ada data transaksi penjualan</td>
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
        const stok = selectedOption.dataset.stok || 0;
        const hargaJual = selectedOption.dataset.harga || 0;
        document.getElementById('stok-tersedia').textContent = stok;
        hitungTotal();
    });

    document.getElementById('jumlah').addEventListener('input', function() {
        const jumlah = parseInt(this.value) || 0;
        const stok = parseInt(document.getElementById('stok-tersedia').textContent) || 0;
        
        if (jumlah > stok) {
            alert('Jumlah melebihi stok tersedia!');
            this.value = stok;
        }
        
        hitungTotal();
    });

    function hitungTotal() {
        const selectedOption = document.getElementById('barang_id').options[document.getElementById('barang_id').selectedIndex];
        if (!selectedOption || selectedOption.value === '') {
            document.getElementById('total').value = 'Rp 0';
            return;
        }
        
        const jumlah = parseInt(document.getElementById('jumlah').value) || 0;
        const hargaJual = parseFloat(selectedOption.dataset.harga) || 0;
        const total = jumlah * hargaJual;
        
        document.getElementById('total').value = total.toLocaleString('id-ID', {
            style: 'currency',
            currency: 'IDR'
        });
    }
</script>
@endpush
@endsection 