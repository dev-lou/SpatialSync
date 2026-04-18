<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Build;
use App\Services\SupabaseService;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    protected $supabase;

    public function __construct(SupabaseService $supabase)
    {
        $this->supabase = $supabase;
    }

    /**
     * Get all permissions for the current user on a specific build
     */
    public function getPermissions(Request $request, $buildId)
    {
        $userId = $request->session()->get('supabase_user_id');

        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get build from Supabase
        $builds = $this->supabase->select('builds', ['*'], ['id' => $buildId]);
        if (empty($builds)) {
            return response()->json(['error' => 'Build not found'], 404);
        }
        $build = (object) $builds[0];

        // Determine role
        $role = 'viewer';
        $isOwner = $build->created_by === $userId;

        if ($isOwner) {
            $role = 'admin';
        } else {
            $members = $this->supabase->select('build_members', ['role'], [
                'build_id' => $buildId,
                'user_id' => $userId
            ]);
            if (!empty($members)) {
                $role = $members[0]['role'];
            }
        }

        // Calculate permissions based on role
        $permissions = $this->calculatePermissions($role);

        return response()->json([
            'build_id' => $buildId,
            'user_id' => $userId,
            'role' => $role,
            'is_owner' => $isOwner,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Check a specific permission
     */
    public function checkPermission(Request $request, $buildId, $permission)
    {
        $userId = $request->session()->get('supabase_user_id');

        if (!$userId) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Get build from Supabase
        $builds = $this->supabase->select('builds', ['*'], ['id' => $buildId]);
        if (empty($builds)) {
            return response()->json(['error' => 'Build not found'], 404);
        }
        $build = (object) $builds[0];

        // Determine role
        $role = 'viewer';
        $isOwner = $build->created_by === $userId;

        if ($isOwner) {
            $role = 'admin';
        } else {
            $members = $this->supabase->select('build_members', ['role'], [
                'build_id' => $buildId,
                'user_id' => $userId
            ]);
            if (!empty($members)) {
                $role = $members[0]['role'];
            }
        }

        $permissions = $this->calculatePermissions($role);
        $hasPermission = $permissions[$permission] ?? false;

        return response()->json([
            'permission' => $permission,
            'granted' => $hasPermission,
            'role' => $role,
        ]);
    }

    /**
     * Calculate permissions based on role
     */
    protected function calculatePermissions(string $role): array
    {
        $permissionMatrix = [
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
