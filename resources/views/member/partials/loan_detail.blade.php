<div class="space-y-6">
    <!-- Loan Basic Info -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Informasi Pinjaman</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600">Tanggal Pinjaman</label>
                <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($loan->tgl_pinjam)->format('d M Y') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Jenis Pinjaman</label>
                <p class="text-sm text-gray-900">
                    @if($loan->jns_pinjaman == '1')
                        Pinjaman Biasa
                    @elseif($loan->jns_pinjaman == '2')
                        Pinjaman Barang
                    @else
                        {{ $loan->jns_pinjaman }}
                    @endif
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Jumlah Pinjaman</label>
                <p class="text-sm text-gray-900 font-semibold">Rp {{ number_format($loan->jumlah, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Lama Angsuran</label>
                <p class="text-sm text-gray-900">{{ $loan->lama_angsuran }} bulan</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Angsuran per Bulan</label>
                <p class="text-sm text-gray-900">Rp {{ number_format($loan->angsuran_per_bulan ?? 0, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Tanggal Jatuh Tempo</label>
                <p class="text-sm text-gray-900">{{ \Carbon\Carbon::parse($loan->tempo)->format('d M Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Financial Summary -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan Keuangan</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600">Pokok Pinjaman</label>
                <p class="text-sm text-gray-900">Rp {{ number_format($loan->jumlah, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Bunga</label>
                <p class="text-sm text-gray-900">Rp {{ number_format($loan->bunga_rp ?? 0, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Biaya Administrasi</label>
                <p class="text-sm text-gray-900">Rp {{ number_format($loan->biaya_adm ?? 0, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Total Tagihan</label>
                <p class="text-sm text-gray-900 font-semibold">Rp {{ number_format($total_tagihan_loan, 0, ',', '.') }}</p>
            </div>
        </div>
    </div>

    <!-- Payment Summary -->
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Ringkasan Pembayaran</h4>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-600">Total Dibayar</label>
                <p class="text-sm text-gray-900 font-semibold text-green-600">Rp {{ number_format($jml_bayar, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Sisa Tagihan</label>
                <p class="text-sm text-gray-900 font-semibold text-red-600">Rp {{ number_format($sisa_tagihan, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Total Denda</label>
                <p class="text-sm text-gray-900">Rp {{ number_format($jml_denda, 0, ',', '.') }}</p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-600">Status</label>
                <p class="text-sm text-gray-900">
                    @if($loan->lunas == 'Y')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            Lunas
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                            Belum Lunas
                        </span>
                    @endif
                </p>
            </div>
        </div>
    </div>

    <!-- Installment History -->
    @if($angsuran->count() > 0)
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Angsuran</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Bayar</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Pokok</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Bunga</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Denda</th>
                        <th class="px-3 py-2 text-left text-xs font-medium text-gray-500 uppercase">Total Bayar</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($angsuran as $index => $angsur)
                    <tr>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($angsur->tgl_bayar)->format('d M Y') }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($angsur->pokok ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($angsur->bunga ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900">
                            Rp {{ number_format($angsur->denda_rp ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap text-sm text-gray-900 font-semibold">
                            Rp {{ number_format($angsur->jumlah_bayar ?? 0, 0, ',', '.') }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-lg font-semibold text-gray-800 mb-4">Riwayat Angsuran</h4>
        <p class="text-gray-500 text-center py-4">Belum ada pembayaran angsuran</p>
    </div>
    @endif

    <!-- Keterangan -->
    @if($loan->keterangan)
    <div class="bg-gray-50 rounded-lg p-4">
        <h4 class="text-lg font-semibold text-gray-800 mb-2">Keterangan</h4>
        <p class="text-sm text-gray-700">{{ $loan->keterangan }}</p>
    </div>
    @endif
</div>
