<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Koperasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login/login-transition.css') }}">
    <style>
    body {
        font-family: 'Poppins', sans-serif;
    }
    </style>
</head>

<body>
    <main class="relative w-full h-screen overflow-hidden">
        <!-- Admin -->
        <section id="loginAdmin" class="absolute top-0 left-0 w-full h-full flex transition-slide z-20 translate-x-0">
            <div class="w-1/2 bg-white flex items-center justify-center">
                <div
                    class="w-full md:w-2/3 text-center bg-[#1abc9c] p-4 rounded-lg min-h-[75vh] md:h-3/4 justify-center flex flex-col mx-auto">
                    <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">Sign In to Admin</h2>
                    <p class="text-white mb-8">KOPERASI KARYAWAN<br />PT. KAO INDONESIA</p>

                    <!-- FORM LOGIN -->
                    <form class="px-4 md:px-8" id="loginAdminForm" method="POST"
                        action="{{ route('admin.login.post') }}">
                        @csrf

                        <!-- Username -->
                        <div class="mb-4">
                            <div
                                class="flex flex-row items-center bg-white rounded-lg border focus-within:ring-2 focus-within:ring-[#1abc9c]">
                                <label for="username"><i
                                        class="fa-solid fa-user text-gray-400 text-lg px-3"></i></label>
                                <input type="text" name="u_name" id="usernameAdmin" placeholder="Username"
                                    value="{{ old('username') }}"
                                    class="w-full px-4 py-3 md:py-4 bg-transparent outline-none" required />
                            </div>
                        </div>

                        <!-- Password -->
                        <div class="mb-6">
                            <div
                                class="flex flex-row items-center bg-white rounded-lg border focus-within:ring-2 focus-within:ring-[#1abc9c]">
                                <label for="password"><i
                                        class="fa-solid fa-lock text-gray-400 text-lg px-3"></i></label>
                                <input type="password" name="pass_word" id="passwordAdmin" placeholder="Password"
                                    class="w-full px-4 py-3 md:py-4 bg-transparent outline-none" required />
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" id="loginAdminButton"
                            class="w-full bg-white text-[#1abc9c] font-bold py-3 rounded-lg transition-all duration-300 ease-in-out hover:bg-[#16a085] hover:text-white">
                            Log In
                        </button>
                    </form>

                    <!-- ERROR MESSAGE -->
                    @if ($errors->has('login'))
                    <div class="mt-4 text-white bg-red-500 rounded-md px-4 py-2">
                        {{ $errors->first('login') }}
                    </div>
                    @endif
                </div>
            </div>
            <div class="w-1/2 bg-[#1abc9c] text-white flex flex-col justify-center items-center">
                <h2 class="text-2xl md:text-4xl font-bold mb-4">Member Login here!</h2>
                <p class="mb-8 text-center">Welcome to Koperasi Karyawan<br />PT. KAO Indonesia</p>
                <a href="javascript:void(0)" onclick="toMember()"
                    class="inline-block border-2 animate-bounce border-white text-white font-bold py-3 px-10 rounded-full transition-all duration-500 ease-in-out hover:bg-white hover:text-[#1abc9c]">
                    Click Here!
                </a>
            </div>
        </section>

        <!-- Member -->
        <section id="loginMember"
            class="absolute top-0 left-0 w-full h-full flex transition-slide z-10 translate-x-full">
            <div class="w-1/2 bg-[#1abc9c] text-white flex flex-col justify-center items-center">
                <h2 class="text-2xl md:text-4xl font-bold mb-4">Admin Login here!</h2>
                <p class="mb-8 text-center">Welcome to Koperasi Karyawan<br />PT. KAO Indonesia</p>
                <a href="javascript:void(0)" onclick="toAdmin()"
                    class="inline-block border-2 animate-bounce border-white text-white font-bold py-3 px-10 rounded-full transition-all duration-500 ease-in-out hover:bg-white hover:text-[#1abc9c]">
                    Click Here!
                </a>
            </div>
            <div class="w-1/2 bg-white flex items-center justify-center">
                <div
                    class="w-full md:w-2/3 text-center bg-[#1abc9c] p-4 rounded-lg min-h-[75vh] md:h-3/4 justify-center flex flex-col mx-auto">
                    <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">Sign In to Member</h2>
                    <p class="text-white mb-8">KOPERASI KARYAWAN<br />PT. KAO INDONESIA</p>
                    <form action="" method="POST" class="px-4 md:px-8" id="loginMemberForm">
                        @csrf
                        <div class="mb-4">
                            <div
                                class="flex flex-row items-center bg-white rounded-lg border focus-within:ring-2 focus-within:ring-[#1abc9c]">
                                <label for="username"><i
                                        class="fa-solid fa-user text-gray-400 text-lg px-3"></i></label>
                                <input type="text" name="username" id="usernameMember" placeholder="Username"
                                    value="{{ old('username') }}"
                                    class="w-full px-4 py-3 md:py-4 bg-transparent outline-none" required />
                            </div>
                        </div>
                        <div class="mb-6">
                            <div
                                class="flex flex-row items-center bg-white rounded-lg border focus-within:ring-2 focus-within:ring-[#1abc9c]">
                                <label for="password"><i
                                        class="fa-solid fa-lock text-gray-400 text-lg px-3"></i></label>
                                <input type="password" name="password" id="passwordMember" placeholder="Password"
                                    class="w-full px-4 py-3 md:py-4 bg-transparent outline-none" required />
                            </div>
                        </div>
                        <button type="submit" id="loginMemberButton"
                            class="w-full bg-white text-[#1abc9c] font-bold py-3 rounded-lg transition-all duration-300 ease-in-out hover:bg-[#16a085] hover:text-white">
                            Log In
                        </button>
                    </form>
                </div>
            </div>
        </section>
    </main>

    <script src="{{ asset('js/login/login-transition.js') }}"></script>
    <script src="{{ asset('js/login/login-form.js') }}"></script>
</body>

</html>