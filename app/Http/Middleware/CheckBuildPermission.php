<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Services\SupabaseClient;

class CheckBuildPermission
{
    protected $supabase;

    public function __construct(SupabaseClient $supabase)
    {
        $this->supabase = $supabase;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $buildId = $request->route('buildId') ?? $request->route('build');
        $userId = $request->session()->get('supabase_user_id');

        if (!$buildId || !$userId) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        // Get build to check ownership
        $builds = $this->supabase->select('builds', ['created_by'], ['id' => $buildId]);
        if (empty($builds)) {
            return response()->json(['error' => 'Build not found'], 404);
        }

        $isOwner = $builds[0]['created_by'] === $userId;

        // Get user's role
        $role = null;
        if ($isOwner) {
            $role = 'admin';
        } else {
            $members = $this->supabase->select('build_members', ['role'], [
                'build_id' => $buildId,
                'user_id' => $userId
            ]);
            $role = !empty($members) ? $members[0]['role'] : null;
        }

        // Check if user has the required permission
        if (!$this->hasPermission($role, $permission)) {
            return response()->json(['error' => 'Permission denied: ' . $permission], 403);
        }

        return $next($request);
    }

    /**
     * Check if a role has a specific permission
     */
    protected function hasPermission(?string $role, string $permission): bool
    {
        // Permission matrix
        $permissions = [
            'admin' => [
                'edit_geometry',
                'delete_parts',
                'manage_members',
                'change_settings',
                'export_build',
                'delete_build',
                'add_comments',
                'view_build',
            ],
            'editor' => [
                'edit_geometry',
                'delete_parts',
                'export_build',
                'add_comments',
                'view_build',
            ],
            'viewer' => [
                'add_comments',
                'view_build',
            ],
        ];

        if (!$role || !isset($permissions[$role])) {
            return false;
        }

        return in_array($permission, $permissions[$role]);
    }
}
