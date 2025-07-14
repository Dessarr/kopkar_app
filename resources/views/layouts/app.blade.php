<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
        min-height: 200vh;
        height: 100%;
    }

    .sidebar-item:active {
        background-color: rgb(18, 188, 98);
        color: #ffffff;
    }



    .active {
        background-color: rgb(18, 188, 98);
        border-left: 4px solid #ffffff;
    }
    </style>
</head>

<body class="bg-gray-200">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 sidebar text-[#6F757E] mt-6 ml-6 rounded-lg shadow-md">
            @include('layouts.sidebar')
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col overflow-hidden">
            <!-- Top Bar -->
            <div class="bg-[#14AE5C] p-4 flex justify-between items-center mt-6 mx-6 shadow-md rounded-lg">
                <div class="flex flex-row items-center space-x-4">
                    <h1 class="text-2xl font-bold text-white">@yield('title', 'dashboard')</h1>
                    <h2 class="text-gray-200 flex text-center align-middle justify-center">@yield('sub-title','Menu
                        Utama')
                    </h2>
                </div>
                <div class="flex items-center text-white">
                    <span id="date" class="mr-4"></span>
                    <span id="time"></span>
                </div>
            </div>

            <!-- Content Area -->
            <main class="flex-1 p-6 overflow-y-auto overflow-hidden">

                <main class="flex-1 p-6 overflow-y-auto overflow-x-hidden">

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
</body>

</html>