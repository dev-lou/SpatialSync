<?php

namespace App\Http\Controllers;

use App\Services\SupabaseClient;
use App\Services\SupabaseUserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class AdminController extends Controller
{
    protected SupabaseClient $supabase;

    protected SupabaseUserService $userService;

    public function __construct()
    {
        $this->supabase = app(SupabaseClient::class);
        $this->userService = app(SupabaseUserService::class);
    }

    public function dashboard()
    {
        $users = $this->userService->all();
        $builds = $this->supabase->select('builds', ['*'], []);
        $messages = $this->supabase->select('build_messages', ['*'], []);
        $presets = $this->supabase->select('part_presets', ['*'], []);

        // Simulated Telemetry (2026 SaaS Style)
        $telemetry = [
            'cpu_usage' => rand(12, 45).'%',
            'ram_usage' => rand(2, 5).'GB / 16GB',
            'ws_connections' => rand(15, 120),
            'storage_used' => (count($builds) * 0.5).'MB',
            'uptime' => '99.98%',
        ];

        $stats = [
            'users' => count($users),
            'builds' => count($builds),
            'presets' => count($presets),
            'messages' => count($messages),
        ];

        $recentMessages = array_slice(array_reverse($messages), 0, 8);
        $recentActivity = collect($recentMessages)->map(fn ($m) => (object) $m);

        return view('admin.dashboard', compact('stats', 'recentActivity', 'telemetry'));
    }

    public function users(Request $request)
    {
        $users = $this->userService->all();
        $users = collect($users)->map(fn ($u) => (object) $u);

        return view('admin.users', compact('users'));
    }

    public function presets()
    {
        $presetsData = $this->supabase->select('part_presets', ['*'], []);
        $presets = collect($presetsData)->map(fn ($p) => (object) $p);

        return view('admin.presets', compact('presets'));
    }

    public function builds()
    {
        $buildsData = $this->supabase->select('builds', ['*'], []);
        $builds = collect($buildsData)->map(fn ($b) => (object) $b);

        return view('admin.builds', compact('builds'));
    }

    public function deleteUser(Request $request, $userId)
    {
        $deleted = $this->supabase->delete('users', ['id' => $userId]);

        if ($deleted) {
            return back()->with('success', 'User deleted successfully.');
        }

        return back()->with('error', 'Failed to delete user.');
    }

    public function deleteBuild(Request $request, $buildId)
    {
        $this->supabase->delete('build_parts', ['build_id' => $buildId]);
        $deleted = $this->supabase->delete('builds', ['id' => $buildId]);

        if ($deleted) {
            return back()->with('success', 'Build deleted successfully.');
        }

        return back()->with('error', 'Failed to delete build.');
    }

    public function security()
    {
        $userId = session('supabase_user_id');
        $userData = $this->userService->findById($userId);
        $user = (object) $userData;

        return view('admin.security', compact('user'));
    }

    public function saveBiometrics(Request $request)
    {
        $request->validate([
            'descriptor' => 'required|array',
        ]);

        $userId = session('supabase_user_id');
        
        // Save to Supabase using biometric_data column (AES-256 encrypted at rest)
        $success = $this->supabase->update('users', ['biometric_data' => Crypt::encryptString(json_encode($request->descriptor))], ['id' => $userId]);

        if ($success) {
            return response()->json(['message' => 'Face fingerprint saved successfully.']);
        }

        return response()->json(['message' => 'Failed to save biometric data.'], 500);
    }
}
