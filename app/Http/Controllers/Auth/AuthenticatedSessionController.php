<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SupabaseUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticatedSessionController extends Controller
{
    public function create()
    {
        return view('auth.login');
    }

    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $supabaseUser = app(SupabaseUserService::class)->findByEmail($credentials['email']);

        if (! $supabaseUser || ! isset($supabaseUser['password'])) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        // Verify password using bcrypt
        if (! Hash::check($credentials['password'], $supabaseUser['password'])) {
            return back()->withErrors([
                'email' => 'The provided credentials do not match our records.',
            ]);
        }

        // Store user data in session
        $request->session()->put('supabase_user_id', $supabaseUser['id']);
        $request->session()->put('supabase_user_email', $supabaseUser['email']);
        $request->session()->put('supabase_user_name', $supabaseUser['name']);
        $request->session()->put('supabase_user_plan', $supabaseUser['plan'] ?? 'free');
        $request->session()->put('supabase_user_admin', $supabaseUser['is_admin'] ?? false);
        $request->session()->regenerate();

        return redirect()->intended(route('builds.index'));
    }

    public function destroy(Request $request)
    {
        $request->session()->flush();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
