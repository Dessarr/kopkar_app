@extends('layouts.app')

@section('title', 'Pemasukan Angkutan')
@section('sub-title', 'Form Pemasukan')

@section('content')
<div class="container">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Form Pemasukan -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            <form action="{{ route('angkutan.store.pemasukan') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 gap-6">
                    <div>
                        <label for="id_mobil" class="block text-sm font-medium text-gray-700">Pilih Kendaraan</label>
                        <select name="id_mobil" id="id_mobil" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                            <option value="">Pilih Kendaraan</option>
                            @foreach($mobil as $item)
                            <option value="{{ $item->id }}">{{ $item->nama }} - {{ $item->no_polisi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="tgl_catat" class="block text-sm font-medium text-gray-700">Tanggal Transaksi</label>
                        <input type="datetime-local" name="tgl_catat" id="tgl_catat" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                    </div>

                    <div>
                        <label for="akun" class="block text-sm font-medium text-gray-700">Jenis Akun</label>
                        <select name="akun" id="akun" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                            <option value="">Pilih Jenis Akun</option>
                            @foreach($akun as $item)
                            <option value="{{ $item->id }}">{{ $item->nama_akun }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="dari_kas_id" class="block text-sm font-medium text-gray-700">Dari Kas</label>
                        <select name="dari_kas_id" id="dari_kas_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                            <option value="">Pilih Kas Asal</option>
                            @foreach($kas as $item)
                            <option value="{{ $item->id }}">{{ $item->nama_kas }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="untuk_kas_id" class="block text-sm font-medium text-gray-700">Untuk Kas</label>
                        <select name="untuk_kas_id" id="untuk_kas_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                            <option value="">Pilih Kas Tujuan</option>
                            @foreach($kas as $item)
                            <option value="{{ $item->id }}">{{ $item->nama_kas }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                        <textarea name="keterangan" id="keterangan" rows="3" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50"
                            placeholder="Masukkan keterangan transaksi..."></textarea>
                    </div>

                    <div>
                        <label for="jumlah" class="block text-sm font-medium text-gray-700">Jumlah</label>
                        <div class="mt-1 relative rounded-md shadow-sm">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" name="jumlah" id="jumlah" min="0" required
                                class="pl-12 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50"
                                placeholder="0">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end mt-6">
                    <button type="submit"
                        class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50">
                        Simpan Pemasukan
                    </button>
                </div>
            </form>
        </div>

        <!-- Riwayat Transaksi -->
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Riwayat Transaksi</h3>
            <div id="riwayat-transaksi" class="space-y-4">
                <p class="text-gray-500 text-center py-4">Pilih kendaraan untuk melihat riwayat transaksi</p>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Set default datetime to now
    document.getElementById('tgl_catat').value = new Date().toISOString().slice(0, 16);

    // Load transaction history when vehicle is selected
    document.getElementById('id_mobil').addEventListener('change', function() {
        const id_mobil = this.value;
        if (!id_mobil) {
            document.getElementById('riwayat-transaksi').innerHTML = '<p class="text-gray-500 text-center py-4">Pilih kendaraan untuk melihat riwayat transaksi</p>';
            return;
        }

        fetch(`/angkutan/transaksi?id_mobil=${id_mobil}`)
            .then(response => response.json())
            .then(data => {
                let html = '<div class="space-y-4">';
                if (data.length === 0) {
                    html = '<p class="text-gray-500 text-center py-4">Belum ada transaksi</p>';
                } else {
                    data.forEach(transaksi => {
                        const tanggal = new Date(transaksi.tgl_catat).toLocaleString('id-ID', {
                            dateStyle: 'medium',
                            timeStyle: 'short'
                        });
                        const jumlah = new Intl.NumberFormat('id-ID', {
                            style: 'currency',
                            currency: 'IDR'
                        }).format(transaksi.jumlah);

                        html += `
                            <div class="border-b pb-4">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <p class="font-medium">${transaksi.keterangan}</p>
                                        <p class="text-sm text-gray-500">${tanggal}</p>
                                        <p class="text-sm text-gray-500">Dari: ${transaksi.dari_kas.nama_kas}</p>
                                        <p class="text-sm text-gray-500">Ke: ${transaksi.untuk_kas.nama_kas}</p>
                                    </div>
                                    <p class="font-medium ${transaksi.dk === 'D' ? 'text-green-600' : 'text-red-600'}">${jumlah}</p>
                                </div>
                            </div>
                        `;
                    });
                }
                html += '</div>';
                document.getElementById('riwayat-transaksi').innerHTML = html;
            })
            .catch(error => {
                console.error('Error:', error);
                document.getElementById('riwayat-transaksi').innerHTML = '<p class="text-red-500 text-center py-4">Gagal memuat riwayat transaksi</p>';
            });
    });
</script>
@endpush
@endsection 