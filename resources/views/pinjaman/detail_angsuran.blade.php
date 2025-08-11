@extends('layouts.app')

@section('title', 'Detail Angsuran')
@section('sub-title', 'Detail Angsuran Pinjaman')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Angsuran</h1>
        <a href="{{ route('pinjaman.data_angsuran') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            ← Kembali
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    <!-- Informasi Pinjaman -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Informasi Pinjaman</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Kode Pinjam</label>
                <p class="text-lg font-semibold text-gray-900">{{ $pinjaman->id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Anggota</label>
                <p class="text-lg font-semibold text-gray-900">{{ $pinjaman->anggota->nama ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Pinjam</label>
                <p class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($pinjaman->tgl_pinjam)->format('d/m/Y') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Jumlah Pinjaman</label>
                <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($pinjaman->jumlah, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Lama Angsuran</label>
                <p class="text-lg font-semibold text-gray-900">{{ $pinjaman->lama_angsuran }} Bulan</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Angsuran per Bulan</label>
                <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($pinjaman->jumlah_angsuran, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Bunga</label>
                <p class="text-lg font-semibold text-gray-900">{{ $pinjaman->bunga }}%</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Biaya Admin</label>
                <p class="text-lg font-semibold text-gray-900">Rp {{ number_format($pinjaman->biaya_adm, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Rangkuman Angsuran -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Rangkuman Angsuran</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-blue-700">Sisa Angsuran</label>
                <p class="text-2xl font-bold text-blue-900">{{ $pinjaman->lama_angsuran - $pinjaman->detail_angsuran->count() }} Bulan</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-green-700">Sudah Dibayar</label>
                <p class="text-2xl font-bold text-green-900">{{ $pinjaman->detail_angsuran->count() }}x</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-yellow-700">Total Denda</label>
                <p class="text-2xl font-bold text-yellow-900">Rp {{ number_format($pinjaman->detail_angsuran->sum('denda_rp'), 0, ',', '.') }}</p>
            </div>
            <div class="bg-purple-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-purple-700">Sisa Tagihan</label>
                <p class="text-2xl font-bold text-purple-900">Rp {{ number_format($sisaAngsuran, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Form Pembayaran Angsuran -->
    @if($pinjaman->lunas === 'Belum')
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Pembayaran Angsuran ke-{{ $angsuranKe }}</h2>
        
        <form action="{{ route('pinjaman.data_angsuran.store', $pinjaman->id) }}" method="POST">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="tgl_bayar" class="block text-sm font-medium text-gray-700">Tanggal Bayar</label>
                    <input type="date" name="tgl_bayar" id="tgl_bayar" value="{{ date('Y-m-d') }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                <div>
                    <label for="jumlah_bayar" class="block text-sm font-medium text-gray-700">Jumlah Bayar</label>
                    <input type="number" name="jumlah_bayar" id="jumlah_bayar" value="{{ $pinjaman->jumlah_angsuran }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                <div>
                    <label for="bunga" class="block text-sm font-medium text-gray-700">Bunga</label>
                    <input type="number" name="bunga" id="bunga" value="{{ ($pinjaman->bunga * $pinjaman->jumlah_angsuran) / 100 }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                <div>
                    <label for="biaya_adm" class="block text-sm font-medium text-gray-700">Biaya Admin</label>
                    <input type="number" name="biaya_adm" id="biaya_adm" value="{{ $pinjaman->biaya_adm / $pinjaman->lama_angsuran }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                <div>
                    <label for="denda_rp" class="block text-sm font-medium text-gray-700">Denda (jika ada)</label>
                    <input type="number" name="denda_rp" id="denda_rp" value="{{ $denda }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                <div>
                    <label for="kas_id" class="block text-sm font-medium text-gray-700">Kas</label>
                    <select name="kas_id" id="kas_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="">Pilih Kas</option>
                        <option value="1">Kas Tunai</option>
                        <option value="2">Kas Bank</option>
                    </select>
                </div>
                <div class="md:col-span-2">
                    <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Keterangan pembayaran angsuran..."></textarea>
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Simpan Pembayaran
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Tabel Riwayat Pembayaran -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Riwayat Pembayaran Angsuran</h2>
        
        @if($pinjaman->detail_angsuran->count() > 0)
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Angsuran Ke</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Bayar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah Bayar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Bunga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Denda</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Biaya Adm</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($pinjaman->detail_angsuran as $angsuran)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $angsuran->angsuran_ke }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ \Carbon\Carbon::parse($angsuran->tgl_bayar)->format('d/m/Y') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($angsuran->jumlah_bayar, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($angsuran->bunga, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($angsuran->denda_rp, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">Rp {{ number_format($angsuran->biaya_adm, 0, ',', '.') }}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <div class="flex space-x-2">
                                <a href="{{ route('pinjaman.data_angsuran.edit', $angsuran->id) }}" 
                                   class="text-indigo-600 hover:text-indigo-900">Edit</a>
                                <form action="{{ route('pinjaman.data_angsuran.destroy', $angsuran->id) }}" method="POST" 
                                      onsubmit="return confirm('Hapus angsuran ini?')" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8">
            <p class="text-gray-500">Belum ada pembayaran angsuran</p>
        </div>
        @endif
    </div>
</div>

<script>
// Auto-calculate denda if jatuh tempo
document.addEventListener('DOMContentLoaded', function() {
    const tglTempo = '{{ $tglTempo }}';
    const today = new Date();
    const tempoDate = new Date(tglTempo);
    
    if (today > tempoDate) {
        const diffTime = Math.abs(today - tempoDate);
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
        const denda = diffDays * 1000; // Denda per hari
        
        document.getElementById('denda_rp').value = denda;
    }
});
</script>
@endsection
