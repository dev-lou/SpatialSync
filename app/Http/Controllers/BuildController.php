<?php

namespace App\Http\Controllers;

use App\Services\SupabaseClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BuildController extends Controller
{
    protected SupabaseClient $supabase;

    public function __construct()
    {
        $this->supabase = app(SupabaseClient::class);
    }

    protected function getUserId(Request $request): string
    {
        return $request->session()->get('supabase_user_id');
    }

    protected function getUserName(Request $request): string
    {
        return $request->session()->get('supabase_user_name', 'User');
    }

    public function index(Request $request)
    {
        $userId = $this->getUserId($request);

        // 1. Get builds owned by the user
        $allBuilds = $this->supabase->select('builds', ['*'], []);
        $ownedBuilds = array_filter($allBuilds, function ($build) use ($userId) {
            return isset($build['created_by']) && $build['created_by'] === $userId;
        });

        // 2. Get builds where user is a member (Invited)
        $memberships = $this->supabase->select('build_members', ['build_id', 'role'], ['user_id' => $userId]);
        $sharedBuildIds = array_column($memberships, 'build_id');
        $roleMap = array_column($memberships, 'role', 'build_id');
        
        $invitedBuilds = array_filter($allBuilds, function ($build) use ($sharedBuildIds, $userId) {
            // Avoid duplicates if owner is also in members table
            return in_array($build['id'], $sharedBuildIds) && $build['created_by'] !== $userId;
        });

        // 3. Combine and flag roles
        $combined = [];
        foreach ($ownedBuilds as $b) {
            $b['user_role'] = 'owner';
            $combined[] = (object) $b;
        }
        foreach ($invitedBuilds as $b) {
            $b['user_role'] = $roleMap[$b['id']] ?? 'viewer';
            $combined[] = (object) $b;
        }

        // 4. Attach members to each build for avatar stacking
        // First get all relevant memberships
        $allMemberships = $this->supabase->select('build_members', ['build_id', 'user_id', 'role'], []);
        
        // Then get all users
        $allUsers = $this->supabase->select('users', ['id', 'name', 'email'], []);
        $userMap = collect($allUsers)->keyBy('id');

        foreach ($combined as $b) {
            $membersData = collect([]);
            
            // Add owner
            $ownerUser = $userMap->get($b->created_by);
            if ($ownerUser) {
                $membersData->push((object)[
                    'id' => $b->created_by,
                    'name' => $ownerUser['name'],
                    'role' => 'owner'
                ]);
            }
            
            // Add other members
            foreach ($allMemberships as $m) {
                if ($m['build_id'] === $b->id && $m['user_id'] !== $b->created_by) {
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
            
            $b->members = $membersData;
        }

        // 5. Sort by created_at descending
        usort($combined, function ($a, $b) {
            return ($b->created_at ?? '') <=> ($a->created_at ?? '');
        });

        $builds = collect($combined);

        return view('builds.index', compact('builds'));
    }

    public function create()
    {
        $presetsData = $this->supabase->select('part_presets', ['*'], ['is_active' => 'true']);
        $presets = collect($presetsData)->groupBy('type');

        return view('builds.create', compact('presets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $userId = $this->getUserId($request);

        $buildData = [
            'id' => Str::uuid()->toString(),
            'team_id' => null,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'created_by' => $userId,
            'current_floor' => 1,
            'roof_visible' => true,
            'canvas_json' => json_encode(['version' => '1.0', 'parts' => []]),
        ];

        try {
            $build = $this->supabase->insert('builds', $buildData);

            if (! $build) {
                \Log::error('Supabase insert failed for build: '.json_encode($buildData));

                return back()->with('error', 'Failed to create build. Please try again.');
            }

            return redirect()->route('builds.show', ['build' => $build['id']]);
        } catch (\Exception $e) {
            \Log::error('Exception in BuildController@store: '.$e->getMessage());

            return back()->with('error', 'An error occurred while creating the build.');
        }
    }

    public function show(Request $request, $buildId)
    {
        $userId = $this->getUserId($request);
        $userName = $this->getUserName($request);

        // 1. Fetch the Build
        $builds = $this->supabase->select('builds', ['*'], ['id' => $buildId]);
        if (empty($builds)) {
            abort(404, 'Build not found');
        }
        $build = (object) $builds[0];

        // 2. Determine Role & Privacy Check
        $userRole = 'viewer';
        if ($build->created_by === $userId) {
            $userRole = 'owner';
        } else {
            // Check if invited
            $memberships = $this->supabase->select('build_members', ['role'], [
                'build_id' => $buildId,
                'user_id' => $userId
            ]);

            if (empty($memberships)) {
                // Strict Privacy: Only owner and invitees can enter
                abort(403, 'You do not have permission to access this build.');
            }
            $userRole = $memberships[0]['role'];
        }

        // 3. Fetch Members (Persistence)
        $rawMembers = $this->supabase->select('build_members', ['*'], ['build_id' => $buildId]);
        
        // We need player names, so let's get all users who are members
        $allUsers = $this->supabase->select('users', ['id', 'name'], []); // Ideally use a join or IN query if supported
        $userMap = collect($allUsers)->keyBy('id');

        $membersData = [];
        
        // Add the owner first
        $ownerUser = $userMap->get($build->created_by);
        $membersData[] = [
            'id' => $build->created_by,
            'name' => ($ownerUser['name'] ?? 'Owner') . ($userId === $build->created_by ? ' (You)' : ''),
            'role' => 'owner',
            'isOnline' => true, // Default for now
            'color' => '#0066FF',
            'color2' => '#818CF8'
        ];

        // Add invited members
        foreach ($rawMembers as $m) {
            if ($m['user_id'] === $build->created_by) continue; // Skip if owner is also in members table redundant
            
            $u = $userMap->get($m['user_id']);
            $membersData[] = [
                'id' => $m['user_id'],
                'name' => ($u['name'] ?? 'Guest') . ($userId === $m['user_id'] ? ' (You)' : ''),
                'role' => $m['role'],
                'isOnline' => false,
                'color' => '#6B7280',
                'color2' => '#9CA3AF'
            ];
        }

        // 4. Fetch Chat History (Persistence)
        $messages = $this->supabase->select('build_messages', ['*'], ['build_id' => $buildId]);
        // Sort by time or take last 50
        $messages = collect($messages)->sortBy('created_at')->values()->all();

        // 5. Fetch Issues for build
        $rawIssues = $this->supabase->select('build_issues', ['*'], ['build_id' => $buildId]);
        \Log::info('Raw issues from Supabase: ' . json_encode($rawIssues));
        $issues = collect($rawIssues);
        // Get user names for issues
        $userMap = collect($allUsers)->keyBy('id');
        $issues = collect($issues)->map(function ($issue) use ($userMap) {
            $issue['creator_name'] = $userMap->get($issue['created_by'])['name'] ?? 'Unknown';
            $issue['status_color'] = match($issue['status']) {
                'open' => '#ef4444',
                'in_progress' => '#eab308',
                'resolved' => '#22c55e',
                'closed' => '#6b7280',
                default => '#6b7280',
            };
            $issue['priority_color'] = match($issue['priority']) {
                'critical' => '#dc2626',
                'high' => '#f97316',
                'medium' => '#eab308',
                'low' => '#22c55e',
                default => '#6b7280',
            };
            $issue['priority_label'] = match($issue['priority']) {
                'critical' => 'Critical',
                'high' => 'High',
                'medium' => 'Medium',
                'low' => 'Low',
                default => 'Medium',
            };
            $issue['status_label'] = match($issue['status']) {
                'open' => 'Open',
                'in_progress' => 'In Progress',
                'resolved' => 'Resolved',
                'closed' => 'Closed',
                default => 'Open',
            };
            return $issue;
        })->values()->all();

        // 6. Fetch Presets for editor
        $presetsData = $this->supabase->select('part_presets', ['*'], ['is_active' => 'true']);
        $presets = collect($presetsData)->groupBy('type');

        $auth_user_id = $userId;
        $auth_user_name = $userName;

        // Calculate permissions based on role
        $userPermissions = $this->calculatePermissions($userRole);

        return view('builds.show', compact(
            'build', 
            'userRole', 
            'membersData', 
            'messages', 
            'issues',
            'presets', 
            'auth_user_id',
            'auth_user_name',
            'userPermissions'
        ));
    }

    public function update(Request $request, $buildId)
    {
        $validated = $request->validate([
            'current_floor' => 'integer|min:1|max:10',
            'roof_visible' => 'boolean',
        ]);

        $updateData = [];
        if (isset($validated['current_floor'])) {
            $updateData['current_floor'] = $validated['current_floor'];
        }
        if (isset($validated['roof_visible'])) {
            $updateData['roof_visible'] = $validated['roof_visible'];
        }

        $this->supabase->update('builds', $updateData, ['id' => $buildId]);

        return response()->json(['success' => true]);
    }

    public function duplicate(Request $request, $buildId)
    {
        $builds = $this->supabase->select('builds', ['*'], ['id' => $buildId]);

        if (empty($builds)) {
            abort(404, 'Build not found');
        }

        $original = $builds[0];
        $userId = $this->getUserId($request);

        $copyData = [
            'id' => Str::uuid()->toString(),
            'team_id' => $original['team_id'] ?? null,
            'name' => ($original['name'] ?? 'Build').' (Copy)',
            'description' => $original['description'] ?? null,
            'created_by' => $userId,
            'current_floor' => $original['current_floor'] ?? 1,
            'roof_visible' => $original['roof_visible'] ?? true,
            'canvas_json' => $original['canvas_json'] ?? json_encode(['version' => '1.0', 'parts' => []]),
        ];

        $this->supabase->insert('builds', $copyData);

        return redirect()->route('builds.show', ['build' => $copyData['id']]);
    }

    public function destroy(Request $request, $buildId)
    {
        try {
            // Cascade delete: Clean up dependent tables first
            // Note: In a production Supabase setup, you'd ideally use "ON DELETE CASCADE" in the DB.
            // But doing it here ensures reliability during our migration transition.
            
            $this->supabase->delete('build_parts', ['build_id' => $buildId]);
            $this->supabase->delete('build_members', ['build_id' => $buildId]);
            $this->supabase->delete('build_messages', ['build_id' => $buildId]);

            // Finally, delete the build record
            $deleted = $this->supabase->delete('builds', ['id' => $buildId]);

            if (! $deleted) {
                \Log::error('Failed to delete build from Supabase after cleanup: build_id='.$buildId);

                if ($request->expectsJson() || $request->ajax()) {
                    return response()->json(['error' => 'Failed to delete build.'], 500);
                }
                return back()->with('error', 'Failed to delete build. Please try again.');
            }

            \Log::info('Build and related data deleted successfully: build_id='.$buildId);

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['success' => true, 'message' => 'Build deleted successfully.']);
            }
            return redirect()->route('dashboard')->with('success', 'Build deleted successfully.');
        } catch (\Exception $e) {
            \Log::error('Exception in BuildController@destroy: '.$e->getMessage());

            if ($request->expectsJson() || $request->ajax()) {
                return response()->json(['error' => 'An error occurred while deleting the build.'], 500);
            }
            return back()->with('error', 'An error occurred while deleting the build.');
        }
    }

    public function addMember(Request $request, $buildId)
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'role' => 'required|in:viewer,editor',
        ]);

        $users = $this->supabase->select('users', ['*'], ['email' => $validated['email']]);

        if (empty($users)) {
            return response()->json(['error' => 'User not found.'], 404);
        }

        $user = $users[0];

        $memberData = [
            'id' => Str::uuid()->toString(),
            'build_id' => $buildId,
            'user_id' => $user['id'],
            'role' => $validated['role'],
        ];

        $this->supabase->insert('build_members', $memberData);

        return response()->json([
            'message' => "{$user['name']} added as {$validated['role']}.",
            'user' => [
                'id' => $user['id'],
                'name' => $user['name'],
                'role' => $validated['role'],
            ],
        ]);
    }

    public function updateMemberRole(Request $request, $buildId, $userId)
    {
        $validated = $request->validate([
            'role' => 'required|in:viewer,editor',
        ]);

        // Find the member record in Supabase
        $this->supabase->update('build_members', 
            ['role' => $validated['role']], 
            ['build_id' => $buildId, 'user_id' => $userId]
        );

        return response()->json([
            'message' => 'Member role updated successfully.',
            'role' => $validated['role']
        ]);
    }

    public function searchUsers(Request $request)
    {
        $query = $request->get('q');
        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $allUsers = $this->supabase->select('users', ['id', 'name', 'email'], []);

        $filtered = array_filter($allUsers, function ($user) use ($query) {
            $name = strtolower($user['name'] ?? '');
            $email = strtolower($user['email'] ?? '');
            $q = strtolower($query);

            return str_contains($name, $q) || str_contains($email, $q);
        });

        $users = array_slice(array_values($filtered), 0, 5);

        return response()->json($users);
    }

    public function createShare($buildId)
    {
        $token = Str::random(32);

        $shareData = [
            'id' => Str::uuid()->toString(),
            'build_id' => $buildId,
            'share_token' => $token,
            'access_level' => 'view',
        ];

        $this->supabase->insert('build_shares', $shareData);

        return response()->json([
            'url' => route('builds.shared', ['build' => $buildId, 'token' => $token]),
        ]);
    }

    public function removeMember($buildId, $userId)
    {
        $this->supabase->delete('build_members', [
            'build_id' => $buildId,
            'user_id' => $userId
        ]);

        return response()->json(['success' => true, 'message' => 'Member removed.']);
    }

    public function export($buildId, string $format)
    {
        $builds = $this->supabase->select('builds', ['*'], ['id' => $buildId]);

        if (empty($builds)) {
            abort(404, 'Build not found');
        }

        $build = $builds[0];
        $parts = $this->supabase->select('build_parts', ['*'], ['build_id' => $buildId]);

        if ($format === 'json') {
            $exportData = [
                'name' => $build['name'],
                'description' => $build['description'],
                'version' => '1.0',
                'exported_at' => now()->toIso8601String(),
                'current_floor' => $build['current_floor'],
                'roof_visible' => $build['roof_visible'],
                'parts' => $parts,
            ];

            return response()->json($exportData)
                ->header('Content-Disposition', 'attachment; filename='.$build['name'].'.json');
        }

        abort(404, 'Export format not supported');
    }

    public function shared($buildId, string $token)
    {
        $shares = $this->supabase->select('build_shares', ['*'], [
            'build_id' => $buildId,
            'share_token' => $token,
        ]);

        if (empty($shares)) {
            abort(403, 'Invalid share link.');
        }

        $builds = $this->supabase->select('builds', ['*'], ['id' => $buildId]);

        if (empty($builds)) {
            abort(404, 'Build not found');
        }

        $build = (object) $builds[0];

        $presetsData = $this->supabase->select('part_presets', ['*'], ['is_active' => 'true']);
        $presets = collect($presetsData)->groupBy('type');

        $members = collect([]);
        $messages = collect([]);

        return view('builds.shared', compact('build', 'members', 'messages', 'presets'));
    }

    /**
     * Calculate permissions based on user role
     */
    protected function calculatePermissions(string $role): array
    {
        $permissionMatrix = [
            'owner' => [
                'can_edit_geometry' => true,
                'can_delete_parts' => true,
                'can_manage_members' => true,
                'can_add_comments' => true,
                'can_export_build' => true,
                'can_change_settings' => true,
                'can_view_build' => true,
            ],
            'admin' => [
                'can_edit_geometry' => true,
                'can_delete_parts' => true,
                'can_manage_members' => true,
                'can_add_comments' => true,
                'can_export_build' => true,
                'can_change_settings' => true,
                'can_view_build' => true,
            ],
            'editor' => [
                'can_edit_geometry' => true,
                'can_delete_parts' => true,
                'can_manage_members' => false,
                'can_add_comments' => true,
                'can_export_build' => true,
                'can_change_settings' => false,
                'can_view_build' => true,
            ],
            'viewer' => [
                'can_edit_geometry' => false,
                'can_delete_parts' => false,
                'can_manage_members' => false,
                'can_add_comments' => true,
                'can_export_build' => false,
                'can_change_settings' => false,
                'can_view_build' => true,
            ],
        ];

        return $permissionMatrix[$role] ?? $permissionMatrix['viewer'];
    }
}
