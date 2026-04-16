<?php

namespace App\Http\Controllers;

use App\Services\SupabaseClient;
use App\Services\SupabaseUserService;
use Illuminate\Http\Request;

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

        $stats = [
            'users' => count($users),
            'builds' => count($builds),
            'activeToday' => count(array_filter($users, fn ($u) => isset($u['updated_at']) && date('Y-m-d', strtotime($u['updated_at'])) === date('Y-m-d'))),
            'messages' => count($messages),
        ];

        $recentMessages = array_slice(array_reverse($messages), 0, 10);
        $recentActivity = collect($recentMessages)->map(fn ($m) => (object) $m);

        return view('admin.dashboard', compact('stats', 'recentActivity'));
    }

    public function users(Request $request)
    {
        $users = $this->userService->all();
        $users = collect($users)->map(fn ($u) => (object) $u);

        return view('admin.users', compact('users'));
    }

    public function builds()
    {
        $buildsData = $this->supabase->select('builds', ['*'], []);
        $builds = collect($buildsData)->map(fn ($b) => (object) $b);

        return view('admin.blueprints', compact('builds'));
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
}
