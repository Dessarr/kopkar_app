@extends('layouts.app')

@section('title', 'Identitas Koperasi')
@section('sub-title', 'Pengaturan Data Profil Koperasi')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Identitas Koperasi</h1>
            <p class="text-gray-600 mt-1">Kelola data profil dan informasi dasar koperasi</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="bg-blue-50 px-4 py-2 rounded-lg border border-blue-200">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-building text-blue-600"></i>
                    <span class="text-sm font-medium text-blue-800">Data Profil</span>
                </div>
            </div>
            <div class="bg-green-50 px-4 py-2 rounded-lg border border-green-200">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-shield-alt text-green-600"></i>
                    <span class="text-sm font-medium text-green-800">Terlindungi</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Message -->
    @if(session('success'))
    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded-r-lg">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-3"></i>
            <div>
                <p class="font-semibold">Berhasil!</p>
                <p>{{ session('success') }}</p>
            </div>
        </div>
    </div>
    @endif

    <!-- Error Message -->
    @if($errors->any())
    <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded-r-lg">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-3"></i>
            <div>
                <p class="font-semibold">Terjadi Kesalahan!</p>
                <ul class="list-disc list-inside mt-1">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
    @endif

    @php
        $fieldGroups = [
            'Informasi Dasar' => [
                'nama_lembaga' => ['label' => 'Nama Koperasi', 'icon' => 'fas fa-building', 'required' => true],
                'nama_ketua' => ['label' => 'Nama Pimpinan', 'icon' => 'fas fa-user-tie', 'required' => true],
                'hp_ketua' => ['label' => 'No HP Pimpinan', 'icon' => 'fas fa-phone', 'type' => 'tel'],
                'email' => ['label' => 'Email', 'icon' => 'fas fa-envelope', 'type' => 'email'],
                'web' => ['label' => 'Website', 'icon' => 'fas fa-globe', 'type' => 'url'],
            ],
            'Alamat & Kontak' => [
                'alamat' => ['label' => 'Alamat Lengkap', 'icon' => 'fas fa-map-marker-alt', 'required' => true, 'colspan' => 2],
                'kota' => ['label' => 'Kota/Kabupaten', 'icon' => 'fas fa-city', 'required' => true],
                'telepon' => ['label' => 'Telepon Kantor', 'icon' => 'fas fa-phone-alt', 'type' => 'tel'],
            ],
            'Legal & Administrasi' => [
                'npwp' => ['label' => 'NPWP', 'icon' => 'fas fa-file-invoice', 'type' => 'text'],
                'no_badan_hukum' => ['label' => 'No. Badan Hukum', 'icon' => 'fas fa-certificate', 'type' => 'text'],
                'tgl_berdiri' => ['label' => 'Tanggal Berdiri', 'icon' => 'fas fa-calendar-plus', 'type' => 'date'],
                'tgl_pengesahan' => ['label' => 'Tanggal Pengesahan', 'icon' => 'fas fa-calendar-check', 'type' => 'date'],
                'bidang_usaha' => ['label' => 'Bidang Usaha', 'icon' => 'fas fa-briefcase', 'type' => 'text'],
            ],
            'Status & Kepemilikan' => [
                'status_kantor' => ['label' => 'Status Kantor', 'icon' => 'fas fa-home', 'type' => 'text'],
                'status_kepemilikan' => ['label' => 'Status Kepemilikan', 'icon' => 'fas fa-key', 'type' => 'text'],
                'luas_tanah' => ['label' => 'Luas Tanah (m²)', 'icon' => 'fas fa-ruler', 'type' => 'number'],
                'luas_bangunan' => ['label' => 'Luas Bangunan (m²)', 'icon' => 'fas fa-building', 'type' => 'number'],
            ],
            'Modal & Keuangan' => [
                'modal_sendiri' => ['label' => 'Modal Sendiri (Rp)', 'icon' => 'fas fa-wallet', 'type' => 'number', 'format' => 'currency'],
                'modal_luar' => ['label' => 'Modal Luar (Rp)', 'icon' => 'fas fa-hand-holding-usd', 'type' => 'number', 'format' => 'currency'],
                'jumlah_simpanan' => ['label' => 'Total Simpanan (Rp)', 'icon' => 'fas fa-piggy-bank', 'type' => 'number', 'format' => 'currency'],
                'jumlah_pinjaman' => ['label' => 'Total Pinjaman (Rp)', 'icon' => 'fas fa-credit-card', 'type' => 'number', 'format' => 'currency'],
            ],
            'Sumber Daya Manusia' => [
                'jumlah_anggota' => ['label' => 'Jumlah Anggota', 'icon' => 'fas fa-users', 'type' => 'number'],
                'jumlah_karyawan' => ['label' => 'Jumlah Karyawan', 'icon' => 'fas fa-user-friends', 'type' => 'number'],
                'jumlah_pengurus' => ['label' => 'Jumlah Pengurus', 'icon' => 'fas fa-user-tie', 'type' => 'number'],
                'jumlah_pengawas' => ['label' => 'Jumlah Pengawas', 'icon' => 'fas fa-user-shield', 'type' => 'number'],
            ]
        ];
        
        $data = [];
        foreach($identitasKoperasi as $item) {
            $data[$item->opsi_key] = $item->opsi_val;
        }
    @endphp

    <!-- Main Form Container -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Form Header -->
        <div class="bg-gradient-to-r from-blue-600 to-blue-700 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white bg-opacity-20 p-2 rounded-lg">
                        <i class="fas fa-building text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Data Profil Koperasi</h2>
                        <p class="text-blue-100 text-sm">Kelola informasi identitas dan profil koperasi</p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <button type="button" id="editBtn" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition-all duration-200 flex items-center space-x-2">
                        <i class="fas fa-edit"></i>
                        <span>Edit Data</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Form Content -->
        <form id="identitasForm" method="POST" action="{{ route('settings.identitas_koperasi.update') }}" class="p-6">
            @csrf
            
            @foreach($fieldGroups as $groupTitle => $fields)
            <div class="mb-8">
                <!-- Group Header -->
                <div class="flex items-center mb-4 pb-2 border-b border-gray-200">
                    <div class="bg-blue-50 p-2 rounded-lg mr-3">
                        <i class="fas fa-folder text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">{{ $groupTitle }}</h3>
                </div>

                <!-- Group Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($fields as $key => $fieldConfig)
                    <div class="{{ isset($fieldConfig['colspan']) && $fieldConfig['colspan'] == 2 ? 'md:col-span-2' : '' }}">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="{{ $fieldConfig['icon'] }} mr-2 text-gray-500"></i>
                            {{ $fieldConfig['label'] }}
                            @if(isset($fieldConfig['required']) && $fieldConfig['required'])
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        
                        @php
                            $inputType = $fieldConfig['type'] ?? 'text';
                            $inputValue = $data[$key] ?? '';
                            
                            // Format currency values
                            if(isset($fieldConfig['format']) && $fieldConfig['format'] == 'currency' && is_numeric($inputValue)) {
                                $inputValue = number_format($inputValue, 0, ',', '.');
                            }
                        @endphp
                        
                        <input type="{{ $inputType }}" 
                               name="{{ $key }}" 
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-200 identitas-input bg-gray-50" 
                               value="{{ $inputValue }}" 
                               placeholder="Masukkan {{ strtolower($fieldConfig['label']) }}"
                               readonly>
                    </div>
                    @endforeach
                </div>
            </div>
            @endforeach

            <!-- Action Buttons -->
            <div class="flex justify-end space-x-4 pt-6 border-t border-gray-200">
                <button type="button" id="cancelBtn" class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-all duration-200 flex items-center space-x-2" style="display:none;">
                    <i class="fas fa-times"></i>
                    <span>Batal</span>
                </button>
                <button type="submit" id="saveBtn" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-all duration-200 flex items-center space-x-2" style="display:none;">
                    <i class="fas fa-save"></i>
                    <span>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
    <style>
        .export-btn {
            background-color: #e6fff2;
            border: 2px solid #14AE5C;
            color: #222;
            font-weight: 500;
            border-radius: 0.5rem;
            padding: 0.5rem 1.5rem;
            transition: background 0.2s, color 0.2s;
        }
        .export-btn:hover {
            background-color: #b2f5d6;
            color: #111;
        }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const editBtn = document.getElementById('editBtn');
            const saveBtn = document.getElementById('saveBtn');
            const cancelBtn = document.getElementById('cancelBtn');
            const inputs = document.querySelectorAll('.identitas-input');
            const form = document.getElementById('identitasForm');
            
            // Store original values for cancel functionality
            let originalValues = {};
            inputs.forEach(input => {
                originalValues[input.name] = input.value;
            });

            if(editBtn) {
                editBtn.addEventListener('click', function() {
                    // Enable all inputs
                    inputs.forEach(input => {
                        input.removeAttribute('readonly');
                        input.classList.remove('bg-gray-50');
                        input.classList.add('bg-white', 'border-blue-300');
                    });
                    
                    // Show/hide buttons
                    editBtn.style.display = 'none';
                    saveBtn.style.display = 'flex';
                    cancelBtn.style.display = 'flex';
                    
                    // Add visual feedback
                    const header = document.querySelector('.bg-gradient-to-r');
                    if(header) {
                        header.classList.add('from-orange-600', 'to-orange-700');
                        header.classList.remove('from-blue-600', 'to-blue-700');
                    }
                    
                    // Focus first input
                    if(inputs.length > 0) {
                        inputs[0].focus();
                    }
                });
            }

            if(cancelBtn) {
                cancelBtn.addEventListener('click', function() {
                    // Restore original values
                    inputs.forEach(input => {
                        input.value = originalValues[input.name];
                        input.setAttribute('readonly', 'readonly');
                        input.classList.add('bg-gray-50');
                        input.classList.remove('bg-white', 'border-blue-300');
                    });
                    
                    // Show/hide buttons
                    editBtn.style.display = 'flex';
                    saveBtn.style.display = 'none';
                    cancelBtn.style.display = 'none';
                    
                    // Restore header color
                    const header = document.querySelector('.bg-gradient-to-r');
                    if(header) {
                        header.classList.add('from-blue-600', 'to-blue-700');
                        header.classList.remove('from-orange-600', 'to-orange-700');
                    }
                });
            }

            // Form submission with loading state
            if(form) {
                form.addEventListener('submit', function(e) {
                    saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Menyimpan...';
                    saveBtn.disabled = true;
                    cancelBtn.disabled = true;
                });
            }

            // Add input validation
            inputs.forEach(input => {
                input.addEventListener('blur', function() {
                    if (this.hasAttribute('readonly')) return;
                    
                    // Basic validation
                    if (this.hasAttribute('required') && !this.value.trim()) {
                        this.classList.add('border-red-500');
                        this.classList.remove('border-gray-300');
                    } else {
                        this.classList.remove('border-red-500');
                        this.classList.add('border-gray-300');
                    }
                });
            });
        });
    </script>
</div>

<div class="popup">

</div>
@endsection