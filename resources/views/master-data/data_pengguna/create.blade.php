@extends('layouts.app')

@section('title', 'Tambah Data Pengguna')
@section('sub-title', 'Tambah Data Pengguna Baru')

@section('content')
<div class="px-1 justify-center flex flex-col">
    <!-- Header Section -->
    <div class="bg-gradient-to-r from-green-600 to-green-700 text-white p-6 rounded-lg mb-6">
        <div class="flex justify-between items-center">
            <div>
                <h1 class="text-2xl font-bold">Tambah Data Pengguna</h1>
                <p class="text-green-100">Buat akun pengguna baru untuk sistem</p>
            </div>
            <div class="text-right">
                <a href="{{ route('master-data.data_pengguna') }}" class="bg-white bg-opacity-20 hover:bg-opacity-30 text-white px-4 py-2 rounded-lg transition">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali
                </a>
            </div>
        </div>
    </div>

    <!-- Form Section -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-6">
            @if ($errors->any())
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6">
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('master-data.data_pengguna.store') }}" method="POST" class="space-y-6">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Username -->
                    <div>
                        <label for="u_name" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user mr-2"></i>Username <span class="text-red-500">*</span>
                        </label>
                        <input type="text" id="u_name" name="u_name" value="{{ old('u_name') }}" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('u_name') border-red-500 @enderror"
                               placeholder="Masukkan username" required>
                        @error('u_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="pass_word" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-lock mr-2"></i>Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" id="pass_word" name="pass_word" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('pass_word') border-red-500 @enderror"
                               placeholder="Masukkan password (min. 6 karakter)" required>
                        @error('pass_word')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Level -->
                    <div>
                        <label for="level" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-user-tag mr-2"></i>Level <span class="text-red-500">*</span>
                        </label>
                        <select id="level" name="level" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('level') border-red-500 @enderror" required>
                            <option value="">Pilih Level</option>
                            <option value="admin" {{ old('level') == 'admin' ? 'selected' : '' }}>Administrator</option>
                            <option value="manager" {{ old('level') == 'manager' ? 'selected' : '' }}>Manager</option>
                            <option value="supervisor" {{ old('level') == 'supervisor' ? 'selected' : '' }}>Supervisor</option>
                            <option value="pinjaman" {{ old('level') == 'pinjaman' ? 'selected' : '' }}>Staff Pinjaman</option>
                            <option value="simpanan" {{ old('level') == 'simpanan' ? 'selected' : '' }}>Staff Simpanan</option>
                            <option value="kas" {{ old('level') == 'kas' ? 'selected' : '' }}>Staff Kas</option>
                            <option value="laporan" {{ old('level') == 'laporan' ? 'selected' : '' }}>Staff Laporan</option>
                        </select>
                        @error('level')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Cabang -->
                    <div>
                        <label for="id_cabang" class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-building mr-2"></i>Cabang <span class="text-red-500">*</span>
                        </label>
                        <select id="id_cabang" name="id_cabang" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent @error('id_cabang') border-red-500 @enderror" required>
                            <option value="">Pilih Cabang</option>
                            @if(isset($cabangs) && $cabangs->count() > 0)
                                @foreach($cabangs as $cabang)
                                    <option value="{{ $cabang->id_cabang }}" {{ old('id_cabang') == $cabang->id_cabang ? 'selected' : '' }}>
                                        {{ $cabang->nama }}
                                    </option>
                                @endforeach
                            @else
                                <option value="" disabled>Data cabang tidak tersedia</option>
                            @endif
                        </select>
                        @error('id_cabang')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        @if(isset($cabangs) && $cabangs->count() == 0)
                            <p class="mt-1 text-sm text-red-600">Tidak ada data cabang tersedia. Silakan hubungi administrator.</p>
                        @endif
                    </div>

                    <!-- Status Aktif -->
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            <i class="fas fa-toggle-on mr-2"></i>Status Aktif <span class="text-red-500">*</span>
                        </label>
                        <div class="flex space-x-4">
                            <label class="flex items-center">
                                <input type="radio" name="aktif" value="Y" {{ old('aktif', 'Y') == 'Y' ? 'checked' : '' }} 
                                       class="mr-2 text-green-600 focus:ring-green-500">
                                <span class="text-sm text-gray-700">Aktif</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="aktif" value="N" {{ old('aktif') == 'N' ? 'checked' : '' }} 
                                       class="mr-2 text-red-600 focus:ring-red-500">
                                <span class="text-sm text-gray-700">Tidak Aktif</span>
                            </label>
                        </div>
                        @error('aktif')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex justify-end space-x-4 pt-6 border-t">
                    <a href="{{ route('master-data.data_pengguna') }}" 
                       class="px-6 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                    <button type="submit" 
                            class="px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition">
                        <i class="fas fa-save mr-2"></i>Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Password strength indicator
document.getElementById('pass_word').addEventListener('input', function() {
    const password = this.value;
    const strength = getPasswordStrength(password);
    
    // Remove existing strength indicator
    const existingIndicator = document.getElementById('password-strength');
    if (existingIndicator) {
        existingIndicator.remove();
    }
    
    // Add strength indicator
    const indicator = document.createElement('div');
    indicator.id = 'password-strength';
    indicator.className = 'mt-1 text-sm';
    
    if (password.length > 0) {
        if (strength < 3) {
            indicator.className += ' text-red-600';
            indicator.textContent = 'Password lemah';
        } else if (strength < 5) {
            indicator.className += ' text-yellow-600';
            indicator.textContent = 'Password sedang';
        } else {
            indicator.className += ' text-green-600';
            indicator.textContent = 'Password kuat';
        }
    }
    
    this.parentNode.appendChild(indicator);
});

function getPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    return strength;
}
</script>
@endsection