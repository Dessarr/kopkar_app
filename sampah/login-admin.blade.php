@extends('layouts.auth')

@section('left-panel')
<div class="w-full md:w-2/3 text-center bg-[#1abc9c] p-4 rounded-lg min-h-[75vh] md:h-3/4 justify-center flex flex-col flex-wrap mx-auto">
    <h2 class="text-2xl md:text-3xl font-bold text-white mb-2">Sign In to Admin</h2>
    <p class="text-white mb-8">KOPERASI KARYAWAN<br>PT. KAO INDONESIA</p>

    <form action="{{ route('admin.login.submit') }}" method="POST" class="px-4 md:px-8">
        @csrf
        <div class="mb-4">
            <div class="flex flex-row items-center bg-white rounded-lg border focus-within:ring-2 focus-within:ring-[#1abc9c]">
                <label for="username"><i class="fa-solid fa-user text-gray-400 text-lg px-3"></i></label>
                <input type="text" name="username" id="username" placeholder="Username" value="{{ old('username') }}" class="w-full px-4 py-3 md:py-4 bg-transparent outline-none" required>
            </div>
            @error('username')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-6">
            <div class="flex flex-row items-center bg-white rounded-lg border focus-within:ring-2 focus-within:ring-[#1abc9c]">
                <label for="password"><i class="fa-solid fa-lock text-gray-400 text-lg px-3"></i></label>
                <input type="password" name="password" id="password" placeholder="Password" class="w-full px-4 py-3 md:py-4 bg-transparent outline-none" required>
            </div>
            @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="w-full bg-[white] text-[#1abc9c] font-bold py-3 rounded-lg transition-all duration-300 ease-in-out hover:bg-[#16a085] hover:text-white">
            Log In
        </button>
    </form>
</div>
@endsection

@section('right-panel')
<div class="w-full h-full text-center bg-[url(/img/login/element-login-fix.png)] bg-cover bg-center md:bg-fixed bg-[#1abc9c] text-white flex flex-col justify-center items-center p-8">
    <h2 class="text-3xl md:text-4xl font-bold mb-4">Member Login here!</h2>
    <p class="mb-8 text-sm md:text-base">Welcome to Koperasi Karyawan<br>PT. KAO Indonesia</p>
    <a href="{{ route('member.login') }}" class="inline-block border-2 animate-bounce border-white text-white font-bold py-2 md:py-3 px-6 md:px-10 rounded-full transition-all duration-500 ease-in-out hover:bg-white hover:text-[#1abc9c] transition-colors">
        Click Here!
    </a>
</div>
@endsection 