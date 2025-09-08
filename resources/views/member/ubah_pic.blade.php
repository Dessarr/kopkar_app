@extends('layouts.member')

@section('title', 'Ubah Foto Profil')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="bg-white shadow-sm rounded-lg mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-900">Ubah Foto Profil</h1>
                        <p class="text-sm text-gray-600 mt-1">Perbarui foto profil Anda dengan mudah dan aman</p>
                    </div>
                    <div class="flex items-center">
                        <a href="{{ route('member.profile') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                            <i class="fas fa-arrow-left mr-2"></i>Kembali ke Profil
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
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Current Photo Section -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Foto Profil Saat Ini</h3>
                </div>
                <div class="p-6">
                    <div class="flex flex-col items-center">
                        <div class="relative">
                            <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-gray-200 shadow-lg">
                                @if($member->file_pic && \Storage::disk('public')->exists('anggota/' . $member->file_pic))
                                    <img src="{{ asset('storage/anggota/' . $member->file_pic) }}?v={{ time() }}" 
                                         alt="Foto Profil" 
                                         class="w-full h-full object-cover"
                                         id="currentProfilePhoto">
                                @else
                                    <div class="w-full h-full bg-gray-200 flex items-center justify-center">
                                        <i class="fas fa-user text-gray-400 text-4xl"></i>
                                    </div>
                                @endif
                            </div>
                            @if($member->file_pic)
                            <div class="absolute -bottom-2 -right-2 bg-green-500 text-white rounded-full p-2">
                                <i class="fas fa-check text-xs"></i>
                            </div>
                            @endif
                        </div>
                        <div class="mt-4 text-center">
                            <p class="text-sm text-gray-600">
                                @if($member->file_pic)
                                    Foto profil sudah diatur
                                @else
                                    Belum ada foto profil
                                @endif
                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                Ukuran: 250x250 pixel
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Form Section -->
            <div class="bg-white shadow-sm rounded-lg">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-lg font-medium text-gray-900">Upload Foto Baru</h3>
                </div>
                <div class="p-6">
                    <form action="{{ route('member.update.pic') }}" method="POST" enctype="multipart/form-data" id="uploadForm">
                        @csrf
                        
                        <!-- File Upload Area -->
                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih Foto
                            </label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md hover:border-gray-400 transition-colors" id="dropZone">
                                <div class="space-y-1 text-center">
                                    <div class="mx-auto h-12 w-12 text-gray-400" id="uploadIcon">
                                        <i class="fas fa-cloud-upload-alt text-3xl"></i>
                                    </div>
                                    <div class="flex text-sm text-gray-600">
                                        <label for="photo" class="relative cursor-pointer bg-white rounded-md font-medium text-blue-600 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                            <span>Upload file</span>
                                            <input id="photo" name="photo" type="file" class="sr-only" accept="image/*" onchange="previewImage(this)">
                                        </label>
                                        <p class="pl-1">atau drag and drop</p>
                                    </div>
                                    <p class="text-xs text-gray-500">
                                        PNG, JPG, GIF hingga 1MB
                                    </p>
                                </div>
                            </div>
                            
                            <!-- Preview Area -->
                            <div id="previewArea" class="mt-4 hidden">
                                <div class="flex items-center justify-center">
                                    <div class="relative">
                                        <img id="previewImage" src="" alt="Preview" class="w-32 h-32 rounded-full object-cover border-4 border-gray-200 shadow-lg">
                                        <button type="button" onclick="clearPreview()" class="absolute -top-2 -right-2 bg-red-500 text-white rounded-full p-1 hover:bg-red-600">
                                            <i class="fas fa-times text-xs"></i>
                                        </button>
                                    </div>
                                </div>
                                <p class="text-center text-sm text-gray-600 mt-2" id="fileName"></p>
                            </div>
                        </div>

                        <!-- File Requirements -->
                        <div class="mb-6">
                            <h4 class="text-sm font-medium text-gray-700 mb-3">Persyaratan Foto:</h4>
                            <ul class="text-sm text-gray-600 space-y-1">
                                <li class="flex items-center">
                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                    Format: JPG, PNG, GIF
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                    Ukuran maksimal: 1MB
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                    Dimensi maksimal: 2000x2000 pixel
                                </li>
                                <li class="flex items-center">
                                    <i class="fas fa-check text-green-500 mr-2"></i>
                                    Akan diresize otomatis ke 250x250 pixel
                                </li>
                            </ul>
                        </div>

                        <!-- Submit Button -->
                        <div class="flex items-center justify-between">
                            <button type="button" onclick="clearForm()" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md font-medium transition-colors">
                                <i class="fas fa-times mr-2"></i>Batal
                            </button>
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                                <i class="fas fa-upload mr-2"></i>Upload Foto
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>


        <!-- Tips Section -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-6 mt-6">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="fas fa-lightbulb text-blue-400"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-800">Tips Foto Profil yang Baik</h3>
                    <div class="mt-2 text-sm text-blue-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>Gunakan foto yang jelas dan terlihat wajah Anda</li>
                            <li>Pastikan pencahayaan cukup dan tidak terlalu gelap</li>
                            <li>Hindari foto yang blur atau tidak fokus</li>
                            <li>Foto akan otomatis diresize menjadi bentuk persegi</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function previewImage(input) {
    if (input.files && input.files[0]) {
        const file = input.files[0];
        const reader = new FileReader();
        
        reader.onload = function(e) {
            document.getElementById('previewImage').src = e.target.result;
            document.getElementById('fileName').textContent = file.name;
            document.getElementById('previewArea').classList.remove('hidden');
            document.getElementById('uploadIcon').classList.add('hidden');
        }
        
        reader.readAsDataURL(file);
    }
}

function clearPreview() {
    document.getElementById('photo').value = '';
    document.getElementById('previewArea').classList.add('hidden');
    document.getElementById('uploadIcon').classList.remove('hidden');
}

function clearForm() {
    clearPreview();
}

// Drag and drop functionality
const dropZone = document.getElementById('dropZone');
const fileInput = document.getElementById('photo');

dropZone.addEventListener('dragover', function(e) {
    e.preventDefault();
    dropZone.classList.add('border-blue-400', 'bg-blue-50');
});

dropZone.addEventListener('dragleave', function(e) {
    e.preventDefault();
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
});

dropZone.addEventListener('drop', function(e) {
    e.preventDefault();
    dropZone.classList.remove('border-blue-400', 'bg-blue-50');
    
    const files = e.dataTransfer.files;
    if (files.length > 0) {
        fileInput.files = files;
        previewImage(fileInput);
    }
});

// Form validation - simplified
document.getElementById('uploadForm').addEventListener('submit', function(e) {
    const fileInput = document.getElementById('photo');
    
    if (!fileInput.files || fileInput.files.length === 0) {
        e.preventDefault();
        alert('Silakan pilih foto terlebih dahulu.');
        return false;
    }
    
    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Mengupload...';
        submitBtn.disabled = true;
    }
});
</script>
@endsection
