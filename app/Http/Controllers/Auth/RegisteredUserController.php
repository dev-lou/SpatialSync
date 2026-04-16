<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SupabaseUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class RegisteredUserController extends Controller
{
    protected SupabaseUserService $supabaseUser;

    public function __construct()
    {
        $this->supabaseUser = app(SupabaseUserService::class);
    }

    public function create()
    {
        return view('auth.register');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);

        // Check if user already exists in Supabase
        $existingUser = $this->supabaseUser->findByEmail($validated['email']);
        if ($existingUser) {
            return back()->withErrors([
                'email' => 'A user with this email already exists.',
            ]);
        }

        // Create user in Supabase only - hash the password
        $supabaseUser = $this->supabaseUser->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_admin' => false,
        ]);

        // Check if user already exists in Supabase
        $existingUser = $this->supabaseUser->findByEmail($validated['email']);
        if ($existingUser) {
            return back()->withErrors([
                'email' => 'A user with this email already exists.',
            ]);
        }

        // Create user in Supabase only
        $supabaseUser = $this->supabaseUser->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'is_admin' => false,
        ]);

        if (! $supabaseUser) {
            return back()->withErrors([
                'email' => 'Failed to create account. Please try again.',
            ]);
        }

        // Create default team in Supabase
        $teamId = Str::uuid()->toString();
        $teamUrl = config('supabase.url').'/rest/v1/teams';
        $teamData = [
            'id' => $teamId,
            'name' => $validated['name']."'s Team",
        ];

        try {
            Http::withHeaders([
                'apikey' => config('supabase.service_key'),
                'Authorization' => 'Bearer '.config('supabase.service_key'),
                'Content-Type' => 'application/json',
            ])->post($teamUrl, $teamData);
        } catch (\Exception $e) {
            // Team creation failed, continue without team
        }

        // Manually login the user using our Supabase provider
        $user = $this->supabaseUser->findByEmail($validated['email']);
        if ($user) {
            // Create a session-based login
            $request->session()->put('supabase_user_id', $user['id']);
            $request->session()->put('supabase_user_email', $user['email']);
            $request->session()->put('supabase_user_name', $user['name']);
        }

        return redirect()->route('builds.index');
    }
}
