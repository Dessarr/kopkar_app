<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function showAdminLoginForm()
    {
        return view('auth.login-admin');
    }

    public function adminLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = \App\Models\Akun::where('username', $request->username)
                                ->where('role', 'admin')
                                ->where('is_active', 1)
                                ->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->getAuthPassword())) {
            Auth::login($user, $request->filled('remember'));
            $request->session()->regenerate();
            return redirect()->intended(route('admin.dashboard'));
        }

        return back()->withErrors([
            'username' => 'Username atau Password Salah, Silahkan Coba Lagi!',
        ])->onlyInput('username');
    }

    public function showMemberLoginForm()
    {
        return view('auth.login-member');
    }

    public function memberLogin(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        $user = \App\Models\Akun::where('username', $request->username)
                                ->where('role', 'member')
                                ->where('is_active', 1)
                                ->first();

        if ($user && \Illuminate\Support\Facades\Hash::check($request->password, $user->getAuthPassword())) {
            Auth::login($user, $request->filled('remember'));
            $request->session()->regenerate();
            return redirect()->intended(route('member.dashboard'));
        }

        return back()->withErrors([
            'username' => 'Username atau Password Salah, Silahkan Coba Lagi!',
        ])->onlyInput('username');
    }
    
    protected function authenticated(Request $request, $user)
    {
        if ($user->role == 'admin') {
            return redirect()->route('admin.dashboard');
        } elseif ($user->role == 'member') {
            return redirect()->route('member.dashboard');
        }

        return redirect('/home'); // Fallback
    }

    public function logout(Request $request)
    {
        $role = Auth::user()->role;
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        if ($role == 'admin') {
            return redirect()->route('admin.login');
        }
        
        return redirect()->route('member.login');
    }
}
