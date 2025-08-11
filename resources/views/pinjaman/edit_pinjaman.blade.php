@extends('layouts.app')

@section('title', 'Edit Pinjaman')
@section('sub-title', 'Edit Data Pinjaman')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Edit Data Pinjaman</h1>
        <a href="{{ route('pinjaman.data_pinjaman') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
            ← Kembali
        </a>
    </div>

    @if (session('success'))
        <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Edit Data Pinjaman - {{ $pinjaman->id }}</h2>
        
        <form action="{{ route('pinjaman.data_pinjaman.update', $pinjaman->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Informasi Anggota (Read Only) -->
                <div class="md:col-span-2">
                    <h3 class="text-md font-medium text-gray-700 mb-3">Informasi Anggota</h3>
                    <div class="bg-gray-50 p-4 rounded-lg">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Nama Anggota</label>
                                <p class="text-sm text-gray-900">{{ $pinjaman->anggota->nama ?? '-' }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">ID Anggota</label>
                                <p class="text-sm text-gray-900">{{ $pinjaman->anggota_id }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Data Pinjaman -->
                <div>
                    <label for="jumlah" class="block text-sm font-medium text-gray-700">Jumlah Pinjaman</label>
                    <input type="number" name="jumlah" id="jumlah" value="{{ $pinjaman->jumlah }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                           readonly>
                    <p class="mt-1 text-sm text-gray-500">Jumlah pinjaman tidak dapat diubah</p>
                </div>

                <div>
                    <label for="lama_angsuran" class="block text-sm font-medium text-gray-700">Lama Angsuran (Bulan)</label>
                    <input type="number" name="lama_angsuran" id="lama_angsuran" value="{{ $pinjaman->lama_angsuran }}" 
                           min="1" max="60"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <div>
                    <label for="bunga" class="block text-sm font-medium text-gray-700">Bunga (%)</label>
                    <input type="number" name="bunga" id="bunga" value="{{ $pinjaman->bunga }}" 
                           min="0" max="100" step="0.01"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <div>
                    <label for="biaya_adm" class="block text-sm font-medium text-gray-700">Biaya Admin</label>
                    <input type="number" name="biaya_adm" id="biaya_adm" value="{{ $pinjaman->biaya_adm }}" 
                           min="0" step="1000"
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>

                <div class="md:col-span-2">
                    <label for="keterangan" class="block text-sm font-medium text-gray-700">Keterangan</label>
                    <textarea name="keterangan" id="keterangan" rows="3" 
                              class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500"
                              placeholder="Keterangan pinjaman...">{{ $pinjaman->keterangan }}</textarea>
                </div>

                <!-- Preview Perhitungan -->
                <div class="md:col-span-2">
                    <h3 class="text-md font-medium text-gray-700 mb-3">Preview Perhitungan</h3>
                    <div class="bg-blue-50 p-4 rounded-lg">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-blue-700">Angsuran Pokok</label>
                                <p class="text-lg font-semibold text-blue-900" id="preview-pokok">
                                    Rp {{ number_format($pinjaman->jumlah / $pinjaman->lama_angsuran, 0, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-blue-700">Bunga per Bulan</label>
                                <p class="text-lg font-semibold text-blue-900" id="preview-bunga">
                                    Rp {{ number_format(($pinjaman->bunga * $pinjaman->jumlah / $pinjaman->lama_angsuran) / 100, 0, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-blue-700">Angsuran per Bulan</label>
                                <p class="text-lg font-semibold text-blue-900" id="preview-total">
                                    Rp {{ number_format($pinjaman->jumlah_angsuran, 0, ',', '.') }}
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-blue-700">Total Tagihan</label>
                                <p class="text-lg font-semibold text-blue-900" id="preview-tagihan">
                                    Rp {{ number_format($pinjaman->jumlah + $pinjaman->biaya_adm, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mt-6 flex justify-end space-x-3">
                <a href="{{ route('pinjaman.data_pinjaman') }}" 
                   class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-lg">
                    Batal
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    Update Pinjaman
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Real-time calculation preview
document.addEventListener('DOMContentLoaded', function() {
    const jumlah = document.getElementById('jumlah');
    const lamaAngsuran = document.getElementById('lama_angsuran');
    const bunga = document.getElementById('bunga');
    const biayaAdm = document.getElementById('biaya_adm');
    
    function updatePreview() {
        const jumlahVal = parseFloat(jumlah.value) || 0;
        const lamaVal = parseFloat(lamaAngsuran.value) || 1;
        const bungaVal = parseFloat(bunga.value) || 0;
        const biayaVal = parseFloat(biayaAdm.value) || 0;
        
        // Hitung angsuran pokok
        const pokok = jumlahVal / lamaVal;
        document.getElementById('preview-pokok').textContent = 'Rp ' + pokok.toLocaleString('id-ID');
        
        // Hitung bunga per bulan
        const bungaPerBulan = (bungaVal * pokok) / 100;
        document.getElementById('preview-bunga').textContent = 'Rp ' + bungaPerBulan.toLocaleString('id-ID');
        
        // Hitung total angsuran per bulan
        const totalAngsuran = pokok + bungaPerBulan;
        document.getElementById('preview-total').textContent = 'Rp ' + totalAngsuran.toLocaleString('id-ID');
        
        // Hitung total tagihan
        const totalTagihan = jumlahVal + biayaVal;
        document.getElementById('preview-tagihan').textContent = 'Rp ' + totalTagihan.toLocaleString('id-ID');
    }
    
    // Event listeners
    lamaAngsuran.addEventListener('input', updatePreview);
    bunga.addEventListener('input', updatePreview);
    biayaAdm.addEventListener('input', updatePreview);
    
    // Initial calculation
    updatePreview();
});
</script>
@endsection
