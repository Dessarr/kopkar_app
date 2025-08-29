@extends('layouts.member')

@section('title', 'Form Pengajuan Penarikan Simpanan')

@section('content')
<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 border border-green-400 text-green-700 rounded">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">{{ session('error') }}</div>
        @endif

        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-semibold text-gray-800">Form Pengajuan Penarikan Simpanan</h1>
            <a href="{{ route('member.pengajuan.penarikan') }}"
                class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded-lg">
                ← Kembali
            </a>
        </div>

        <form action="{{ route('member.pengajuan.penarikan.store') }}" method="POST" id="formPengajuan">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Jenis Simpanan -->
                <div class="col-span-1 md:col-span-2">
                    <label for="jenis_simpanan" class="block text-sm font-medium text-gray-700 mb-2">
                        Jenis Simpanan <span class="text-red-500">*</span>
                    </label>
                    <select name="jenis_simpanan" id="jenis_simpanan" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Pilih Jenis Simpanan</option>
                        @foreach($jenisSimpanan as $jenis)
                            <option value="{{ $jenis->id }}" data-id="{{ $jenis->id }}" {{ old('jenis_simpanan') == $jenis->id ? 'selected' : '' }}>
                                {{ $jenis->jns_simpan }}
                            </option>
                        @endforeach
                    </select>
                    @error('jenis_simpanan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Saldo Tersedia -->
                <div class="col-span-1 md:col-span-2">
                    <div class="bg-blue-50 border border-blue-200 rounded-md p-4">
                        <h3 class="text-sm font-medium text-blue-800 mb-2">Informasi Saldo</h3>
                        <div id="saldoInfo" class="text-sm text-blue-700">
                            Pilih jenis simpanan untuk melihat saldo tersedia
                        </div>
                    </div>
                </div>

                <!-- Nominal Penarikan -->
                <div class="col-span-1 md:col-span-2">
                    <label for="nominal" class="block text-sm font-medium text-gray-700 mb-2">
                        Nominal Penarikan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                        <input type="text" name="nominal" id="nominal" required
                            class="w-full pl-12 pr-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                            placeholder="0"
                            value="{{ old('nominal') }}"
                            maxlength="20">
                    </div>
                    @error('nominal')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-gray-500">Minimal penarikan: Rp 1.000</p>
                </div>

                <!-- Keterangan -->
                <div class="col-span-1 md:col-span-2">
                    <label for="keterangan" class="block text-sm font-medium text-gray-700 mb-2">
                        Keterangan <span class="text-red-500">*</span>
                    </label>
                    <textarea name="keterangan" id="keterangan" rows="4" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500"
                        placeholder="Alasan penarikan simpanan...">{{ old('keterangan') }}</textarea>
                    @error('keterangan')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Informasi Member -->
                <div class="col-span-1 md:col-span-2">
                    <div class="bg-gray-50 border border-gray-200 rounded-md p-4">
                        <h3 class="text-sm font-medium text-gray-800 mb-2">Informasi Member</h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div>
                                <span class="font-medium text-gray-700">Nama:</span>
                                <span class="text-gray-600">{{ $member->nama }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">No. KTP:</span>
                                <span class="text-gray-600">{{ $member->no_ktp }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Tanggal Pengajuan:</span>
                                <span class="text-gray-600">{{ now()->format('d/m/Y H:i') }}</span>
                            </div>
                            <div>
                                <span class="font-medium text-gray-700">Status:</span>
                                <span class="text-blue-600 font-medium">Menunggu Konfirmasi</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="mt-8 flex justify-end space-x-4">
                <button type="button" onclick="window.history.back()"
                    class="bg-gray-500 hover:bg-gray-600 text-white px-6 py-2 rounded-md transition duration-200">
                    Batal
                </button>
                <button type="submit" id="submitBtn"
                    class="bg-[#14AE5C] hover:bg-[#14AE5C]/80 text-white px-6 py-2 rounded-md transition duration-200">
                    Kirim Pengajuan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const jenisSimpananSelect = document.getElementById('jenis_simpanan');
    const nominalInput = document.getElementById('nominal');
    const saldoInfo = document.getElementById('saldoInfo');
    const submitBtn = document.getElementById('submitBtn');

    // Format number input
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    // Unformat number
    function unformatNumber(str) {
        return str.replace(/\./g, '');
    }

    // Handle nominal input formatting
    nominalInput.addEventListener('input', function(e) {
        let value = e.target.value.replace(/[^\d]/g, '');
        if (value) {
            e.target.value = formatNumber(value);
        }
    });

    // Initialize nominal input with old value if exists
    if (nominalInput.value) {
        let value = nominalInput.value.replace(/[^\d]/g, '');
        if (value) {
            nominalInput.value = formatNumber(value);
        }
    }

    // Handle jenis simpanan change
    jenisSimpananSelect.addEventListener('change', function() {
        loadSaldo(this.value);
    });

    // Function to load saldo
    function loadSaldo(jenisId) {
        if (jenisId) {
            // Show loading
            saldoInfo.innerHTML = '<div class="flex items-center"><div class="animate-spin rounded-full h-4 w-4 border-b-2 border-blue-600 mr-2"></div>Memuat saldo...</div>';
            
            // Get CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (!csrfToken) {
                console.error('CSRF token not found');
                saldoInfo.innerHTML = '<span class="text-red-600">Error: CSRF token tidak ditemukan</span>';
                return;
            }
            
            // Fetch saldo
            fetch(`{{ route('member.saldo.simpanan') }}?jenis_id=${jenisId}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    saldoInfo.innerHTML = `
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                            <div><span class="font-medium">Saldo Tersedia:</span></div>
                            <div class="font-bold text-lg">Rp ${data.saldo_formatted}</div>
                        </div>
                    `;
                } else {
                    saldoInfo.innerHTML = '<span class="text-red-600">Gagal memuat saldo</span>';
                }
            })
            .catch(error => {
                console.error('Error fetching saldo:', error);
                saldoInfo.innerHTML = '<span class="text-red-600">Gagal memuat saldo</span>';
            });
        } else {
            saldoInfo.innerHTML = 'Pilih jenis simpanan untuk melihat saldo tersedia';
        }
    }

    // Load saldo if jenis simpanan is already selected (from old input)
    if (jenisSimpananSelect.value) {
        loadSaldo(jenisSimpananSelect.value);
    }

    // Form validation
    document.getElementById('formPengajuan').addEventListener('submit', function(e) {
        console.log('Form submission started');
        
        const nominal = unformatNumber(nominalInput.value);
        const jenisSimpanan = jenisSimpananSelect.value;
        const keterangan = document.getElementById('keterangan').value;

        console.log('Form data:', {
            nominal: nominal,
            jenisSimpanan: jenisSimpanan,
            keterangan: keterangan
        });

        if (!jenisSimpanan) {
            e.preventDefault();
            alert('Silakan pilih jenis simpanan');
            return;
        }

        if (!nominal || nominal < 1000) {
            e.preventDefault();
            alert('Nominal minimal Rp 1.000');
            return;
        }

        if (!keterangan.trim()) {
            e.preventDefault();
            alert('Silakan isi keterangan');
            return;
        }

        // Set the unformatted value for submission
        nominalInput.value = nominal;
        
        console.log('Form validation passed, submitting with nominal:', nominalInput.value);

        // Disable submit button
        submitBtn.disabled = true;
        submitBtn.textContent = 'Mengirim...';
    });
});
</script>
@endpush
@endsection

