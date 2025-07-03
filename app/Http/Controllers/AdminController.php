<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\user_admin;

class AdminController extends Controller
{
    // Tampilkan form login
    public function showLoginForm()
    {
        return view('auth.login'); // Pastikan file view-nya berada di resources/views/auth/login.blade.php
    }

    // Proses login
    public function login(Request $request)
    {
        // Validasi input (menggunakan u_name dan pass_word karena menyesuaikan kolom tabel)
        $request->validate([
            'u_name' => 'required',
            'pass_word' => 'required',
        ]);

        // Cari user berdasarkan u_name
        $admin = user_admin::where('u_name', $request->u_name)->first();

        // Cek apakah user ditemukan dan password cocok
        if ($admin && Hash::check($request->pass_word, $admin->pass_word)) {
            // Simpan ID admin ke session
            session(['admin_id' => $admin->id]);
            return redirect()->route('admin.dashboard');
        }

        // Jika gagal login
        return back()->withErrors([
            'login_error' => 'Username atau password salah.',
        ])->withInput();
    }

    // Dashboard admin
    public function adminDashboard()
    {
        if (!session('admin_id')) {
            return redirect()->route('auth.login');
        }

        return view('admin.dashboard'); // Ganti dengan view dashboard admin kamu
    }

    // Logout
    public function logout(Request $request)
    {
        $request->session()->forget('admin_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')->with('message', 'Berhasil logout.');
    }
}