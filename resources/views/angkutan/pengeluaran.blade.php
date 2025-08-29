@extends('layouts.app')

@section('title', 'Pengeluaran Angkutan')
@section('sub-title', 'Data Pengeluaran Angkutan Karyawan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pengeluaran</p>
                    <p class="text-2xl font-semibold text-gray-900">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $totalTransaksi }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow-lg p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Periode</p>
                    <p class="text-lg font-semibold text-gray-900">
                        {{ $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : 'Semua' }} - 
                        {{ $endDate ? \Carbon\Carbon::parse($endDate)->format('d M Y') : 'Semua' }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter and Actions -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <!-- Filter Form -->
            <form method="GET" class="flex flex-col lg:flex-row gap-4 flex-1">
                <div class="flex flex-col sm:flex-row gap-4 flex-1">
                    <div class="flex-1">
                        <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" value="{{ $startDate }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                    </div>
                    <div class="flex-1">
                        <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">Tanggal Akhir</label>
                        <input type="date" name="end_date" id="end_date" value="{{ $endDate }}"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                    </div>
                    <div class="flex-1">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                        <input type="text" name="search" id="search" value="{{ $search }}" placeholder="Kode Transaksi, Uraian..."
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                    </div>
                    <div class="flex-1">
                        <label for="kas_filter" class="block text-sm font-medium text-gray-700 mb-1">Filter Kas</label>
                        <select name="kas_filter" id="kas_filter"
                            class="w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                            <option value="">Semua Kas</option>
                            @foreach($kas as $k)
                            <option value="{{ $k->id }}" {{ $kasFilter == $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        Filter
                    </button>
                    <a href="{{ route('angkutan.pengeluaran') }}"
                        class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                        Reset
                    </a>
                </div>
            </form>

            <!-- Action Buttons -->
            <div class="flex gap-2">
                <button onclick="openModal()"
                    class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Tambah Pengeluaran
                </button>
                <a href="{{ route('angkutan.export.pdf.pengeluaran', request()->query()) }}"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-opacity-50">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export PDF
                </a>
                <a href="{{ route('angkutan.export.excel.pengeluaran', request()->query()) }}"
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-opacity-50">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Export Excel
                </a>
            </div>
        </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kode Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Transaksi</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Uraian</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dari Kas</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Akun</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jumlah</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($transaksi as $tr)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                            TKD{{ str_pad($tr->id, 6, '0', STR_PAD_LEFT) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $tr->tgl_catat->format('d F Y - H:i') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            {{ $tr->keterangan }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ optional($tr->dariKas)->nama }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            @php
                                $akunMap = [
                                    '55' => 'Beban Bahan Bakar',
                                    '56' => 'Beban Servis',
                                    '57' => 'Beban Parkir',
                                    '58' => 'Beban Tol',
                                    '59' => 'Beban Gaji Supir',
                                    '60' => 'Beban Gaji Kernet',
                                    '61' => 'Beban Asuransi',
                                    '62' => 'Beban Pajak',
                                    '63' => 'Beban Administrasi',
                                    '64' => 'Beban Lain-lain',
                                    '65' => 'Beban Perbaikan',
                                    '66' => 'Beban P3K',
                                    '67' => 'Beban Cuci',
                                    '68' => 'Beban Ban',
                                    '69' => 'Beban Oli'
                                ];
                                echo $akunMap[$tr->jns_trans] ?? 'Akun Lain';
                            @endphp
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-red-600">
                            Rp {{ number_format($tr->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $tr->user_name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            <button onclick="editTransaksi({{ $tr->id }})" class="text-indigo-600 hover:text-indigo-900 mr-2">Edit</button>
                            <button onclick="deleteTransaksi({{ $tr->id }})" class="text-red-600 hover:text-red-900">Hapus</button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                            Tidak ada data pengeluaran angkutan
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($transaksi->hasPages())
        <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
            {{ $transaksi->appends(request()->query())->links() }}
        </div>
        @endif
    </div>
</div>

<!-- Modal Form -->
<div id="modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
        <div class="mt-3">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900" id="modal-title">Tambah Pengeluaran Angkutan</h3>
                <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                <span class="block sm:inline">{{ session('success') }}</span>
            </div>
            @endif

            <form id="transaksiForm" action="{{ route('angkutan.store.pengeluaran') }}" method="POST" class="space-y-4">
                @csrf
                    <div>
                        <label for="tgl_catat" class="block text-sm font-medium text-gray-700">Tanggal Transaksi</label>
                        <input type="datetime-local" name="tgl_catat" id="tgl_catat" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                    </div>

                    <div>
                    <label for="keterangan" class="block text-sm font-medium text-gray-700">Uraian</label>
                    <textarea name="keterangan" id="keterangan" rows="3" required
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50"
                        placeholder="Masukkan uraian transaksi..."></textarea>
                </div>

                <div>
                    <label for="jns_trans" class="block text-sm font-medium text-gray-700">Jenis Pengeluaran</label>
                    <select name="jns_trans" id="jns_trans" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                        <option value="">Pilih Jenis Pengeluaran</option>
                        <option value="55">Beban Bahan Bakar</option>
                        <option value="56">Beban Servis</option>
                        <option value="57">Beban Parkir</option>
                        <option value="58">Beban Tol</option>
                        <option value="59">Beban Gaji Supir</option>
                        <option value="60">Beban Gaji Kernet</option>
                        <option value="61">Beban Asuransi</option>
                        <option value="62">Beban Pajak</option>
                        <option value="63">Beban Administrasi</option>
                        <option value="64">Beban Lain-lain</option>
                        <option value="65">Beban Perbaikan</option>
                        <option value="66">Beban P3K</option>
                        <option value="67">Beban Cuci</option>
                        <option value="68">Beban Ban</option>
                        <option value="69">Beban Oli</option>
                        </select>
                    </div>

                    <div>
                        <label for="dari_kas_id" class="block text-sm font-medium text-gray-700">Dari Kas</label>
                        <select name="dari_kas_id" id="dari_kas_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                            <option value="">Pilih Kas Asal</option>
                            @foreach($kas as $item)
                        <option value="{{ $item->id }}">{{ $item->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label for="untuk_kas_id" class="block text-sm font-medium text-gray-700">Untuk Kas</label>
                        <select name="untuk_kas_id" id="untuk_kas_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                            <option value="">Pilih Kas Tujuan</option>
                            @foreach($kas as $item)
                        <option value="{{ $item->id }}">{{ $item->nama }}</option>
                            @endforeach
                        </select>
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

                <div class="flex justify-end gap-2 pt-4">
                    <button type="button" onclick="closeModal()"
                        class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // Set default datetime to now
    document.getElementById('tgl_catat').value = new Date().toISOString().slice(0, 16);

    function openModal() {
        document.getElementById('modal').classList.remove('hidden');
        document.getElementById('modal-title').textContent = 'Tambah Pengeluaran Angkutan';
        document.getElementById('transaksiForm').reset();
        document.getElementById('transaksiForm').action = '{{ route("angkutan.store.pengeluaran") }}';
    }

    function closeModal() {
        document.getElementById('modal').classList.add('hidden');
    }

    function editTransaksi(id) {
        // Implement edit functionality
        alert('Edit transaksi dengan ID: ' + id);
    }

    function deleteTransaksi(id) {
        if (confirm('Apakah Anda yakin ingin menghapus transaksi ini?')) {
            // Implement delete functionality
            alert('Hapus transaksi dengan ID: ' + id);
        }
    }

    // Close modal when clicking outside
    document.getElementById('modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeModal();
        }
    });
</script>
@endpush
@endsection 