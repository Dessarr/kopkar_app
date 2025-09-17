@extends('layouts.member')

@section('title', 'Ubah Password')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Ubah Password</h1>
                        <p class="text-sm text-gray-600 mt-1">Perbarui password Anda untuk keamanan akun</p>
                    </div>
                    <div class="flex items-center">
                        <a href="{{ route('member.dashboard') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
        <div class="bg-green-50 border border-green-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-check-circle text-green-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button type="button" class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-green-50 focus:ring-green-600" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display='none'">
                            <span class="sr-only">Dismiss</span>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button type="button" class="inline-flex bg-red-50 rounded-md p-1.5 text-red-500 hover:bg-red-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-red-50 focus:ring-red-600" onclick="this.parentElement.parentElement.parentElement.parentElement.style.display='none'">
                            <span class="sr-only">Dismiss</span>
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="bg-red-50 border border-red-200 rounded-md p-4 mb-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-exclamation-circle text-red-400"></i>
                </div>
                <div class="ml-3">
                    <div class="text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Main Content -->
        <div class="bg-white shadow-sm rounded-lg">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-medium text-gray-900">Form Ubah Password</h3>
            </div>
            <div class="p-6">
                <form action="{{ route('member.update.password') }}" method="POST" id="passwordForm">
                    @csrf
                    
                    <!-- Password Lama -->
                    <div class="mb-6">
                        <label for="password_lama" class="block text-sm font-medium text-gray-700 mb-2">
                            Password Lama <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password_lama" 
                                   name="password_lama" 
                                   class="form-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('password_lama') border-red-500 @enderror" 
                                   placeholder="Masukkan password lama Anda"
                                   required>
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onclick="togglePassword('password_lama')">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password_lama_icon"></i>
                            </button>
                        </div>
                        @error('password_lama')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password Baru -->
                    <div class="mb-6">
                        <label for="password_baru" class="block text-sm font-medium text-gray-700 mb-2">
                            Password Baru <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password_baru" 
                                   name="password_baru" 
                                   class="form-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('password_baru') border-red-500 @enderror" 
                                   placeholder="Masukkan password baru (minimal 6 karakter)"
                                   minlength="6"
                                   required>
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onclick="togglePassword('password_baru')">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password_baru_icon"></i>
                            </button>
                        </div>
                        @error('password_baru')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-2">
                            <div class="flex items-center">
                                <div class="flex-1 bg-gray-200 rounded-full h-2">
                                    <div class="bg-gray-300 h-2 rounded-full transition-all duration-300" id="password-strength-bar"></div>
                                </div>
                                <span class="ml-2 text-sm text-gray-600" id="password-strength-text">Lemah</span>
                            </div>
                        </div>
                    </div>

                    <!-- Konfirmasi Password Baru -->
                    <div class="mb-6">
                        <label for="password_baru_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                            Konfirmasi Password Baru <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input type="password" 
                                   id="password_baru_confirmation" 
                                   name="password_baru_confirmation" 
                                   class="form-input w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 @error('password_baru_confirmation') border-red-500 @enderror" 
                                   placeholder="Ulangi password baru"
                                   minlength="6"
                                   required>
                            <button type="button" 
                                    class="absolute inset-y-0 right-0 pr-3 flex items-center"
                                    onclick="togglePassword('password_baru_confirmation')">
                                <i class="fas fa-eye text-gray-400 hover:text-gray-600" id="password_baru_confirmation_icon"></i>
                            </button>
                        </div>
                        @error('password_baru_confirmation')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <div class="mt-2">
                            <div id="password-match-indicator" class="hidden">
                                <i class="fas fa-check text-green-500 mr-1"></i>
                                <span class="text-sm text-green-600">Password cocok</span>
                            </div>
                            <div id="password-mismatch-indicator" class="hidden">
                                <i class="fas fa-times text-red-500 mr-1"></i>
                                <span class="text-sm text-red-600">Password tidak cocok</span>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="flex items-center justify-between">
                        <button type="button" onclick="clearForm()" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium transition-colors">
                            <i class="fas fa-times mr-2"></i>Batal
                        </button>
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                            <i class="fas fa-key mr-2"></i>Ubah Password
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Security Tips Section -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-shield-alt text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Tips Keamanan Password</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Gunakan minimal 8 karakter dengan kombinasi huruf, angka, dan simbol</li>
                            <li>Hindari menggunakan informasi pribadi seperti nama atau tanggal lahir</li>
                            <li>Jangan gunakan password yang sama dengan akun lain</li>
                            <li>Ganti password secara berkala untuk keamanan maksimal</li>
                            <li>Jangan bagikan password Anda kepada siapapun</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Password Requirements -->
        <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 mt-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-info-circle text-yellow-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">Persyaratan Password</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Minimal 6 karakter</li>
                            <li>Password baru harus berbeda dengan password lama</li>
                            <li>Konfirmasi password harus sama dengan password baru</li>
                            <li>Password akan dienkripsi untuk keamanan</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function togglePassword(fieldId) {
    const field = document.getElementById(fieldId);
    const icon = document.getElementById(fieldId + '_icon');
    
    if (field.type === 'password') {
        field.type = 'text';
        icon.classList.remove('fa-eye');
        icon.classList.add('fa-eye-slash');
    } else {
        field.type = 'password';
        icon.classList.remove('fa-eye-slash');
        icon.classList.add('fa-eye');
    }
}

function checkPasswordStrength(password) {
    let strength = 0;
    let strengthText = 'Lemah';
    let strengthColor = 'bg-red-500';
    
    if (password.length >= 6) strength++;
    if (password.length >= 8) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    if (strength >= 5) {
        strengthText = 'Sangat Kuat';
        strengthColor = 'bg-green-500';
    } else if (strength >= 4) {
        strengthText = 'Kuat';
        strengthColor = 'bg-blue-500';
    } else if (strength >= 3) {
        strengthText = 'Sedang';
        strengthColor = 'bg-yellow-500';
    } else if (strength >= 2) {
        strengthText = 'Lemah';
        strengthColor = 'bg-orange-500';
    } else {
        strengthText = 'Sangat Lemah';
        strengthColor = 'bg-red-500';
    }
    
    document.getElementById('password-strength-bar').className = strengthColor + ' h-2 rounded-full transition-all duration-300';
    document.getElementById('password-strength-text').textContent = strengthText;
}

function checkPasswordMatch() {
    const password = document.getElementById('password_baru').value;
    const confirmPassword = document.getElementById('password_baru_confirmation').value;
    const matchIndicator = document.getElementById('password-match-indicator');
    const mismatchIndicator = document.getElementById('password-mismatch-indicator');
    
    if (confirmPassword.length > 0) {
        if (password === confirmPassword) {
            matchIndicator.classList.remove('hidden');
            mismatchIndicator.classList.add('hidden');
        } else {
            matchIndicator.classList.add('hidden');
            mismatchIndicator.classList.remove('hidden');
        }
    } else {
        matchIndicator.classList.add('hidden');
        mismatchIndicator.classList.add('hidden');
    }
}

function clearForm() {
    document.getElementById('passwordForm').reset();
    document.getElementById('password-strength-bar').className = 'bg-gray-300 h-2 rounded-full transition-all duration-300';
    document.getElementById('password-strength-text').textContent = 'Lemah';
    document.getElementById('password-match-indicator').classList.add('hidden');
    document.getElementById('password-mismatch-indicator').classList.add('hidden');
}

// Event listeners
document.getElementById('password_baru').addEventListener('input', function() {
    checkPasswordStrength(this.value);
    checkPasswordMatch();
});

document.getElementById('password_baru_confirmation').addEventListener('input', checkPasswordMatch);

// Form validation
document.getElementById('passwordForm').addEventListener('submit', function(e) {
    const passwordLama = document.getElementById('password_lama').value;
    const passwordBaru = document.getElementById('password_baru').value;
    const confirmPassword = document.getElementById('password_baru_confirmation').value;
    
    if (!passwordLama || !passwordBaru || !confirmPassword) {
        e.preventDefault();
        alert('Semua field harus diisi.');
        return false;
    }
    
    if (passwordBaru.length < 6) {
        e.preventDefault();
        alert('Password baru minimal 6 karakter.');
        return false;
    }
    
    if (passwordBaru !== confirmPassword) {
        e.preventDefault();
        alert('Konfirmasi password tidak sama.');
        return false;
    }
    
    if (passwordLama === passwordBaru) {
        e.preventDefault();
        alert('Password baru harus berbeda dengan password lama.');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengubah Password...';
        submitBtn.disabled = true;
    }
});
</script>
@endsection
