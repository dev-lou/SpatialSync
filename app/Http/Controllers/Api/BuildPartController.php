<?php

namespace App\Http\Controllers\Api;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Services\SupabaseClient;

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
    protected function checkBuildAccess(AuthenticatedRequest $request, string $buildId): bool
    {
        $userId = $request->auth_user_id;

        // Get build to check ownership
        $builds = $this->supabase->select('builds', ['created_by'], ['id' => $buildId]);
        if ($builds === []) {
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

        return $members !== [];
    }

    /**
     * Check if user can modify build (owner or editor)
     */
    protected function canModifyBuild(AuthenticatedRequest $request, string $buildId): bool
    {
        $userId = $request->auth_user_id;

        // Get build to check ownership
        $builds = $this->supabase->select('builds', ['created_by'], ['id' => $buildId]);
        if ($builds === []) {
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

        return $members !== [] && $members[0]['role'] === 'editor';
    }

    public function index(AuthenticatedRequest $request, string $buildId)
    {
        // Check access
        if (!$this->checkBuildAccess($request, $buildId)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Get build to check current floor
        $builds = $this->supabase->select('builds', ['current_floor'], ['id' => $buildId]);
        if ($builds === []) {
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

    public function allParts(AuthenticatedRequest $request, string $buildId)
    {
        // Check access
        if (!$this->checkBuildAccess($request, $buildId)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $parts = $this->supabase->select('build_parts', ['*'], ['build_id' => $buildId]);

        // Sort by floor_number then z_index
        usort($parts, function ($a, $b) {
            $floorCompare = (int) ($a['floor_number'] ?? 1) - (int) ($b['floor_number'] ?? 1);
            if ($floorCompare !== 0) {
                return $floorCompare;
            }

            return (int) ($a['z_index'] ?? 0) - (int) ($b['z_index'] ?? 0);
        });

        return response()->json($parts);
    }

    public function store(AuthenticatedRequest $request, string $buildId)
    {
        // Check modify permission
        if (!$this->canModifyBuild($request, $buildId)) {
            return response()->json(['error' => 'Access denied - editor role required'], 403);
        }

        $validated = $request->validate([
            'type' => 'required|string|in:wall,floor,roof,door,window,stairs,furniture,structural,fixture,landscape',

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

    public function update(AuthenticatedRequest $request, string $buildId, string $partId)
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
            'updated_at' => 'nullable|string', // optimistic locking
        ]);

        // Optimistic locking: if client provided updated_at, verify it matches
        $filters = [
            'id' => $partId,
            'build_id' => $buildId,
        ];

        if (isset($validated['updated_at']) && $validated['updated_at'] !== null) {
            $filters['updated_at'] = $validated['updated_at'];
        }

        $count = $this->supabase->update('build_parts', $validated, $filters);

        if ($count === 0) {
            // Check if part exists at all
            $existing = $this->supabase->select('build_parts', ['id', 'updated_at'], ['id' => $partId]);
            if ($existing !== []) {
                return response()->json([
                    'error' => 'Part was modified by another user. Please refresh.',
                    'conflict' => true,
                    'current_updated_at' => $existing[0]['updated_at'] ?? null,
                ], 409);
            }
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

    public function destroy(AuthenticatedRequest $request, string $buildId, string $partId)
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
