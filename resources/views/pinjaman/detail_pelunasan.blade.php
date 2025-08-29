@extends('layouts.app')

@section('title', 'Detail Pelunasan')
@section('sub-title', 'Detail Pelunasan Pinjaman')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Detail Pelunasan</h1>
        <a href="{{ route('pinjaman.pelunasan') }}" class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
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

    <!-- Rangkuman Tagihan -->
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Rangkuman Tagihan</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-blue-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-blue-700">Total Tagihan</label>
                <p class="text-2xl font-bold text-blue-900">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</p>
            </div>
            <div class="bg-green-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-green-700">Sudah Dibayar</label>
                <p class="text-2xl font-bold text-green-900">Rp {{ number_format($totalTagihan - $sisaTagihan, 0, ',', '.') }}</p>
            </div>
            <div class="bg-yellow-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-yellow-700">Total Denda</label>
                <p class="text-2xl font-bold text-yellow-900">Rp {{ number_format($totalDenda, 0, ',', '.') }}</p>
            </div>
            <div class="bg-red-50 p-4 rounded-lg">
                <label class="block text-sm font-medium text-red-700">Sisa Tagihan</label>
                <p class="text-2xl font-bold text-red-900">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</p>
            </div>
        </div>
        
        <!-- Detail Total Tagihan -->
        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
            <h3 class="text-md font-medium text-gray-700 mb-2">Detail Total Tagihan</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-600">Pokok Pinjaman:</span>
                    <span class="font-medium">Rp {{ number_format($pinjaman->jumlah, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Total Bunga:</span>
                    <span class="font-medium">Rp {{ number_format(($pinjaman->bunga * $pinjaman->jumlah * $pinjaman->lama_angsuran) / 100, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Biaya Admin:</span>
                    <span class="font-medium">Rp {{ number_format($pinjaman->biaya_adm, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-gray-600">Total Tagihan:</span>
                    <span class="font-medium text-lg text-red-600">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Pelunasan -->
    @if($pinjaman->lunas === 'Belum')
    <div class="bg-white rounded-lg shadow-md p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Form Pelunasan</h2>
        
        <!-- Peringatan -->
        <div class="mb-4 p-3 bg-yellow-100 border border-yellow-400 text-yellow-700 rounded">
            <i class="fas fa-exclamation-triangle mr-2"></i>
            <strong>Peringatan:</strong> Pelunasan akan mengubah status pinjaman menjadi "Lunas" dan tidak bisa dibayar angsuran lagi.
        </div>
        
        <form action="{{ route('pinjaman.pelunasan.store', $pinjaman->id) }}" method="POST" id="formPelunasan">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="tgl_bayar" class="block text-sm font-medium text-gray-700">Tanggal Bayar</label>
                    <input type="date" name="tgl_bayar" id="tgl_bayar" value="{{ date('Y-m-d') }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" required>
                </div>
                <div>
                    <label for="jumlah_bayar" class="block text-sm font-medium text-gray-700">Jumlah Bayar</label>
                    <input type="number" name="jumlah_bayar" id="jumlah_bayar" value="{{ $sisaTagihan }}" 
                           class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500" 
                           min="{{ $sisaTagihan }}" required>
                    <p class="mt-1 text-xs text-gray-500">Minimal: Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</p>
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
                              placeholder="Keterangan pelunasan..."></textarea>
                </div>
            </div>
            
            <!-- Preview Total Pembayaran -->
            <div class="mt-4 p-4 bg-green-50 rounded-lg">
                <h3 class="text-md font-medium text-green-700 mb-2">Preview Total Pembayaran</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                    <div>
                        <span class="text-green-600">Jumlah Bayar:</span>
                        <span class="font-medium" id="preview-jumlah">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-green-600">Sisa Tagihan:</span>
                        <span class="font-medium">Rp {{ number_format($sisaTagihan, 0, ',', '.') }}</span>
                    </div>
                    <div>
                        <span class="text-green-600">Status:</span>
                        <span class="font-medium text-green-600">Lunas</span>
                    </div>
                    <div>
                        <span class="text-green-600">Aksi:</span>
                        <span class="font-medium text-green-600">Pelunasan</span>
                    </div>
                </div>
            </div>
            
            <div class="mt-6">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                    <i class="fas fa-check mr-2"></i>Lunasi Pinjaman
                </button>
            </div>
        </form>
    </div>
    @endif

    <!-- Riwayat Pembayaran -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Riwayat Pembayaran</h2>
        
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis</th>
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
                        <td class="px-6 py-4 whitespace-nowrap text-sm">
                            @if($angsuran->ket_bayar === 'Pelunasan')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded-full">Pelunasan</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-blue-100 text-blue-800 rounded-full">Angsuran</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-8">
            <p class="text-gray-500">Belum ada pembayaran</p>
        </div>
        @endif
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Real-time calculation preview
    const jumlahBayar = document.getElementById('jumlah_bayar');
    const sisaTagihan = {{ $sisaTagihan }};
    
    if (jumlahBayar) {
        jumlahBayar.addEventListener('input', function() {
            const jumlahVal = parseFloat(this.value) || 0;
            document.getElementById('preview-jumlah').textContent = 'Rp ' + jumlahVal.toLocaleString('id-ID');
            
            // Validasi minimal pembayaran
            if (jumlahVal < sisaTagihan) {
                this.setCustomValidity('Jumlah pembayaran tidak mencukupi untuk melunasi pinjaman');
            } else {
                this.setCustomValidity('');
            }
        });
    }
    
    // Form validation
    const formPelunasan = document.getElementById('formPelunasan');
    if (formPelunasan) {
        formPelunasan.addEventListener('submit', function(e) {
            const jumlahBayar = parseFloat(document.getElementById('jumlah_bayar').value) || 0;
            
            if (jumlahBayar < sisaTagihan) {
                e.preventDefault();
                alert('Jumlah pembayaran tidak mencukupi untuk melunasi pinjaman. Minimal: Rp ' + sisaTagihan.toLocaleString('id-ID'));
                return false;
            }
            
            if (!confirm('Apakah Anda yakin ingin melunasi pinjaman ini? Status akan berubah menjadi "Lunas" dan tidak bisa dibayar angsuran lagi.')) {
                e.preventDefault();
                return false;
            }
        });
    }
});
</script>
@endsection
