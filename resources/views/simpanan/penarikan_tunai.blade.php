@extends('layouts.app')

@section('title', 'Penarikan Tunai')
@section('sub-title', 'Penarikan Tunai')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Penarikan Tunai</h1>
        <div class="flex place-content-around items-center w-1/2">
            <div class="bg-green-100 p-2 rounded-lg border-2 border-green-400 space-x-2 flex justify-around">
                <p class="text-sm">Export</p> <img src="{{ asset('img/icons-bootstrap/export/cloud-download.svg') }}"
                    class="h-auto w-[20px]">
            </div>
            <div class="bg-gray-100 p-2 flex flex-row space-x-2 item-center rounded-lg border-2 border-gray-300">
                <i class="fa-solid fa-magnifying-glass" style="color:gray;"></i>
                <p class="text-sm text-gray-500">Cari Anggota</p>
            </div>
            <div class="bg-gray-100 p-3 flex flex-row item-center rounded-lg border-2 border-gray-300">
                <img src="{{ asset('img/icons-bootstrap/calendar/calendar4.svg') }}">
            </div>
            <div class="bg-green-100 py-2 px-5 rounded-lg border-2 border-green-400">
                <i class="fa-solid fa-ellipsis-vertical"></i>
            </div>
        </div>
    </div>

    <!-- Form Penarikan Tunai -->
    <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Form Penarikan Tunai</h2>
        </div>
        <div class="p-6">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            <form action="" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                        <input type="date" name="tgl_transaksi" value="{{ date('Y-m-d') }}" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No KTP</label>
                        <input type="text" name="no_ktp" id="no_ktp" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Masukkan No KTP">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Anggota</label>
                        <select name="anggota_id" id="anggota_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Pilih Anggota</option>
                            @foreach($dataAnggota as $anggota)
                                <option value="{{ $anggota->id }}" data-ktp="{{ $anggota->no_ktp }}">{{ $anggota->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jenis Simpanan</label>
                        <select name="jenis_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Pilih Jenis Simpanan</option>
                            @foreach($jenisSimpanan as $jenis)
                                <option value="{{ $jenis->id }}">{{ $jenis->nama_simpanan }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                        <input type="number" name="jumlah" step="0.01" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Masukkan jumlah">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <input type="text" name="keterangan"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Keterangan (opsional)">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Akun</label>
                        <input type="text" name="akun" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Masukkan akun">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">D/K</label>
                        <select name="dk" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Pilih D/K</option>
                            <option value="D">Debit (D)</option>
                            <option value="K">Kredit (K)</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Kas</label>
                        <select name="kas_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Pilih Kas</option>
                            @foreach($dataKas as $kas)
                                <option value="{{ $kas->id }}">{{ $kas->nama_kas }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Nama Penarik</label>
                        <input type="text" name="nama_penyetor" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Nama penarik">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">No Identitas</label>
                        <input type="text" name="no_identitas" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="No identitas">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Alamat</label>
                        <input type="text" name="alamat" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="Alamat">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ID Cabang</label>
                        <input type="text" name="id_cabang" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="ID Cabang">
                    </div>
                </div>

                <div class="mt-6 flex justify-end">
                    <button type="submit" class="bg-red-500 text-white px-6 py-2 rounded-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                        Simpan Penarikan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabel Riwayat Penarikan -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Riwayat Penarikan Tunai</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full border border-gray-300 text-center">
                <thead class="bg-gray-50">
                    <tr class="text-sm align-middle w-full">
                        <th class="py-2 px-5 border">No</th>
                        <th class="p-5 border whitespace-nowrap">ID</th>
                        <th class="p-5 border whitespace-nowrap">Tgl Transaksi</th>
                        <th class="p-5 border whitespace-nowrap">No KTP</th>
                        <th class="p-5 border whitespace-nowrap">Anggota ID</th>
                        <th class="p-5 border whitespace-nowrap">Jenis ID</th>
                        <th class="p-5 border whitespace-nowrap">Jumlah</th>
                        <th class="p-5 border whitespace-nowrap">Keterangan</th>
                        <th class="p-5 border whitespace-nowrap">Akun</th>
                        <th class="p-5 border whitespace-nowrap">D/K</th>
                        <th class="p-5 border whitespace-nowrap">Kas ID</th>
                        <th class="p-5 border whitespace-nowrap">Update Data</th>
                        <th class="p-5 border whitespace-nowrap">User Name</th>
                        <th class="p-5 border whitespace-nowrap">Nama Penyetor</th>
                        <th class="p-5 border whitespace-nowrap">No Identitas</th>
                        <th class="p-5 border whitespace-nowrap">Alamat</th>
                        <th class="p-5 border whitespace-nowrap">ID Cabang</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transaksiPenarikan as $transaksi)
                    <tr class="text-sm align-middle">
                        <td class="py-2 border">
                            {{ ($transaksiPenarikan->currentPage() - 1) * $transaksiPenarikan->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-2 border">{{ $transaksi->id }}</td>
                        <td class="py-2 border">{{ $transaksi->tgl_transaksi }}</td>
                        <td class="py-2 border">{{ $transaksi->no_ktp }}</td>
                        <td class="py-2 border">{{ $transaksi->anggota_id }}</td>
                        <td class="py-2 border">{{ $transaksi->jenis_id }}</td>
                        <td class="py-2 border">{{ number_format($transaksi->jumlah, 2) }}</td>
                        <td class="py-2 border">{{ $transaksi->keterangan }}</td>
                        <td class="py-2 border">{{ $transaksi->akun }}</td>
                        <td class="py-2 border">{{ $transaksi->dk }}</td>
                        <td class="py-2 border">{{ $transaksi->kas_id }}</td>
                        <td class="py-2 border">{{ $transaksi->update_data }}</td>
                        <td class="py-2 border">{{ $transaksi->user_name }}</td>
                        <td class="py-2 border">{{ $transaksi->nama_penyetor }}</td>
                        <td class="py-2 border">{{ $transaksi->no_identitas }}</td>
                        <td class="py-2 border">{{ $transaksi->alamat }}</td>
                        <td class="py-2 border">{{ $transaksi->id_cabang }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    
    <!-- Pagination -->
    <div class="mt-5 w-full relative px-2 py-2">
        <div class="mx-auto w-fit">
            <div
                class="bg-white px-4 py-1 flex flex-row rounded-full justify-center items-center space-x-2 border border-gray-300 shadow-sm">
                @for ($i = 1; $i <= $transaksiPenarikan->lastPage(); $i++)
                    @if ($i == 1 || $i == $transaksiPenarikan->lastPage() || ($i >= $transaksiPenarikan->currentPage() - 1 && $i
                    <= $transaksiPenarikan->
                        currentPage() + 1))
                        <a href="{{ $transaksiPenarikan->url($i) }}">
                            <div
                                class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $transaksiPenarikan->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </a>
                        @elseif ($i == 2 || $i == $transaksiPenarikan->lastPage() - 1)
                        <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                        @endif
                        @endfor
            </div>
        </div>

        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Displaying {{ $transaksiPenarikan->firstItem() }} to {{ $transaksiPenarikan->lastItem() }} of
            {{ $transaksiPenarikan->total() }}
            items
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Auto-fill anggota when KTP is entered
    const ktpInput = document.getElementById('no_ktp');
    const anggotaSelect = document.getElementById('anggota_id');

    ktpInput.addEventListener('input', function() {
        const ktpValue = this.value;
        const options = anggotaSelect.options;
        
        for (let i = 0; i < options.length; i++) {
            if (options[i].dataset.ktp === ktpValue) {
                anggotaSelect.selectedIndex = i;
                break;
            }
        }
    });

    // Auto-fill KTP when anggota is selected
    anggotaSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.dataset.ktp) {
            ktpInput.value = selectedOption.dataset.ktp;
        }
    });
});
</script>
@endsection
