@extends('layouts.app')

@section('title', 'Bayar Toserda')
@section('sub-title', 'Pembayaran Toserda & Lain-lain')

@section('content')
<div class="container">
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

    <!-- Info Anggota -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Informasi Anggota</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <p class="text-sm text-gray-600">Nomor KTP</p>
                <p class="font-medium">{{ $anggota->no_ktp ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-600">Nama</p>
                <p class="font-medium">{{ $anggota->nama ?? 'N/A' }}</p>
            </div>
        </div>
    </div>

    <!-- Filter Periode -->
    <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Filter Transaksi</h2>
        <div class="flex flex-wrap items-center gap-4">
            <div class="w-full md:w-auto">
                <label for="period" class="block text-sm font-medium text-gray-700 mb-1">Pilih Periode</label>
                <select id="period" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-[#14AE5C] focus:ring focus:ring-[#14AE5C] focus:ring-opacity-50">
                    <option value="">Pilih Periode</option>
                    @foreach($transactionsByPeriod as $period => $transactions)
                        @php
                            $date = \Carbon\Carbon::createFromFormat('Y-m', $period);
                            $periodLabel = $date->format('F Y');
                        @endphp
                        <option value="{{ $period }}">{{ $periodLabel }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-full md:w-auto md:mt-6">
                <button id="filterBtn" class="px-4 py-2 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50">
                    Filter
                </button>
            </div>
        </div>
    </div>

    <!-- Tabel Transaksi -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        <h2 class="text-lg font-semibold mb-4">Data Transaksi Toserda</h2>
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white border border-gray-200">
                <thead>
                    <tr>
                        <th class="px-4 py-2 border-b text-left">Tanggal</th>
                        <th class="px-4 py-2 border-b text-left">No KTP</th>
                        <th class="px-4 py-2 border-b text-left">Jumlah</th>
                        <th class="px-4 py-2 border-b text-left">Keterangan</th>
                        <th class="px-4 py-2 border-b text-left">Debit/Kredit</th>
                        <th class="px-4 py-2 border-b text-left">Status Pembayaran</th>
                        <th class="px-4 py-2 border-b text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody id="transactionTableBody">
                    @forelse($transaksi as $tr)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-2 border-b">{{ $tr->tgl_transaksi->format('d/m/Y') }}</td>
                        <td class="px-4 py-2 border-b">{{ $tr->no_ktp }}</td>
                        <td class="px-4 py-2 border-b">{{ number_format($tr->jumlah, 0, ',', '.') }}</td>
                        <td class="px-4 py-2 border-b">{{ $tr->keterangan }}</td>
                        <td class="px-4 py-2 border-b">{{ $tr->dk == 'D' ? 'Debit' : 'Kredit' }}</td>
                        <td class="px-4 py-2 border-b">
                            @php
                                $billing = \App\Models\billing::where('id_transaksi', $tr->id)
                                    ->where('jns_transaksi', 'toserda')
                                    ->first();
                            @endphp
                            
                            @if($billing && $billing->status_bayar == 'sudah')
                                <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Sudah Bayar</span>
                            @else
                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Belum Bayar</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 border-b">
                            @if($billing && $billing->status_bayar != 'sudah')
                                <form action="{{ route('anggota.bayar.toserda.process', $billing->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="px-3 py-1 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50 text-xs">
                                        Bayar
                                    </button>
                                </form>
                            @elseif($billing)
                                <span class="px-3 py-1 bg-gray-100 text-gray-500 rounded-md text-xs">Sudah Dibayar</span>
                            @else
                                <span class="px-3 py-1 bg-gray-100 text-gray-500 rounded-md text-xs">Belum Ditagih</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-4 py-4 text-center text-gray-500">Belum ada data transaksi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const filterBtn = document.getElementById('filterBtn');
        const periodSelect = document.getElementById('period');
        const tableBody = document.getElementById('transactionTableBody');
        
        filterBtn.addEventListener('click', function() {
            const period = periodSelect.value;
            if (!period) {
                alert('Silakan pilih periode terlebih dahulu');
                return;
            }
            
            // Fetch transactions for selected period
            fetch(`{{ route('anggota.get.transaksi.period') }}?period=${period}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(response => response.json())
            .then(data => {
                // Clear table
                tableBody.innerHTML = '';
                
                if (data.transaksi.length === 0) {
                    const row = document.createElement('tr');
                    row.innerHTML = `<td colspan="7" class="px-4 py-4 text-center text-gray-500">Tidak ada transaksi pada periode ini</td>`;
                    tableBody.appendChild(row);
                    return;
                }
                
                // Populate table with new data
                data.transaksi.forEach(tr => {
                    const row = document.createElement('tr');
                    row.className = 'hover:bg-gray-50';
                    
                    const date = new Date(tr.tgl_transaksi);
                    const formattedDate = `${date.getDate().toString().padStart(2, '0')}/${(date.getMonth() + 1).toString().padStart(2, '0')}/${date.getFullYear()}`;
                    
                    let statusBayar = '';
                    let actionButton = '';
                    
                    if (tr.billing && tr.billing.status_bayar === 'sudah') {
                        statusBayar = `<span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs">Sudah Bayar</span>`;
                        actionButton = `<span class="px-3 py-1 bg-gray-100 text-gray-500 rounded-md text-xs">Sudah Dibayar</span>`;
                    } else if (tr.billing) {
                        statusBayar = `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Belum Bayar</span>`;
                        actionButton = `
                            <form action="{{ route('anggota.bayar.toserda.process', '') }}/${tr.billing.id}" method="POST">
                                @csrf
                                <button type="submit" class="px-3 py-1 bg-[#14AE5C] text-white rounded-md hover:bg-[#14AE5C]/80 focus:outline-none focus:ring-2 focus:ring-[#14AE5C] focus:ring-opacity-50 text-xs">
                                    Bayar
                                </button>
                            </form>
                        `;
                    } else {
                        statusBayar = `<span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs">Belum Ditagih</span>`;
                        actionButton = `<span class="px-3 py-1 bg-gray-100 text-gray-500 rounded-md text-xs">Belum Ditagih</span>`;
                    }
                    
                    row.innerHTML = `
                        <td class="px-4 py-2 border-b">${formattedDate}</td>
                        <td class="px-4 py-2 border-b">${tr.no_ktp}</td>
                        <td class="px-4 py-2 border-b">${new Intl.NumberFormat('id-ID').format(tr.jumlah)}</td>
                        <td class="px-4 py-2 border-b">${tr.keterangan || '-'}</td>
                        <td class="px-4 py-2 border-b">${tr.dk === 'D' ? 'Debit' : 'Kredit'}</td>
                        <td class="px-4 py-2 border-b">${statusBayar}</td>
                        <td class="px-4 py-2 border-b">${actionButton}</td>
                    `;
                    
                    tableBody.appendChild(row);
                });
            })
            .catch(error => {
                console.error('Error fetching transactions:', error);
                alert('Terjadi kesalahan saat mengambil data transaksi');
            });
        });
    });
</script>
@endsection
