<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard Koperasi')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

    <style>
    body {
        font-family: 'Poppins', sans-serif;
    }

    .sidebar {
        background-color: #ffff;
        height: calc(100vh - 3rem);
        overflow-y: auto;
        overflow-x: hidden;
    }

    .sidebar-item:active {
        background-color: rgb(18, 188, 98);
        color: #ffffff;
    }

    .active {
        background-color: rgb(18, 188, 98);
        border-left: 4px solid #ffffff;
    }

    /* Floating Burger Menu */
    .floating-burger {
        position: fixed;
        top: 1.5rem;
        left: 0.5rem;
        z-index: 50;
        transition: all 0.3s ease;
        display: none;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: #14AE5C;
        color: white;
        border: 2px solid white;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
    }

    .floating-burger:hover {
        background-color: #0f8a4a;
        transform: scale(1.1);
    }

    .floating-burger:active {
        transform: scale(0.95);
    }

    .floating-burger.open {
        left: 18rem;
        background-color: white;
        color: #374151;
        border: 2px solid #e5e7eb;
    }

    .floating-burger.open:hover {
        background-color: #f3f4f6;
    }

    /* Main Content Responsive */
    .main-content {
        height: calc(100vh - 3rem);
        overflow-y: auto;
    }

    @media (max-width: 1023px) {
        .sidebar {
            position: fixed;
            top: 0;
            left: -100%;
            width: 280px;
            height: 100vh;
            z-index: 40;
            transition: left 0.3s ease;
        }

        .sidebar.open {
            left: 0;
        }

        .floating-burger {
            display: flex !important;
            align-items: center;
            justify-content: center;
        }

        .main-content {
            margin-left: 0;
        }
    }

    @media (max-width: 768px) {
        .sidebar {
            width: 260px;
        }

        .floating-burger {
            top: 1rem;
            left: 0.5rem;
        }

        .floating-burger.open {
            left: 16rem;
        }
    }

    @media (max-width: 480px) {
        .sidebar {
            width: 240px;
        }

        .floating-burger {
            top: 0.75rem;
            left: 0.5rem;
        }

        .floating-burger.open {
            left: 14rem;
        }
    }

    @media (max-width: 360px) {
        .sidebar {
            width: 220px;
        }

        .floating-burger {
            top: 0.5rem;
            left: 0.25rem;
        }

        .floating-burger.open {
            left: 12rem;
        }
    }

    @media (min-width: 1024px) {
        .floating-burger {
            display: none !important;
        }

        .sidebar {
            position: relative;
            left: 0;
            width: 16rem;
        }

        .main-content {
            margin-left: 0;
        }
    }
    </style>
</head>

<body class="bg-gray-200">
    <div class="flex h-screen" x-data="{ sidebarOpen: false }">
        <!-- Floating Burger Menu -->
        <button class="floating-burger" @click="sidebarOpen = !sidebarOpen" :class="{ 'open': sidebarOpen }">
            <i class="fas fa-bars" x-show="!sidebarOpen"></i>
            <i class="fas fa-times" x-show="sidebarOpen"></i>
        </button>

        <!-- Sidebar -->
        <aside class="sidebar text-[#6F757E] mt-6 ml-6 rounded-lg shadow-md" :class="{ 'open': sidebarOpen }"
            @keydown.escape.window="sidebarOpen = false">
            @include('layouts.sidebar')
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col main-content">
            <!-- Top Bar -->
            <div class="bg-[#14AE5C] p-4 flex justify-between items-center mt-6 mx-6 shadow-md rounded-lg">
                <div class="flex flex-row items-center space-x-4">
                    <h1 class="text-2xl font-bold text-white">@yield('title', 'dashboard')</h1>
                    <h2 class="text-gray-200 flex text-center align-middle justify-center">@yield('sub-title','Menu
                        Utama')</h2>
                </div>
                <div class="flex items-center text-white space-x-4">
                    <!-- Dynamic Notifications Component -->
                    @include('components.notifications')
                    
                    <!-- Date and Time -->
                    <div class="flex items-center border-l border-white/30 pl-4">
                        <span id="date" class="mr-4"></span>
                        <span id="time"></span>
                    </div>
                </div>
            </div>

            <!-- Content Area -->
            <main class="flex-1 p-6 overflow-y-auto">
                @yield('content')
            </main>
        </div>
    </div>

    <script>
    function updateTime() {
        const dateEl = document.getElementById('date');
        const timeEl = document.getElementById('time');
        const now = new Date();

        const dateOptions = {
            day: 'numeric',
            month: 'long',
            year: 'numeric'
        };
        dateEl.textContent = now.toLocaleDateString('id-ID', dateOptions);

        const timeOptions = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        timeEl.textContent = now.toLocaleTimeString('id-ID', timeOptions);
    }
    updateTime();
    setInterval(updateTime, 1000);
    </script>

    @stack('scripts')
    
    <!-- Notification System -->
    <script src="{{ asset('js/notifications.js') }}"></script>
</body>

</html>