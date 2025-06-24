<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Dashboard Koperasi')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
        }
        .sidebar {
            background-color: #1abc9c;
            min-height: 100vh;
            height: 100%;
        }
        .sidebar-item:hover {
            background-color: #16a085;
        }
        .active {
            background-color: #16a085;
            border-left: 4px solid #ffffff;
        }
    </style>
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 sidebar text-white">
            @include('layouts.sidebar')
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col">
            <!-- Top Bar -->
            <div class="bg-white shadow-md p-4 flex justify-between items-center">
                <h1 class="text-2xl font-bold text-gray-800">Dashboard</h1>
                <div class="flex items-center text-gray-600">
                    <span id="date" class="mr-4"></span>
                    <span id="time"></span>
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
        
        const dateOptions = { day: 'numeric', month: 'long', year: 'numeric' };
        dateEl.textContent = now.toLocaleDateString('id-ID', dateOptions);

        const timeOptions = { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: false };
        timeEl.textContent = now.toLocaleTimeString('id-ID', timeOptions);
    }
    updateTime();
    setInterval(updateTime, 1000);
</script>
</body>
</html> 