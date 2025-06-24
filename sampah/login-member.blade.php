@extends('layouts.auth')

@section('left-panel')
<div class="w-full h-full text-center bg-[url(/img/login/element-login-fix.png)] bg-fixed bg-[#1abc9c] text-white flex flex-col justify-center items-center">
    <h2 class="text-4xl font-bold mb-4">Admin Login Here!</h2>
    <p class="mb-8">Welcome to Koperasi Karyawan<br>PT. KAO Indonesia</p>
    <a href="{{ route('admin.login') }}" class="inline-block border-2 animate-bounce border-[white] text-[white] font-bold py-3 px-10 rounded-full transition-all duration-500 ease-in-out hover:bg-[white] hover:text-[#1abc9c] transition-colors">
        Click Here!
    </a>
</div>
@endsection

@section('right-panel')
<div class="w-2/3 text-center bg-[#1abc9c] p-4 rounded-lg h-3/4 justify-center flex flex-col flex-wrap">
    <h2 class="text-3xl font-bold text-white mb-2">Member Login</h2>
    <p class="text-white mb-8">KOPERASI KARYAWAN<br>PT. KAO INDONESIA</p>

    <form action="{{ route('member.login.submit') }}" method="POST">
        @csrf
        <div class="mb-4">
            <div class="flex flex-row items-center bg-white rounded-lg border focus-within:ring-2 focus-within:ring-[#1abc9c]">
                <label for="username"><i class="fa-solid fa-user text-gray-400 text-lg px-3"></i></label>
                <input type="text" name="username" id="username" placeholder="Username" value="{{ old('username') }}" class="w-full px-4 py-4 bg-transparent outline-none" required>
            </div>
            @error('username')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-6">
            <div class="flex flex-row items-center bg-white rounded-lg border focus-within:ring-2 focus-within:ring-[#1abc9c]">
                <label for="password"><i class="fa-solid fa-lock text-gray-400 text-lg px-3"></i></label>
                <input type="password" name="password" id="password" placeholder="Password" class="w-full px-4 py-4 bg-transparent outline-none" required>
            </div>
            @error('password')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="w-full bg-[white] text-[#1abc9c] transition-all duration-300 ease-in-out hover:bg-[#16a085] hover:text-white p-3 rounded-lg font-bold">Log In</button>
    </form>
</div>
@endsection 