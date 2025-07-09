<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'u_name' => 'required',
            'pass_word' => 'required'
        ]);

        // Debug log
        Log::info('Login attempt for user: ' . $credentials['u_name']);

        // Cari admin berdasarkan u_name
        $admin = Admin::where('u_name', $credentials['u_name'])->first();

        if ($admin) {
            Log::info('Admin found with ID: ' . $admin->id);
            
            // Debug password check
            $passwordMatch = Hash::check($credentials['pass_word'], $admin->pass_word);
            Log::info('Password match: ' . ($passwordMatch ? 'Yes' : 'No'));

            if ($passwordMatch) {
                Auth::guard('admin')->login($admin);
                $request->session()->regenerate();
                Log::info('Login successful for admin: ' . $admin->u_name);
                return redirect()->intended('admin/dashboard');
            }
        } else {
            Log::info('No admin found with username: ' . $credentials['u_name']);
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->withInput($request->except('pass_word'));
    }

    public function adminDashboard()
    {
        return view('admin.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
}