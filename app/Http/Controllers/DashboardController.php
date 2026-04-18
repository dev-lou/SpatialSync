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

        $allMemberships = $this->supabase->select('build_members', ['build_id', 'user_id', 'role'], []);
        $allUsers = $this->supabase->select('users', ['id', 'name', 'email'], []);
        $userMap = collect($allUsers)->keyBy('id');

        // Identify shared builds (user is in members, but not created_by)
        $sharedBuildIds = collect($allMemberships)
            ->filter(fn($m) => $m['user_id'] === $userId)
            ->pluck('build_id')
            ->toArray();
            
        $sharedBuildsFilter = array_filter($allBuilds, function ($build) use ($sharedBuildIds, $userId) {
            return in_array($build['id'], $sharedBuildIds) && (isset($build['created_by']) ? $build['created_by'] !== $userId : true);
        });
        
        usort($sharedBuildsFilter, function ($a, $b) {
            return ($b['created_at'] ?? '') <=> ($a['created_at'] ?? '');
        });

        // Map function to attach members properly
        $mapMembers = function ($build, $userRole = 'owner') use ($allMemberships, $userMap) {
            $obj = (object) $build;
            $obj->user_role = $userRole;
            $membersData = collect([]);
            
            // Add owner
            $ownerUser = $userMap->get($obj->created_by);
            if ($ownerUser) {
                $membersData->push((object)[
                    'id' => $obj->created_by,
                    'name' => $ownerUser['name'],
                    'role' => 'owner'
                ]);
            }
            
            // Add other members
            foreach ($allMemberships as $m) {
                if ($m['build_id'] === $obj->id && $m['user_id'] !== $obj->created_by) {
                    $u = $userMap->get($m['user_id']);
                    if ($u) {
                        $membersData->push((object)[
                            'id' => $u['id'],
                            'name' => $u['name'],
                            'role' => $m['role']
                        ]);
                    }
                }
            }
            
            $obj->members = $membersData;
            return $obj;
        };

        $builds = collect($userBuilds)->map(fn($b) => $mapMembers($b, 'owner'));
        
        // For shared builds, determine actual role
        $roleMap = collect($allMemberships)->where('user_id', $userId)->pluck('role', 'build_id');
        $sharedBuilds = collect($sharedBuildsFilter)->map(fn($b) => $mapMembers($b, $roleMap->get($b['id'], 'viewer')));

        return view('dashboard', compact('builds', 'sharedBuilds', 'userName'));
    }
}
