<?php

namespace App\Http\Controllers;

use App\Services\SupabaseClient;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected SupabaseClient $supabase;

    public function __construct()
    {
        $this->supabase = app(SupabaseClient::class);
    }

    public function index(Request $request)
    {
        $userId = $request->session()->get('supabase_user_id');
        $userName = $request->session()->get('supabase_user_name', 'User');

        // Fetch all builds
        $allBuilds = $this->supabase->select('builds', ['*'], []);

        // Filter and sort user's builds
        $userBuilds = array_filter($allBuilds, function ($build) use ($userId) {
            return isset($build['created_by']) && $build['created_by'] === $userId;
        });

        usort($userBuilds, function ($a, $b) {
            return ($b['created_at'] ?? '') <=> ($a['created_at'] ?? '');
        });

        // Fetch members for these builds to show the count correctly
        $buildIds = array_column($userBuilds, 'id');
        $allMembers = !empty($buildIds) ? $this->supabase->select('build_members', ['*']) : [];
        $membersByBuild = collect($allMembers)->groupBy('build_id');

        $builds = collect($userBuilds)->map(function ($build) use ($membersByBuild) {
            $obj = (object) $build;
            // Attach members as a collection so the view can call take() and count()
            $obj->members = $membersByBuild->get($obj->id, collect([]));
            return $obj;
        });

        return view('dashboard', compact('builds', 'userName'));
    }
}
