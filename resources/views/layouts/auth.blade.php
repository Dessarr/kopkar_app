<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Koperasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
    body {
        font-family: 'Poppins', sans-serif;
    }
    </style>
</head>

<body class="bg-white">
    <div class="flex h-screen">
        <!-- Panel Kiri -->
        <div id="left-panel"
            class="w-1/2 flex items-center justify-center transition-all duration-700 bg-fixed bg-white">
            @yield('left-panel')
        </div>
        <!-- Panel Kanan -->
        <div id="right-panel"
            class="w-1/2 flex items-center justify-center transition-all duration-700 bg-fixed bg-[white]">
            @yield('right-panel')
        </div>
    </div>
</body>

</html>