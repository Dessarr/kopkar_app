<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Not Found - Koperasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .floating {
            animation: floating 3s ease-in-out infinite;
        }
        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        .pulse-slow {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="gradient-bg min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        <!-- Main Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8 text-center">
            <!-- Icon -->
            <div class="floating mb-6">
                <div class="w-24 h-24 mx-auto bg-red-100 rounded-full flex items-center justify-center">
                    <i class="fas fa-user-slash text-4xl text-red-500"></i>
                </div>
            </div>
            
            <!-- Title -->
            <h1 class="text-3xl font-bold text-gray-800 mb-4">
                Account Not Found
            </h1>
            
            <!-- Subtitle -->
            <p class="text-gray-600 mb-6 leading-relaxed">
                Maaf, akun Anda tidak ditemukan dalam sistem. 
                <br>Mungkin akun telah dihapus atau belum terdaftar.
            </p>
            
            <!-- Error Code -->
            <div class="bg-gray-100 rounded-lg p-4 mb-6">
                <div class="flex items-center justify-center space-x-2">
                    <i class="fas fa-exclamation-triangle text-yellow-500"></i>
                    <span class="text-sm font-mono text-gray-600">Error Code: 404</span>
                </div>
            </div>
            
            <!-- Actions -->
            <div class="space-y-3">
                <a href="{{ route('member.login') }}" 
                   class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-6 rounded-lg transition duration-300 ease-in-out transform hover:scale-105 flex items-center justify-center space-x-2">
                    <i class="fas fa-sign-in-alt"></i>
                    <span>Kembali ke Login</span>
                </a>
                
                <a href="mailto:admin@koperasi.com" 
                   class="w-full bg-gray-200 hover:bg-gray-300 text-gray-700 font-semibold py-3 px-6 rounded-lg transition duration-300 ease-in-out flex items-center justify-center space-x-2">
                    <i class="fas fa-envelope"></i>
                    <span>Hubungi Admin</span>
                </a>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-white text-sm opacity-80">
                <i class="fas fa-heart text-red-400 pulse-slow"></i>
                Koperasi Digital System
            </p>
        </div>
    </div>
    
    <!-- Background Elements -->
    <div class="fixed inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-40 -right-40 w-80 h-80 bg-white opacity-10 rounded-full"></div>
        <div class="absolute -bottom-40 -left-40 w-80 h-80 bg-white opacity-10 rounded-full"></div>
    </div>
</body>
</html>
