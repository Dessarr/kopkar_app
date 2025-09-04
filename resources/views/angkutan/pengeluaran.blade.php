@extends('layouts.app')

@section('title', 'Pengeluaran Angkutan')
@section('sub-title', 'Data Pengeluaran Angkutan Karyawan')

@section('content')
<div class="px-1 justify-center flex flex-col">

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-money-bill-wave text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Pengeluaran</p>
                    <p class="text-2xl font-semibold text-gray-900">
                        Rp{{ number_format($totalPengeluaran, 0, ',', '.') }}
                    </p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-list text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Transaksi</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $transaksi->total() }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-calendar text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Periode Aktif</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ date('M Y') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Simple Filter Section -->
    <div class="bg-white rounded-lg shadow-md p-3 mb-4">
        <div class="flex items-center justify-between mb-2">
            <h3 class="text-base font-semibold text-gray-800">Filter Pengeluaran Angkutan</h3>
        </div>

        <form method="GET" action="{{ route('angkutan.pengeluaran') }}" id="filterForm">
            <!-- Simple Filter Bar -->
            <div class="flex flex-wrap items-center justify-between gap-2 py-2 px-2 bg-gray-50 rounded-lg">
                <!-- Left Side: Filter Controls -->
                <div class="flex items-center space-x-3">
                    <!-- 1. Tanggal -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Tanggal:</label>
                        <button type="button" id="daterange-btn"
                            class="px-3 py-1.5 border border-gray-300 rounded-lg bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 text-sm">
                            <i class="fas fa-calendar mr-1"></i>
                            <span id="daterange-text">Pilih Tanggal</span>
                            <i class="fas fa-chevron-down ml-1"></i>
                        </button>
                        <!-- Hidden inputs untuk form submission -->
                        <input type="hidden" name="tgl_dari" id="tgl_dari" value="{{ request('tgl_dari') }}">
                        <input type="hidden" name="tgl_sampai" id="tgl_sampai" value="{{ request('tgl_sampai') }}">
                    </div>

                    <!-- 2. Search Kode Transaksi -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Cari:</label>
                        <input type="text" name="kode_transaksi" id="kode_transaksi"
                            value="{{ request('kode_transaksi') }}" placeholder="[PK00001]"
                            class="px-2 py-1.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm w-36"
                            onkeypress="if(event.key==='Enter'){doSearch();}">
                    </div>

                    <!-- 3. Filter Kas -->
                    <div class="flex items-center space-x-2">
                        <label class="text-sm font-medium text-gray-700">Kas:</label>
                        <select name="kas_filter" id="kas_filter"
                            class="px-2 py-1.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500 text-sm w-32">
                            <option value="">Semua Kas</option>
                            @foreach($kas as $k)
                            <option value="{{ $k->id }}" {{ request('kas_filter') == $k->id ? 'selected' : '' }}>
                                {{ $k->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- 4. Button Filter -->
                    <button type="button" onclick="doSearch()" id="searchBtn"
                        class="px-3 py-1.5 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors text-sm">
                        <i class="fas fa-search mr-1"></i>Cari
                    </button>
                </div>

                <!-- Right Side: Action Buttons -->
                <div class="flex items-center space-x-2">
                    <!-- 5. Button Cetak Laporan -->
                    <button type="button" onclick="cetakLaporan()"
                        class="px-3 py-1.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors text-sm">
                        <i class="fas fa-print mr-1"></i>Cetak Laporan
                    </button>

                    <!-- 7. Button Hapus Filter -->
                    <button type="button" onclick="clearFilters()"
                        class="px-3 py-1.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors text-sm">
                        <i class="fas fa-times mr-1"></i>Hapus Filter
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="flex justify-between items-center mb-2 p-4">
            <h2 class="text-lg font-semibold text-gray-800">Data Pengeluaran Angkutan</h2>
            <div class="flex space-x-3">
                <button onclick="openModal('addModal')"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Tambah</span>
                </button>
                <button onclick="editData()"
                    class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-edit"></i>
                    <span>Edit</span>
                </button>
                <button onclick="deleteData()"
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-trash"></i>
                    <span>Hapus</span>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="table-auto w-full border border-gray-300 text-center">
                <thead class="bg-gray-100">
                    <tr class="text-sm">
                        <th class="py-3 border px-4">No</th>
                        <th class="py-3 border px-4">Kode Transaksi</th>
                        <th class="py-3 border px-4">Tanggal Transaksi</th>
                        <th class="py-3 border px-4">Uraian</th>
                        <th class="py-3 border px-4">Dari Kas</th>
                        <th class="py-3 border px-4">Akun</th>
                        <th class="py-3 border px-4">Jumlah</th>
                        <th class="py-3 border px-4">User</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksi as $index => $tr)
                    <tr class="text-sm align-middle hover:bg-gray-50 cursor-pointer row-selectable"
                        data-id="{{ $tr->id }}" data-kode="PK{{ str_pad($tr->id, 5, '0', STR_PAD_LEFT) }}"
                        data-tanggal="{{ $tr->tgl_catat }}" data-keterangan="{{ $tr->keterangan }}"
                        data-dari-kas-id="{{ $tr->dari_kas_id }}"
                        data-dari-kas-nama="{{ optional($tr->dariKas)->nama ?? '-' }}"
                        data-untuk-akun-id="{{ $tr->jns_trans }}" data-jumlah="{{ $tr->jumlah }}"
                        data-user="{{ $tr->user_name }}">
                        <td class="py-3 border px-4">
                            {{ ($transaksi->currentPage() - 1) * $transaksi->perPage() + $loop->iteration }}
                        </td>
                        <td class="py-3 border px-4">
                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded-full text-xs font-medium">
                                PK{{ str_pad($tr->id, 5, '0', STR_PAD_LEFT) }}
                            </span>
                        </td>
                        <td class="py-3 border px-4">
                            {{ $tr->tgl_catat ? \Carbon\Carbon::parse($tr->tgl_catat)->format('d F Y - H:i') : '-' }}
                        </td>
                        <td class="py-3 border px-4 text-left">{{ $tr->keterangan ?? '-' }}</td>
                        <td class="py-3 border px-4">{{ optional($tr->dariKas)->nama ?? '-' }}</td>
                        <td class="py-3 border px-4">
                            @php
                            $akunMap = [
                            5 => 'Piutang Usaha',
                            9 => 'Persediaan Awal Barang',
                            10 => 'Biaya Dibayar Dimuka',
                            11 => 'Perlengkapan Usaha',
                            18 => 'Peralatan Kantor',
                            19 => 'Inventaris Kendaraan',
                            20 => 'Mesin',
                            29 => 'Utang Usaha',
                            33 => 'Utang Pajak',
                            37 => 'Utang Bank',
                            42 => 'Modal Awal',
                            44 => 'Modal Sumbangan',
                            45 => 'Modal Cadangan',
                            50 => 'Beban',
                            53 => 'Biaya Listrik dan Air',
                            54 => 'Biaya Transportasi',
                            55 => 'Biaya Solar',
                            56 => 'Biaya Olie',
                            57 => 'Biaya Ban',
                            58 => 'Biaya Parkir',
                            59 => 'Biaya Perlengkapan',
                            60 => 'Biaya Lainnya',
                            61 => 'Biaya Transportasi',
                            62 => 'Biaya Perawatan',
                            63 => 'Biaya Penyusutan',
                            64 => 'Biaya THR',
                            65 => 'Biaya Keur',
                            66 => 'Biaya Sumbangan Karyawan',
                            67 => 'Biaya STNK',
                            68 => 'Biaya Angsuran Bus',
                            69 => 'Beban Gaji Pengemudi',
                            111 => 'Permisalan',
                            117 => 'Pembelian',
                            118 => 'Biaya Angkut Pembelian',
                            119 => 'Retur Pembelian',
                            120 => 'Potongan Pembelian',
                            121 => 'Persediaan Akhir Barang',
                            122 => 'Biaya Operasional',
                            123 => 'Bahan Habis Pakai',
                            124 => 'Insentive Karyawan',
                            152 => 'Beban Gaji Karyawan'
                            ];
                            echo $akunMap[$tr->jns_trans] ?? 'Akun Lain';
                            @endphp
                        </td>
                        <td class="py-3 border px-4 font-semibold text-red-600">
                            {{ number_format($tr->jumlah ?? 0, 0, ',', '.') }}
                        </td>
                        <td class="py-3 border px-4">{{ $tr->user_name ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-gray-500">
                            <i class="fas fa-inbox text-4xl mb-2"></i>
                            <p>Tidak ada data pengeluaran angkutan</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>

    <!-- Pagination -->
    <div class="mt-5 w-full relative px-2 py-2">
        <div class="mx-auto w-fit">
            <div
                class="bg-white px-4 py-1 flex flex-row rounded-full justify-center items-center space-x-2 border border-gray-300 shadow-sm">
                @for ($i = 1; $i <= $transaksi->lastPage(); $i++)
                    @if ($i == 1 || $i == $transaksi->lastPage() || ($i >= $transaksi->currentPage() - 1 && $i <=
                        $transaksi->currentPage() + 1))
                        <a href="{{ $transaksi->url($i) }}">
                            <div
                                class="rounded-md px-2 py-0.5 text-sm border border-gray-300 {{ $transaksi->currentPage() == $i ? 'bg-gray-100 font-bold' : '' }}">
                                {{ str_pad($i, 2, '0', STR_PAD_LEFT) }}
                            </div>
                        </a>
                        @elseif ($i == 2 || $i == $transaksi->lastPage() - 1)
                        <div class="rounded-md px-2 py-0.5 text-sm">...</div>
                        @endif
                        @endfor
            </div>
        </div>

        <div class="absolute right-4 top-1/2 -translate-y-1/2 whitespace-nowrap text-sm text-gray-400">
            Displaying {{ $transaksi->firstItem() }} to {{ $transaksi->lastItem() }} of {{ $transaksi->total() }} items
        </div>
    </div>
</div>

<!-- Add Modal -->
<div id="addModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Tambah Pengeluaran Angkutan</h3>
                <button onclick="closeModal('addModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="addForm" class="p-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                        <input type="datetime-local" name="tgl_catat" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                        <input type="text" name="jumlah" id="jumlah" required placeholder="Masukkan jumlah"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            oninput="formatNumber(this)" onblur="validateNumber(this)" pattern="[0-9,.]*"
                            inputmode="numeric">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea name="keterangan" rows="3" placeholder="Masukkan keterangan transaksi"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dari Kas</label>
                        <select name="dari_kas_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">-- Pilih Kas --</option>
                            @foreach($kas as $item)
                            <option value="{{ $item->id }}">{{ $item->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Untuk Akun</label>
                        <select name="untuk_akun_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">-- Pilih Jenis Akun --</option>
                            <option value="5">Piutang Usaha</option>
                            <option value="9">Persediaan Awal Barang</option>
                            <option value="10">Biaya Dibayar Dimuka</option>
                            <option value="11">Perlengkapan Usaha</option>
                            <option value="18">Peralatan Kantor</option>
                            <option value="19">Inventaris Kendaraan</option>
                            <option value="20">Mesin</option>
                            <option value="29">Utang Usaha</option>
                            <option value="33">Utang Pajak</option>
                            <option value="37">Utang Bank</option>
                            <option value="42">Modal Awal</option>
                            <option value="44">Modal Sumbangan</option>
                            <option value="45">Modal Cadangan</option>
                            <option value="50">Beban</option>
                            <option value="53">Biaya Listrik dan Air</option>
                            <option value="54">Biaya Transportasi</option>
                            <option value="55">Biaya Solar</option>
                            <option value="56">Biaya Olie</option>
                            <option value="57">Biaya Ban</option>
                            <option value="58">Biaya Parkir</option>
                            <option value="59">Biaya Perlengkapan</option>
                            <option value="60">Biaya Lainnya</option>
                            <option value="61">Biaya Transportasi</option>
                            <option value="62">Biaya Perawatan</option>
                            <option value="63">Biaya Penyusutan</option>
                            <option value="64">Biaya THR</option>
                            <option value="65">Biaya Keur</option>
                            <option value="66">Biaya Sumbangan Karyawan</option>
                            <option value="67">Biaya STNK</option>
                            <option value="68">Biaya Angsuran Bus</option>
                            <option value="69">Beban Gaji Pengemudi</option>
                            <option value="111">Permisalan</option>
                            <option value="117">Pembelian</option>
                            <option value="118">Biaya Angkut Pembelian</option>
                            <option value="119">Retur Pembelian</option>
                            <option value="120">Potongan Pembelian</option>
                            <option value="121">Persediaan Akhir Barang</option>
                            <option value="122">Biaya Operasional</option>
                            <option value="123">Bahan Habis Pakai</option>
                            <option value="124">Insentive Karyawan</option>
                            <option value="152">Beban Gaji Karyawan</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('addModal')"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 flex items-center space-x-2">
                        <i class="fas fa-check"></i>
                        <span>Simpan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div id="editModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md">
            <div class="flex items-center justify-between p-4 border-b">
                <h3 class="text-lg font-semibold">Edit Pengeluaran Angkutan</h3>
                <button onclick="closeModal('editModal')" class="text-gray-400 hover:text-gray-600">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <form id="editForm" class="p-4">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Transaksi</label>
                        <input type="datetime-local" name="tgl_catat" id="edit_tgl_catat" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Jumlah</label>
                        <input type="text" name="jumlah" id="edit_jumlah" required placeholder="Masukkan jumlah"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"
                            oninput="formatNumber(this)" onblur="validateNumber(this)" pattern="[0-9,.]*"
                            inputmode="numeric">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Keterangan</label>
                        <textarea name="keterangan" id="edit_keterangan" rows="3"
                            placeholder="Masukkan keterangan transaksi"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Dari Kas</label>
                        <select name="dari_kas_id" id="edit_dari_kas_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">-- Pilih Kas --</option>
                            @foreach($kas as $item)
                            <option value="{{ $item->id }}">{{ $item->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Untuk Akun</label>
                        <select name="untuk_akun_id" id="edit_untuk_akun_id" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">-- Pilih Jenis Akun --</option>
                            <option value="5">Piutang Usaha</option>
                            <option value="9">Persediaan Awal Barang</option>
                            <option value="10">Biaya Dibayar Dimuka</option>
                            <option value="11">Perlengkapan Usaha</option>
                            <option value="18">Peralatan Kantor</option>
                            <option value="19">Inventaris Kendaraan</option>
                            <option value="20">Mesin</option>
                            <option value="29">Utang Usaha</option>
                            <option value="33">Utang Pajak</option>
                            <option value="37">Utang Bank</option>
                            <option value="42">Modal Awal</option>
                            <option value="44">Modal Sumbangan</option>
                            <option value="45">Modal Cadangan</option>
                            <option value="50">Beban</option>
                            <option value="53">Biaya Listrik dan Air</option>
                            <option value="54">Biaya Transportasi</option>
                            <option value="55">Biaya Solar</option>
                            <option value="56">Biaya Olie</option>
                            <option value="57">Biaya Ban</option>
                            <option value="58">Biaya Parkir</option>
                            <option value="59">Biaya Perlengkapan</option>
                            <option value="60">Biaya Lainnya</option>
                            <option value="61">Biaya Transportasi</option>
                            <option value="62">Biaya Perawatan</option>
                            <option value="63">Biaya Penyusutan</option>
                            <option value="64">Biaya THR</option>
                            <option value="65">Biaya Keur</option>
                            <option value="66">Biaya Sumbangan Karyawan</option>
                            <option value="67">Biaya STNK</option>
                            <option value="68">Biaya Angsuran Bus</option>
                            <option value="69">Beban Gaji Pengemudi</option>
                            <option value="111">Permisalan</option>
                            <option value="117">Pembelian</option>
                            <option value="118">Biaya Angkut Pembelian</option>
                            <option value="119">Retur Pembelian</option>
                            <option value="120">Potongan Pembelian</option>
                            <option value="121">Persediaan Akhir Barang</option>
                            <option value="122">Biaya Operasional</option>
                            <option value="123">Bahan Habis Pakai</option>
                            <option value="124">Insentive Karyawan</option>
                            <option value="152">Beban Gaji Karyawan</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end space-x-3 mt-6">
                    <button type="button" onclick="closeModal('editModal')"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 flex items-center space-x-2">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </button>
                    <button type="submit"
                        class="px-6 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 flex items-center space-x-2">
                        <i class="fas fa-edit"></i>
                        <span>Update</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('angkutan.pengeluaran-scripts')
@endsection