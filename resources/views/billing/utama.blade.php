@extends('layouts.app')

@section('title', 'Billing Utama')
@section('sub-title', 'Rekap Tagihan Utama')

<meta name="csrf-token" content="{{ csrf_token() }}">

@section('content')
<div class="container mx-auto px-4">
    @if(session('error'))
    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Error!</strong>
        <span class="block sm:inline">{!! session('error') !!}</span>
    </div>
    @endif
    @if(session('success'))
    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
        <strong class="font-bold">Success!</strong>
        <span class="block sm:inline">{{ session('success') }}</span>
    </div>
    @endif

    <div class="bg-white rounded-lg shadow-md mb-6">
        <div class="border-b border-gray-200 px-6 py-4">
            <h5 class="font-semibold text-lg">Billing Utama</h5>
        </div>
        <div class="p-6">
            <div class="mb-6">
                <form action="{{ route('billing.utama') }}" method="GET" class="grid grid-cols-1 md:grid-cols-12 gap-4">
                    <div class="md:col-span-3">
                        <label for="bulan" class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                        <select name="bulan" id="bulan"
                            class="w-full rounded-lg border-2 border-gray-300 bg-gray-100 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm py-2 px-3">
                            @foreach($bulanList as $key => $namaBulan)
                            <option value="{{ $key }}" {{ (isset($bulan) && $bulan == $key) ? 'selected' : '' }}>
                                {{ $namaBulan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-3">
                        <label for="tahun" class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                        <select name="tahun" id="tahun"
                            class="w-full rounded-lg border-2 border-gray-300 bg-gray-100 shadow-sm focus:border-green-500 focus:ring focus:ring-green-200 focus:ring-opacity-50 text-sm py-2 px-3">
                            @foreach($tahunList ?? [] as $tahunOption)
                            <option value="{{ $tahunOption }}"
                                {{ (isset($tahun) && $tahun == $tahunOption) ? 'selected' : '' }}>{{ $tahunOption }}
                            </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="md:col-span-4">
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Cari Anggota</label>
                        <div class="flex items-center bg-gray-100 p-2 rounded-lg border-2 border-gray-300">
                            <i class="fa-solid fa-magnifying-glass mr-2 text-gray-400"></i>
                            <input type="text"
                                class="text-sm text-gray-500 bg-transparent border-none focus:outline-none w-full"
                                id="search" name="search" placeholder="Nama atau No ID Koperasi"
                                value="{{ request('search') }}">
                        </div>
                    </div>
                    <div class="md:col-span-2 flex items-end">
                        <div class="flex space-x-2">
                            <button type="submit"
                                class="bg-blue-100 hover:bg-blue-200 text-blue-800 text-sm font-medium px-4 py-2 rounded-lg border-2 border-blue-300 transition">Filter</button>
                            <a href="{{ route('billing.utama') }}"
                                class="bg-gray-100 hover:bg-gray-200 text-gray-800 text-sm font-medium px-4 py-2 rounded-lg border-2 border-gray-300 transition">Reset</a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Button Upload Excel -->
            <div class="mb-6 flex justify-start gap-x-2 items-center">
                <!-- Debug Button -->
                <!-- <button type="button" onclick="debugPeriodData()"
                    class="inline-flex items-center gap-2 bg-yellow-50 border border-yellow-400 text-yellow-900 font-medium px-4 py-2 rounded-lg transition hover:bg-yellow-100 hover:border-yellow-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    <span class="text-sm">Debug Data</span>
                </button> -->

                <!-- Upload Excel Button -->
                <button type="button" onclick="openUploadModal()"
                    class="inline-flex items-center gap-2 bg-green-50 border border-green-400 text-green-900 font-medium px-5 py-2 rounded-lg transition hover:bg-green-100 hover:border-green-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
                    </svg>
                    <span class="text-sm">Add File Excel</span>
                </button>



                <!-- Proceed Button -->
                <button type="button" onclick="proceedBilling()"
                    class="inline-flex items-center gap-2 bg-purple-50 border border-purple-400 text-purple-900 font-medium px-5 py-2 rounded-lg transition hover:bg-purple-100 hover:border-purple-500">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                        stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                    <span class="text-sm">Proceed</span>
                </button>
            </div>
            <!-- Table Periode Summary -->
            <div class="mb-6" id="period-summary">
                @include('billing.partials.period-table')
            </div>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm">
                        <th class="px-4 py-3 border-b text-center w-12">No.</th>
                        <th class="px-4 py-3 border-b text-center">No KTP</th>
                        <th class="px-4 py-3 border-b text-center">Nama</th>
                        <th class="px-4 py-3 border-b text-center">Tgl Transaksi</th>
                        <th class="px-4 py-3 border-b text-center">Toserda</th>
                        <th class="px-4 py-3 border-b text-center">Simpanan Wajib</th>
                        <th class="px-4 py-3 border-b text-center">Sukarela</th>
                        <th class="px-4 py-3 border-b text-center">Khusus 2</th>
                        <th class="px-4 py-3 border-b text-center">Pokok</th>
                        <th class="px-4 py-3 border-b text-center">Pinjaman</th>
                        <th class="px-4 py-3 border-b text-center">Total Tagihan</th>
                        <th class="px-4 py-3 border-b text-center">Tagihan Upload</th>
                        <th class="px-4 py-3 border-b text-center">Selisih</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($data as $index => $row)
                    <tr class="{{ $index % 2 == 0 ? 'bg-white' : 'bg-gray-50' }}">
                        <td class="px-4 py-3 text-center text-sm">{{ $index + 1 }}</td>
                        <td class="px-4 py-3 text-center text-sm">{{ $row->no_ktp }}</td>
                        <td class="px-4 py-3 text-sm">{{ $row->nama }}</td>
                        <td class="px-4 py-3 text-center text-sm">
                            {{ \Carbon\Carbon::parse($row->tgl_transaksi)->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->tagihan_toserda ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->tagihan_simpanan_wajib ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->tagihan_simpanan_sukarela ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->tagihan_simpanan_khusus_2 ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->tagihan_simpanan_pokok ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->tagihan_pinjaman ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm font-semibold">
                            {{ number_format($row->total_tagihan ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->tagihan_upload ?? 0, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right text-sm">
                            {{ number_format($row->selisih_calculated ?? 0, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="13" class="px-4 py-3 text-center text-sm text-gray-500">Belum ada data Billing
                            Utama</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-6">
            @if(method_exists($data, 'hasPages') && $data->hasPages())
            {{ $data->withQueryString()->links('vendor.pagination.tailwind') }}
            @endif
        </div>
    </div>
</div>
</div>

<!-- Modal Upload Excel -->
<div id="uploadModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
    <div class="flex items-center justify-center min-h-screen">
        <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4">
            <div class="flex items-center justify-between p-6 border-b">
                <h3 class="text-lg font-semibold text-gray-900">Upload File Excel</h3>
                <button onclick="closeUploadModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <div class="p-6">
                <form action="#" method="POST" enctype="multipart/form-data" id="uploadForm">
                    @csrf
                    <div class="mb-4">
                        <label for="excel_file" class="block text-sm font-medium text-gray-700 mb-2">Pilih File
                            Excel</label>
                        <input type="file" id="excel_file" name="excel_file" accept=".xlsx,.xls"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <p class="text-xs text-gray-500 mt-1">Format: .xlsx atau .xls</p>
                        <p class="text-xs text-gray-500 mt-1">Kolom yang dibutuhkan: tgl_transaksi, no_ktp, jumlah</p>
                        <p class="text-xs text-gray-500 mt-1">Format tanggal: YYYY-MM-DD (contoh: 2025-07-18)</p>
                    </div>
                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeUploadModal()"
                            class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 border border-gray-300 rounded-md hover:bg-gray-200">
                            Batal
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm font-medium text-white bg-green-600 border border-transparent rounded-md hover:bg-green-700">
                            Upload
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function openUploadModal() {
    document.getElementById('uploadModal').classList.remove('hidden');
}

function closeUploadModal() {
    document.getElementById('uploadModal').classList.add('hidden');
}

// Close modal when clicking outside
document.getElementById('uploadModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeUploadModal();
    }
});

// Handle form submission
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const fileInput = document.getElementById('excel_file');
    const bulan = document.getElementById('bulan').value;
    const tahun = document.getElementById('tahun').value;

    if (!fileInput.files[0]) {
        alert('Silakan pilih file Excel terlebih dahulu');
        return;
    }

    // Add bulan and tahun to form data
    formData.append('bulan', bulan);
    formData.append('tahun', tahun);

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.textContent;
    submitBtn.textContent = 'Uploading...';
    submitBtn.disabled = true;

    // Submit via AJAX
    fetch('/billing-upload/excel', {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert(data.message);
                closeUploadModal();
                // Refresh the page to show updated data
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Upload error:', error);
            alert('Terjadi kesalahan saat upload: ' + error.message);
        })
        .finally(() => {
            // Reset button state
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        });
});

// Update period summary when month/year changes
document.addEventListener('DOMContentLoaded', function() {
    const bulanSelect = document.getElementById('bulan');
    const tahunSelect = document.getElementById('tahun');

    function updatePeriodSummary() {
        const bulan = bulanSelect.value;
        const tahun = tahunSelect.value;

        // Show loading state
        const periodSummary = document.getElementById('period-summary');
        if (periodSummary) {
            periodSummary.style.opacity = '0.6';
        }

        // Fetch new period data
        fetch(`/billing-periode/summary/${bulan}/${tahun}`)
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success') {
                    // Update period display
                    updatePeriodSummaryDisplay(data.data);
                }
            })
            .catch(error => {
                console.error('Error updating period summary:', error);
            })
            .finally(() => {
                // Remove loading state
                if (periodSummary) {
                    periodSummary.style.opacity = '1';
                }
            });
    }

    function updatePeriodSummaryDisplay(data) {
        // Update menggunakan ID unik, bukan querySelectorAll yang bisa mengubah header
        const periodValue = document.getElementById('period-value');
        const totalAnggotaValue = document.getElementById('total-anggota-value');
        const simpananSukarelaValue = document.getElementById('simpanan-sukarela-value');
        const simpananPokokValue = document.getElementById('simpanan-pokok-value');
        const simpananWajibValue = document.getElementById('simpanan-wajib-value');

        if (periodValue) periodValue.textContent = data.periode;
        if (totalAnggotaValue) totalAnggotaValue.textContent = new Intl.NumberFormat('id-ID').format(data
            .total_anggota);
        if (simpananSukarelaValue) simpananSukarelaValue.textContent = new Intl.NumberFormat('id-ID').format(
            data.simpanan_sukarela);
        if (simpananPokokValue) simpananPokokValue.textContent = new Intl.NumberFormat('id-ID').format(data
            .simpanan_pokok);
        if (simpananWajibValue) simpananWajibValue.textContent = new Intl.NumberFormat('id-ID').format(data
            .simpanan_wajib);
    }

    // Add event listeners
    bulanSelect.addEventListener('change', updatePeriodSummary);
    tahunSelect.addEventListener('change', updatePeriodSummary);
});

// Debug function to check what data exists
function debugPeriodData() {
    const bulan = document.getElementById('bulan').value;
    const tahun = document.getElementById('tahun').value;

    console.log('Debugging period data for:', bulan, tahun);

    fetch(`/billing-periode/debug/${bulan}/${tahun}`)
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                console.log('Debug info:', data.debug_info);
                alert(
                    `Debug Info:\n\nPeriode: ${data.debug_info.periode}\nTotal Records Billing: ${data.debug_info.billing_table_total_records}\nTotal Records Anggota: ${data.debug_info.anggota_table_total_records}\nData for Period: ${data.debug_info.billing_data_for_period}\nAnggota for Period: ${data.debug_info.anggota_data_for_period}`
                );
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Debug error:', error);
            alert('Error debugging data: ' + error.message);
        });
}



// Proceed function to process billing data
function proceedBilling() {
    const bulan = document.getElementById('bulan').value;
    const tahun = document.getElementById('tahun').value;

    if (!confirm('Apakah Anda yakin ingin memproses data billing untuk periode ' + bulan + '-' + tahun +
            '?\n\nTindakan ini akan:\n1. Memproses semua pembayaran ke database utama\n2. Mengupdate status pembayaran\n3. Menghapus data temporary\n\nData yang sudah diproses tidak dapat dibatalkan.'
        )) {
        return;
    }

    // Show loading state
    const proceedButton = event.target.closest('button');
    const originalText = proceedButton.innerHTML;
    proceedButton.innerHTML = `
        <svg class="animate-spin h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-sm">Processing...</span>
    `;
    proceedButton.disabled = true;

    console.log('Proceeding billing for:', bulan, tahun);

    fetch('/billing/proceed', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                bulan: bulan,
                tahun: tahun
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                alert('Berhasil! Data billing berhasil diproses.\n\n' + data.message);
                // Reload page to show updated data
                window.location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Proceed error:', error);
            alert('Error memproses data: ' + error.message);
        })
        .finally(() => {
            // Restore button state
            proceedButton.innerHTML = originalText;
            proceedButton.disabled = false;
        });
}
</script>
@endsection