@extends('layouts.app')

@section('title', 'Edit Angsuran')
@section('sub-title', 'Edit Data Angsuran')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Data Angsuran</h1>
        <a href="{{ route('pinjaman.data_angsuran.show', $angsuran->pinjam_id) }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            ← Kembali
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    <!-- Informasi Angsuran -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Informasi Angsuran</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Angsuran Ke</label>
                <p class="text-lg font-semibold text-gray-900">{{ $angsuran->angsuran_ke }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Kode Pinjam</label>
                <p class="text-lg font-semibold text-gray-900">{{ $angsuran->pinjaman->id }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Nama Anggota</label>
                <p class="text-lg font-semibold text-gray-900">{{ $angsuran->pinjaman->anggota->nama ?? '-' }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Tanggal Bayar</label>
                <p class="text-lg font-semibold text-gray-900">{{ \Carbon\Carbon::parse($angsuran->tgl_bayar)->format('d/m/Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Form Edit Angsuran -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Edit Data Pembayaran</h2>
        
        <form action="{{ route('pinjaman.data_angsuran.update', $angsuran->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label for="tgl_bayar" class="block text-sm font-medium text-gray-700">Tanggal Bayar</label>
                    <input type="date" name="tgl_bayar" id="tgl_bayar" value="{{ \Carbon\Carbon::parse($angsuran->tgl_bayar)->format('Y-m-d') }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                
                <div>
                    <label for="jumlah_bayar" class="block text-sm font-medium text-gray-700">Jumlah Bayar</label>
                    <input type="number" name="jumlah_bayar" id="jumlah_bayar" value="{{ $angsuran->jumlah_bayar }}" 
                           min="0" step="1000"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                
                <div>
                    <label for="bunga" class="block text-sm font-medium text-gray-700">Bunga</label>
                    <input type="number" name="bunga" id="bunga" value="{{ $angsuran->bunga }}" 
                           min="0" step="1000"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                
                <div>
                    <label for="biaya_adm" class="block text-sm font-medium text-gray-700">Biaya Admin</label>
                    <input type="number" name="biaya_adm" id="biaya_adm" value="{{ $angsuran->biaya_adm }}" 
                           min="0" step="1000"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                
                <div>
                    <label for="denda_rp" class="block text-sm font-medium text-gray-700">Denda (jika ada)</label>
                    <input type="number" name="denda_rp" id="denda_rp" value="{{ $angsuran->denda_rp }}" 
                           min="0" step="1000"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                </div>
                
                <div>
                    <label for="kas_id" class="block text-sm font-medium text-gray-700">Kas</label>
                    <select name="kas_id" id="kas_id" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                        <option value="">Pilih Kas</option>
                        @foreach($kasList as $kas)
                        <option value="{{ $kas->id }}" {{ $angsuran->kas_id == $kas->id ? 'selected' : '' }}>
                            {{ $kas->nama_kas ?? 'Kas ' . $kas->id }}
                        </option>
                        @endforeach
                    </select>
                </div>
                
                <div class="md:col-span-2">
                    <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Keterangan pembayaran angsuran...">{{ $angsuran->keterangan }}</textarea>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('pinjaman.data_angsuran.show', $angsuran->pinjam_id) }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Update Angsuran
                </button>
            </div>
        </form>
    </div>

    <!-- Preview Perhitungan -->
    <div class="bg-white rounded-lg shadow-md p-6 mt-6">
        <h2 class="text-lg font-semibold mb-4">Preview Perhitungan</h2>
        <div class="bg-blue-50 p-4 rounded-lg">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-blue-700">Jumlah Bayar</label>
                    <p class="text-lg font-semibold text-blue-900" id="preview-jumlah">
                        Rp {{ number_format($angsuran->jumlah_bayar, 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-700">Bunga</label>
                    <p class="text-lg font-semibold text-blue-900" id="preview-bunga">
                        Rp {{ number_format($angsuran->bunga, 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-700">Biaya Admin</label>
                    <p class="text-lg font-semibold text-blue-900" id="preview-biaya">
                        Rp {{ number_format($angsuran->biaya_adm, 0, ',', '.') }}
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-blue-700">Total</label>
                    <p class="text-lg font-semibold text-blue-900" id="preview-total">
                        Rp {{ number_format($angsuran->jumlah_bayar + $angsuran->bunga + $angsuran->biaya_adm, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Real-time calculation preview
document.addEventListener('DOMContentLoaded', function() {
    const jumlahBayar = document.getElementById('jumlah_bayar');
    const bunga = document.getElementById('bunga');
    const biayaAdm = document.getElementById('biaya_adm');
    const denda = document.getElementById('denda_rp');
    
    function updatePreview() {
        const jumlahVal = parseFloat(jumlahBayar.value) || 0;
        const bungaVal = parseFloat(bunga.value) || 0;
        const biayaVal = parseFloat(biayaAdm.value) || 0;
        const dendaVal = parseFloat(denda.value) || 0;
        
        // Update preview values
        document.getElementById('preview-jumlah').textContent = 'Rp ' + jumlahVal.toLocaleString('id-ID');
        document.getElementById('preview-bunga').textContent = 'Rp ' + bungaVal.toLocaleString('id-ID');
        document.getElementById('preview-biaya').textContent = 'Rp ' + biayaVal.toLocaleString('id-ID');
        
        // Calculate total
        const total = jumlahVal + bungaVal + biayaVal + dendaVal;
        document.getElementById('preview-total').textContent = 'Rp ' + total.toLocaleString('id-ID');
    }
    
    // Event listeners
    jumlahBayar.addEventListener('input', updatePreview);
    bunga.addEventListener('input', updatePreview);
    biayaAdm.addEventListener('input', updatePreview);
    denda.addEventListener('input', updatePreview);
    
    // Initial calculation
    updatePreview();
});
</script>
@endsection
