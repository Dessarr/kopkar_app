@extends('layouts.app')

@section('title', 'Suku Bunga')
@section('sub-title', 'Pengaturan Suku Bunga & Biaya')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Suku Bunga & Biaya</h1>
            <p class="text-gray-600 mt-1">Kelola suku bunga, biaya administrasi, dan komponen keuangan lainnya</p>
        </div>
        <div class="flex items-center space-x-3">
            <div class="bg-green-50 px-4 py-2 rounded-lg border border-green-200">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-percentage text-green-600"></i>
                    <span class="text-sm font-medium text-green-800">Suku Bunga</span>
                </div>
            </div>
            <div class="bg-blue-50 px-4 py-2 rounded-lg border border-blue-200">
                <div class="flex items-center space-x-2">
                    <i class="fas fa-calculator text-blue-600"></i>
                    <span class="text-sm font-medium text-blue-800">Kalkulasi</span>
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
            'Suku Bunga Utama' => [
                'bg_tab' => ['label' => 'Bunga Tabungan (%)', 'icon' => 'fas fa-piggy-bank', 'type' => 'number', 'step' => '0.01', 'required' => true],
                'bg_pinjam' => ['label' => 'Bunga Pinjaman (%)', 'icon' => 'fas fa-credit-card', 'type' => 'number', 'step' => '0.01', 'required' => true],
                'bunga_biasa' => ['label' => 'Bunga Biasa (%)', 'icon' => 'fas fa-chart-line', 'type' => 'number', 'step' => '0.01'],
                'bunga_barang' => ['label' => 'Bunga Barang (%)', 'icon' => 'fas fa-box', 'type' => 'number', 'step' => '0.01'],
                'pinjaman_bunga_tipe' => ['label' => 'Tipe Bunga Pinjaman', 'icon' => 'fas fa-cogs', 'type' => 'text'],
            ],
            'Biaya & Denda' => [
                'biaya_adm' => ['label' => 'Biaya Administrasi (Rp)', 'icon' => 'fas fa-receipt', 'type' => 'number', 'format' => 'currency'],
                'denda' => ['label' => 'Denda (Rp)', 'icon' => 'fas fa-exclamation-triangle', 'type' => 'number', 'format' => 'currency'],
                'denda_hari' => ['label' => 'Denda per Hari (Rp)', 'icon' => 'fas fa-calendar-times', 'type' => 'number', 'format' => 'currency'],
                'pjk_pph' => ['label' => 'Pajak PPh (%)', 'icon' => 'fas fa-file-invoice', 'type' => 'number', 'step' => '0.01'],
            ],
            'Dana & Alokasi' => [
                'dana_cadangan' => ['label' => 'Dana Cadangan (%)', 'icon' => 'fas fa-shield-alt', 'type' => 'number', 'step' => '0.01'],
                'jasa_anggota' => ['label' => 'Jasa Anggota (%)', 'icon' => 'fas fa-users', 'type' => 'number', 'step' => '0.01'],
                'dana_pengurus' => ['label' => 'Dana Pengurus (%)', 'icon' => 'fas fa-user-tie', 'type' => 'number', 'step' => '0.01'],
                'dana_karyawan' => ['label' => 'Dana Karyawan (%)', 'icon' => 'fas fa-user-friends', 'type' => 'number', 'step' => '0.01'],
                'dana_pend' => ['label' => 'Dana Pendidikan (%)', 'icon' => 'fas fa-graduation-cap', 'type' => 'number', 'step' => '0.01'],
                'dana_sosial' => ['label' => 'Dana Sosial (%)', 'icon' => 'fas fa-hands-helping', 'type' => 'number', 'step' => '0.01'],
            ],
            'Jasa & Bagi Hasil' => [
                'jasa_usaha' => ['label' => 'Jasa Usaha (%)', 'icon' => 'fas fa-briefcase', 'type' => 'number', 'step' => '0.01'],
                'jasa_modal' => ['label' => 'Jasa Modal (%)', 'icon' => 'fas fa-coins', 'type' => 'number', 'step' => '0.01'],
            ]
        ];
        
        $data = [];
        foreach($sukuBunga as $item) {
            $data[$item->opsi_key] = $item->opsi_val;
        }
    @endphp

    <!-- Main Form Container -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden">
        <!-- Form Header -->
        <div class="bg-gradient-to-r from-green-600 to-green-700 px-6 py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <div class="bg-white bg-opacity-20 p-2 rounded-lg">
                        <i class="fas fa-percentage text-white text-xl"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-white">Pengaturan Suku Bunga & Biaya</h2>
                        <p class="text-green-100 text-sm">Kelola komponen keuangan dan suku bunga koperasi</p>
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
        <form id="sukuBungaForm" method="POST" action="{{ route('settings.suku_bunga.update') }}" class="p-6">
            @csrf
            
            @foreach($fieldGroups as $groupTitle => $fields)
            <div class="mb-8">
                <!-- Group Header -->
                <div class="flex items-center mb-4 pb-2 border-b border-gray-200">
                    <div class="bg-green-50 p-2 rounded-lg mr-3">
                        <i class="fas fa-folder text-green-600"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-800">{{ $groupTitle }}</h3>
                </div>

                <!-- Group Fields -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @foreach($fields as $key => $fieldConfig)
                    <div>
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
                            $inputStep = $fieldConfig['step'] ?? '';
                            
                            // Format currency values
                            if(isset($fieldConfig['format']) && $fieldConfig['format'] == 'currency' && is_numeric($inputValue)) {
                                $inputValue = number_format($inputValue, 0, ',', '.');
                            }
                        @endphp
                        
                        <div class="relative">
                            <input type="{{ $inputType }}" 
                                   name="{{ $key }}" 
                                   class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-transparent transition-all duration-200 suku-bunga-input bg-gray-50" 
                                   value="{{ $inputValue }}" 
                                   placeholder="Masukkan {{ strtolower($fieldConfig['label']) }}"
                                   @if($inputStep) step="{{ $inputStep }}" @endif
                                   readonly>
                            @if(isset($fieldConfig['format']) && $fieldConfig['format'] == 'currency')
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">Rp</span>
                                </div>
                            @elseif($inputType == 'number' && !isset($fieldConfig['format']))
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 text-sm">%</span>
                                </div>
                            @endif
                        </div>
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
                <button type="submit" id="saveBtn" class="px-6 py-3 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all duration-200 flex items-center space-x-2" style="display:none;">
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
            const inputs = document.querySelectorAll('.suku-bunga-input');
            const form = document.getElementById('sukuBungaForm');
            
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
                        input.classList.add('bg-white', 'border-green-300');
                    });
                    
                    // Show/hide buttons
                    editBtn.style.display = 'none';
                    saveBtn.style.display = 'flex';
                    cancelBtn.style.display = 'flex';
                    
                    // Add visual feedback
                    const header = document.querySelector('.bg-gradient-to-r');
                    if(header) {
                        header.classList.add('from-orange-600', 'to-orange-700');
                        header.classList.remove('from-green-600', 'to-green-700');
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
                        input.classList.remove('bg-white', 'border-green-300');
                    });
                    
                    // Show/hide buttons
                    editBtn.style.display = 'flex';
                    saveBtn.style.display = 'none';
                    cancelBtn.style.display = 'none';
                    
                    // Restore header color
                    const header = document.querySelector('.bg-gradient-to-r');
                    if(header) {
                        header.classList.add('from-green-600', 'to-green-700');
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

                // Format currency inputs on input
                input.addEventListener('input', function() {
                    if (this.hasAttribute('readonly')) return;
                    
                    // Format currency inputs
                    if (this.name.includes('biaya') || this.name.includes('denda')) {
                        let value = this.value.replace(/[^\d]/g, '');
                        if (value) {
                            this.value = new Intl.NumberFormat('id-ID').format(value);
                        }
                    }
                });
            });
        });
    </script>
</div>

<div class="popup">

</div>
@endsection