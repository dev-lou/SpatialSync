<?php

namespace App\Http\Controllers;

use App\Models\Build;
use App\Models\PartPreset;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuildController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $user = Auth::user();
        $teamId = $user->current_team_id ?? $user->ownedTeams()->first()?->id;

        $builds = Build::where('team_id', $teamId)
            ->with('creator')
            ->latest()
            ->get();

        return view('builds.index', compact('builds'));
    }

    public function create()
    {
        $presets = PartPreset::active()->orderBy('type')->orderBy('name')->get()->groupBy('type');

        return view('builds.create', compact('presets'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $user = Auth::user();
        $teamId = $user->current_team_id ?? $user->ownedTeams()->first()?->id;

        $build = Build::create([
            'team_id' => $teamId,
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'created_by' => Auth::id(),
            'current_floor' => 1,
            'roof_visible' => true,
            'canvas_json' => ['version' => '1.0', 'parts' => []],
        ]);

        $build->members()->attach(Auth::id(), ['role' => 'editor']);

        return redirect()->route('builds.show', $build);
    }

    public function show(Build $build)
    {
        $this->authorize('view', $build);

        $members = $build->members()->withPivot('role')->get();
        $messages = $build->messages()->with('user')->latest()->take(50)->get()->reverse()->values();
        $userRole = $build->userRole(Auth::user());

        // Get part presets for the UI
        $presets = PartPreset::active()->orderBy('type')->orderBy('name')->get()->groupBy('type');

        // Prepare members array for JavaScript
        $membersData = array_merge(
            [['id' => Auth::id(), 'name' => Auth::user()->name, 'role' => $userRole, 'isOnline' => true, 'isEditing' => false, 'color' => '#0066FF', 'color2' => '#818CF8']],
            $members->map(function ($m) {
                return [
                    'id' => $m->id,
                    'name' => $m->name,
                    'role' => $m->pivot->role ?? 'viewer',
                    'isOnline' => false,
                    'isEditing' => false,
                    'color' => '#10B981',
                    'color2' => '#34D399',
                ];
            })->toArray()
        );

        return view('builds.show', compact('build', 'members', 'messages', 'userRole', 'membersData', 'presets'));
    }

    public function update(Request $request, Build $build)
    {
        $this->authorize('update', $build);

        $validated = $request->validate([
            'current_floor' => 'integer|min:1|max:10',
            'roof_visible' => 'boolean',
        ]);

        $build->update($validated);

        return response()->json($build);
    }

    public function duplicate(Build $build)
    {
        $this->authorize('view', $build);

        $copy = $build->replicate();
        $copy->name = $build->name.' (Copy)';
        $copy->save();

        $copy->members()->attach(Auth::id(), ['role' => 'editor']);

        return redirect()->route('builds.show', $copy);
    }

    public function destroy(Build $build)
    {
        $this->authorize('delete', $build);

        $build->delete();

        return redirect()->route('dashboard');
    }

    public function addMember(Request $request, Build $build)
    {
        $this->authorize('update', $build);

        $validated = $request->validate([
            'name' => 'required|string|exists:users,name',
            'role' => 'required|in:viewer',
        ]);

        $user = User::where('name', $validated['name'])->first();

        $build->members()->syncWithoutDetaching([
            $user->id => ['role' => $validated['role']],
        ]);

        return back()->with('success', "{$user->name} added as {$validated['role']}.");
    }

    public function removeMember(Build $build, User $user)
    {
        $this->authorize('update', $build);

        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot remove yourself.');
        }

        $build->members()->detach($user->id);

        return back()->with('success', "{$user->name} removed.");
    }

    public function export(Build $build, string $format)
    {
        $this->authorize('view', $build);

        if ($format === 'json') {
            $exportData = [
                'name' => $build->name,
                'description' => $build->description,
                'version' => '1.0',
                'exported_at' => now()->toIso8601String(),
                'current_floor' => $build->current_floor,
                'roof_visible' => $build->roof_visible,
                'parts' => $build->parts()->get()->toArray(),
            ];

            return response()->json($exportData)
                ->header('Content-Disposition', 'attachment; filename='.$build->name.'.json');
        }

        abort(404, 'Export format not supported');
    }

    public function shared(Build $build, string $token)
    {
        $share = $build->shares()->where('token', $token)->first();

        if (! $share) {
            abort(403, 'Invalid share link.');
        }

        $members = $build->members()->withPivot('role')->get();
        $messages = $build->messages()->with('user')->latest()->take(50)->get()->reverse()->values();
        $presets = PartPreset::active()->orderBy('type')->orderBy('name')->get()->groupBy('type');

        return view('builds.shared', compact('build', 'members', 'messages', 'presets'));
    }
}
