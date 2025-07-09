<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\Member;

class MemberController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nama' => 'required',
            'pass_word' => 'required'
        ]);

        // Debug log
        Log::info('Login attempt for member: ' . $credentials['nama']);

        // Cari member berdasarkan nama
        $member = Member::where('nama', $credentials['nama'])->first();

        if ($member) {
            Log::info('Member found with ID: ' . $member->id);
            
            // Debug password check
            $passwordMatch = Hash::check($credentials['pass_word'], $member->pass_word);
            Log::info('Password match: ' . ($passwordMatch ? 'Yes' : 'No'));

            if ($passwordMatch) {
                Auth::guard('member')->login($member);
                $request->session()->regenerate();
                Log::info('Login successful for member: ' . $member->nama);
                return redirect()->intended('member/dashboard');
            }
        } else {
            Log::info('No member found with name: ' . $credentials['nama']);
        }

        return back()->withErrors([
            'login' => 'The provided credentials do not match our records.',
        ])->withInput($request->except('pass_word'));
    }

    public function memberDashboard()
    {
        return view('member.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::guard('member')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/');
    }
} 