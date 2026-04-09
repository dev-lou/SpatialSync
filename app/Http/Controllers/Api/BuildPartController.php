<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Build;
use App\Models\BuildPart;
use Illuminate\Http\Request;

class BuildPartController extends Controller
{
    public function index(Build $build)
    {
        $this->authorize('view', $build);

        $parts = $build->parts()
            ->where('floor_number', $build->current_floor)
            ->orderBy('z_index')
            ->get();

        return response()->json($parts);
    }

    public function allParts(Build $build)
    {
        $this->authorize('view', $build);

        $parts = $build->parts()
            ->orderBy('floor_number')
            ->orderBy('z_index')
            ->get();

        return response()->json($parts);
    }

    public function store(Request $request, Build $build)
    {
        $this->authorize('update', $build);

        $validated = $request->validate([
            'type' => 'required|string|in:wall,floor,roof,door,window,stairs,custom_floor,custom_roof',
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

        $part = $build->parts()->create($validated);

        return response()->json($part, 201);
    }

    public function update(Request $request, Build $build, BuildPart $part)
    {
        $this->authorize('update', $build);

        if ($part->build_id !== $build->id) {
            abort(403, 'Part does not belong to this build.');
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

        $part->update($validated);

        return response()->json($part);
    }

    public function destroy(Build $build, BuildPart $part)
    {
        $this->authorize('update', $build);

        if ($part->build_id !== $build->id) {
            abort(403, 'Part does not belong to this build.');
        }

        $part->delete();

        return response()->json(['message' => 'Part deleted successfully']);
    }
}
