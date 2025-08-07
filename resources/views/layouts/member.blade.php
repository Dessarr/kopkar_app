<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Member Dashboard')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    .navbar {
        background-color: #14AE5C;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        z-index: 50;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .main-content {
        margin-top: 60px;
        min-height: calc(100vh - 60px);
        background-color: #f3f4f6;
    }

    .nav-item {
        transition: all 0.3s ease;
    }

    .nav-item:hover {
        background-color: rgba(255, 255, 255, 0.1);
        transform: translateY(-1px);
    }

    .nav-item.active {
        background-color: rgba(255, 255, 255, 0.2);
        border-bottom: 2px solid white;
    }

    .dropdown-menu {
        background-color: white;
        border: 1px solid #e5e7eb;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        border-radius: 0.5rem;
    }

    .dropdown-item:hover {
        background-color: #f3f4f6;
    }

    @media (max-width: 768px) {
        .navbar-menu {
            position: fixed;
            top: 60px;
            left: 0;
            right: 0;
            background-color: #14AE5C;
            transform: translateY(-100%);
            transition: transform 0.3s ease;
        }

        .navbar-menu.open {
            transform: translateY(0);
        }
    }
    </style>
</head>

<body class="bg-gray-200">
    <!-- Navbar -->
    <nav class="navbar" x-data="{ mobileMenuOpen: false, dropdownOpen: false }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex justify-between items-center h-full">
                <!-- Logo and Brand -->
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="flex items-center">
                            <div class="bg-white p-2 rounded-md mr-3">
                                <svg class="w-8 h-8 text-[#14AE5C]" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="#14AE5C" stroke-width="2" fill="#fff" />
                                    <path d="M12 6v6l4 2" stroke="#14AE5C" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round" />
                                </svg>
                            </div>
                            <span class="text-xl font-bold text-white">KOPERASI INDONESIA</span>
                        </div>
                    </div>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:block">
                    <div class="flex items-baseline space-x-3">
                        <a href="{{ route('member.dashboard') }}"
                            class="nav-item px-3 py-2 rounded-md text-md font-medium text-white">
                            <i class="fa-solid fa-house"></i>
                        </a>
                        <a href="{{ route('member.dashboard') }}"
                            class="nav-item px-3 py-2 rounded-md text-md font-medium text-white {{ request()->routeIs('member.dashboard') ? 'active' : '' }}">
                            Beranda
                        </a>
                        <!-- Pengajuan Pinjaman Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="nav-item px-3 py-2 rounded-md text-sm font-medium text-white flex items-center">
                                Pengajuan Pinjaman
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false"
                                class="dropdown-menu absolute right-0 mt-2 w-auto py-2 z-50">
                                <a href="{{ route('member.pengajuan.pinjaman') }}"
                                    class="dropdown-item block px-4 py-2 text-sm text-gray-700 whitespace-nowrap">Data
                                    Pengajuan</a>
                                <a href="{{ route('member.tambah.pengajuan.pinjaman') }}"
                                    class="dropdown-item block px-4 py-2 text-sm text-gray-700 whitespace-nowrap">Tambah
                                    Pengajuan
                                    Baru</a>
                            </div>
                        </div>

                        <!-- Pengajuan Penarikan Simpanan Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="nav-item px-3 py-2 rounded-md text-sm font-medium text-white flex items-center">
                                Pengajuan Penarikan Simpanan
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false"
                                class="dropdown-menu absolute right-0 mt-2 w-auto py-2 z-50">
                                <a href="{{ route('member.pengajuan.penarikan') }}"
                                    class="dropdown-item block px-4 py-2 text-sm text-gray-700 whitespace-nowrap">Data
                                    Pengajuan
                                    Penarikan</a>
                                <a href="{{ route('member.laporan.simpanan') }}"
                                    class="dropdown-item block px-4 py-2 text-sm text-gray-700 whitespace-nowrap">Tambah
                                    Pengajuan
                                    Penarikan Baru</a>
                            </div>
                        </div>

                        <!-- Laporan Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="nav-item px-3 py-2 rounded-md text-sm font-medium text-white flex items-center">
                                Laporan
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false"
                                class="dropdown-menu absolute right-0 mt-2 w-auto py-2 z-50">
                                <a href="{{ route('member.laporan') }}"
                                    class="dropdown-item block px-4 py-2 text-sm text-gray-700 whitespace-nowrap">Pinjaman</a>
                                <a href="{{ route('member.laporan.simpanan') }}"
                                    class="dropdown-item block px-4 py-2 text-sm text-gray-700 whitespace-nowrap">Pembayaran
                                    Pinjaman</a>
                                <a href="{{ route('member.laporan.pinjaman') }}"
                                    class="dropdown-item block px-4 py-2 text-sm text-gray-700 whitespace-nowrap">Simpanan
                                    - Toserda</a>
                            </div>
                        </div>

                        <!-- Profile Dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open"
                                class="nav-item px-3 py-2 rounded-md text-sm font-medium text-white flex items-center">
                                Profile
                                <svg class="ml-1 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </button>
                            <div x-show="open" @click.away="open = false"
                                class="dropdown-menu absolute right-0 mt-2 w-48 py-2 z-50">
                                <a href="" class="dropdown-item block px-4 py-2 text-sm text-gray-700">Ubah Pic</a>
                                <a href="" class="dropdown-item block px-4 py-2 text-sm text-gray-700">Ubah Password</a>
                                <form action="{{ route('admin.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit"
                                        class="dropdown-item block w-full text-left px-4 py-2 text-sm text-gray-700">
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden">
                    <button @click="mobileMenuOpen = !mobileMenuOpen" class="nav-item p-2 rounded-md text-white">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="md:hidden navbar-menu" :class="{ 'open': mobileMenuOpen }">
            <div class="px-2 pt-2 pb-3 space-y-1 sm:px-3">
                <a href="{{ route('member.dashboard') }}"
                    class="nav-item block px-3 py-2 rounded-md text-base font-medium text-white {{ request()->routeIs('member.dashboard') ? 'active' : '' }}">
                    Member
                </a>
                <a href="{{ route('member.beranda') }}"
                    class="nav-item block px-3 py-2 rounded-md text-base font-medium text-white {{ request()->routeIs('member.beranda') ? 'active' : '' }}">
                    Beranda
                </a>
                <a href="{{ route('member.pengajuan.pinjaman') }}"
                    class="nav-item block px-3 py-2 rounded-md text-base font-medium text-white {{ request()->routeIs('member.pengajuan.pinjaman') ? 'active' : '' }}">
                    Pengajuan Pinjaman
                </a>
                <a href="{{ route('member.pengajuan.penarikan') }}"
                    class="nav-item block px-3 py-2 rounded-md text-base font-medium text-white {{ request()->routeIs('member.pengajuan.penarikan') ? 'active' : '' }}">
                    Pengajuan Penarikan Simpanan
                </a>
                <a href="{{ route('member.laporan') }}"
                    class="nav-item block px-3 py-2 rounded-md text-base font-medium text-white {{ request()->routeIs('member.laporan') ? 'active' : '' }}">
                    Laporan
                </a>
                <form action="{{ route('member.logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="nav-item block w-full text-left px-3 py-2 rounded-md text-base font-medium text-white">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            @yield('content')
        </div>
    </div>

    <script>
    function updateTime() {
        const now = new Date();
        const dateOptions = {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        };
        const timeOptions = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
    }
    updateTime();
    setInterval(updateTime, 1000);
    </script>

    @stack('scripts')
</body>

</html>