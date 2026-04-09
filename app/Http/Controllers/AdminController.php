<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\BuildMessage;
use App\Models\User;

class AdminController extends Controller
{
    public function dashboard()
    {
        $stats = [
            'users' => User::count(),
            'builds' => Build::count(),
            'activeToday' => User::whereDate('updated_at', today())->count(),
            'messages' => BuildMessage::count(),
        ];

        $recentActivity = BuildMessage::with('user', 'build')
            ->latest()
            ->take(10)
            ->get();

        return view('admin.dashboard', compact('stats', 'recentActivity'));
    }

    public function users()
    {
        $users = User::with('builds')->get();

        return view('admin.users', compact('users'));
    }

    public function builds()
    {
        $builds = Build::with('creator', 'team')->get();

        return view('admin.blueprints', compact('builds'));
    }

    public function deleteUser(User $user)
    {
        if ($user->is_admin) {
            return back()->with('error', 'Cannot delete admin user.');
        }

        $user->delete();

        return back()->with('success', 'User deleted successfully.');
    }

    public function deleteBuild(Build $build)
    {
        $build->delete();

        return back()->with('success', 'Build deleted successfully.');
    }
}
