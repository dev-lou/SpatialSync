<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupabaseClient;
use Illuminate\Http\Request;

class BuildPartController extends Controller
{
    protected SupabaseClient $supabase;

    public function __construct()
    {
        $this->supabase = app(SupabaseClient::class);
    }

    /**
     * Check if user has access to build
     */
    protected function checkBuildAccess(Request $request, $buildId): bool
    {
        $userId = $request->session()->get('supabase_user_id');

        // Get build to check ownership
        $builds = $this->supabase->select('builds', ['created_by'], ['id' => $buildId]);
        if (empty($builds)) {
            return false;
        }

        // Owner has access
        if ($builds[0]['created_by'] === $userId) {
            return true;
        }

        // Check if user is a member
        $members = $this->supabase->select('build_members', ['role'], [
            'build_id' => $buildId,
            'user_id' => $userId
        ]);

        return !empty($members);
    }

    /**
     * Check if user can modify build (owner or editor)
     */
    protected function canModifyBuild(Request $request, $buildId): bool
    {
        $userId = $request->session()->get('supabase_user_id');

        // Get build to check ownership
        $builds = $this->supabase->select('builds', ['created_by'], ['id' => $buildId]);
        if (empty($builds)) {
            return false;
        }

        // Owner can modify
        if ($builds[0]['created_by'] === $userId) {
            return true;
        }

        // Check if user is an editor
        $members = $this->supabase->select('build_members', ['role'], [
            'build_id' => $buildId,
            'user_id' => $userId
        ]);

        return !empty($members) && $members[0]['role'] === 'editor';
    }

    public function index(Request $request, $buildId)
    {
        // Check access
        if (!$this->checkBuildAccess($request, $buildId)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Get build to check current floor
        $builds = $this->supabase->select('builds', ['current_floor'], ['id' => $buildId]);
        if (empty($builds)) {
            return response()->json(['error' => 'Build not found'], 404);
        }

        $currentFloor = $builds[0]['current_floor'] ?? 1;

        // Get parts for this floor
        $parts = $this->supabase->select('build_parts', ['*'], [
            'build_id' => $buildId,
            'floor_number' => $currentFloor,
        ]);

        return response()->json($parts);
    }

    public function allParts(Request $request, $buildId)
    {
        // Check access
        if (!$this->checkBuildAccess($request, $buildId)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $parts = $this->supabase->select('build_parts', ['*'], ['build_id' => $buildId]);

        // Sort by floor_number then z_index
        usort($parts, function ($a, $b) {
            $floorCompare = ($a['floor_number'] ?? 1) - ($b['floor_number'] ?? 1);
            if ($floorCompare !== 0) {
                return $floorCompare;
            }

            return ($a['z_index'] ?? 0) - ($b['z_index'] ?? 0);
        });

        return response()->json($parts);
    }

    public function store(Request $request, $buildId)
    {
        // Check modify permission
        if (!$this->canModifyBuild($request, $buildId)) {
            return response()->json(['error' => 'Access denied - editor role required'], 403);
        }

        $validated = $request->validate([
            'type' => 'required|string|in:wall,floor,roof,door,window,stairs',
            'variant' => 'required|string',
            'position_x' => 'required|numeric',
            'position_y' => 'required|numeric',
            'position_z' => 'required|numeric',
            'width' => 'sometimes|numeric|min:0.01',
            'height' => 'sometimes|numeric|min:0.01',
            'depth' => 'sometimes|numeric|min:0.01',
            'rotation_y' => 'integer|min:0|max:360',
            'color' => 'nullable|string',
            'color_front' => 'nullable|string',
            'color_back' => 'nullable|string',
            'material' => 'nullable|string|max:50',
            'shape_points' => 'nullable|array',
            'floor_number' => 'integer|min:1|max:10',
            'z_index' => 'integer|min:0',
        ]);

        $validated['build_id'] = $buildId;

        $part = $this->supabase->insert('build_parts', $validated);

        if (! $part) {
            return response()->json(['error' => 'Failed to create part'], 500);
        }

        // Touch build updated_at
        $this->supabase->update('builds', [
            'updated_at' => now()->toIso8601String()
        ], ['id' => $buildId]);

        return response()->json($part, 201);
    }

    public function update(Request $request, $buildId, $partId)
    {
        // Check modify permission
        if (!$this->canModifyBuild($request, $buildId)) {
            return response()->json(['error' => 'Access denied - editor role required'], 403);
        }

        $validated = $request->validate([
            'position_x' => 'numeric',
            'position_y' => 'numeric',
            'position_z' => 'numeric',
            'width' => 'numeric|min:0.1',
            'height' => 'numeric|min:0.1',
            'depth' => 'numeric|min:0.1',
            'rotation_y' => 'integer|min:0|max:360',
            'color' => 'nullable|string',
            'color_front' => 'nullable|string',
            'color_back' => 'nullable|string',
            'material' => 'nullable|string|max:50',
            'shape_points' => 'nullable|array',
            'floor_number' => 'integer|min:1|max:10',
            'z_index' => 'integer|min:0',
        ]);

        $count = $this->supabase->update('build_parts', $validated, [
            'id' => $partId,
            'build_id' => $buildId,
        ]);

        if ($count === 0) {
            return response()->json(['error' => 'Part not found'], 404);
        }

        // Get updated part
        $parts = $this->supabase->select('build_parts', ['*'], ['id' => $partId]);

        // Touch build updated_at
        $this->supabase->update('builds', [
            'updated_at' => now()->toIso8601String()
        ], ['id' => $buildId]);

        return response()->json($parts[0] ?? []);
    }

    public function destroy(Request $request, $buildId, $partId)
    {
        // Check modify permission
        if (!$this->canModifyBuild($request, $buildId)) {
            return response()->json(['error' => 'Access denied - editor role required'], 403);
        }

        $deleted = $this->supabase->delete('build_parts', [
            'id' => $partId,
            'build_id' => $buildId,
        ]);

        if (! $deleted) {
            return response()->json(['error' => 'Failed to delete part'], 500);
        }

        // Touch build updated_at
        $this->supabase->update('builds', [
            'updated_at' => now()->toIso8601String()
        ], ['id' => $buildId]);

        return response()->json(['message' => 'Part deleted successfully']);
    }
}
