<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupabaseClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class BuildIssueController extends Controller
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
     * List all issues for a build
     */
    public function index(Request $request, $buildId)
    {
        if (!$this->checkBuildAccess($request, $buildId)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $status = $request->get('status');
        $priority = $request->get('priority');

        $filters = ['build_id' => $buildId];
        if ($status) {
            $filters['status'] = $status;
        }
        if ($priority) {
            $filters['priority'] = $priority;
        }

        $issues = $this->supabase->select('build_issues', ['*'], $filters);

        // Sort by status (open first) then priority
        usort($issues, function ($a, $b) {
            $statusOrder = ['open' => 0, 'in_progress' => 1, 'resolved' => 2, 'closed' => 3];
            $statusCompare = ($statusOrder[$a['status']] ?? 4) - ($statusOrder[$b['status']] ?? 4);
            if ($statusCompare !== 0) {
                return $statusCompare;
            }

            $priorityOrder = ['critical' => 0, 'high' => 1, 'medium' => 2, 'low' => 3];
            return ($priorityOrder[$a['priority']] ?? 4) - ($priorityOrder[$b['priority']] ?? 4);
        });

        // Get user names for each issue
        $userIds = array_unique(array_filter(array_column($issues, 'created_by')));
        $users = [];
        if (!empty($userIds)) {
            $userData = $this->supabase->select('users', ['id', 'name'], []);
            $users = collect($userData)->keyBy('id')->toArray();
        }

        // Format issues with metadata
        $formattedIssues = array_map(function ($issue) use ($users) {
            $issue['creator_name'] = $users[$issue['created_by']]['name'] ?? 'Unknown';
            $issue['status_color'] = $this->getStatusColor($issue['status']);
            $issue['priority_color'] = $this->getPriorityColor($issue['priority']);
            $issue['status_label'] = $this->getStatusLabel($issue['status']);
            $issue['priority_label'] = $this->getPriorityLabel($issue['priority']);
            return $issue;
        }, $issues);

        return response()->json($formattedIssues);
    }

    /**
     * Create a new issue
     */
    public function store(Request $request, $buildId)
    {
        if (!$this->checkBuildAccess($request, $buildId)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'required|in:low,medium,high,critical',
            'part_id' => 'nullable|string|uuid',
            'position_x' => 'nullable|numeric',
            'position_y' => 'nullable|numeric',
            'position_z' => 'nullable|numeric',
        ]);

        $userId = $request->session()->get('supabase_user_id');

        $data = [
            'id' => Str::uuid()->toString(),
            'build_id' => $buildId,
            'created_by' => $userId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'priority' => $validated['priority'],
            'status' => 'open',
            'part_id' => $validated['part_id'] ?? null,
            'position_x' => $validated['position_x'] ?? null,
            'position_y' => $validated['position_y'] ?? null,
            'position_z' => $validated['position_z'] ?? null,
        ];

        $issue = $this->supabase->insert('build_issues', $data);

        if (!$issue) {
            return response()->json(['error' => 'Failed to create issue'], 500);
        }

        // Get creator name
        $users = $this->supabase->select('users', ['name'], ['id' => $userId]);
        $issue['creator_name'] = $users[0]['name'] ?? 'Unknown';
        $issue['status_color'] = $this->getStatusColor($issue['status']);
        $issue['priority_color'] = $this->getPriorityColor($issue['priority']);
        $issue['status_label'] = $this->getStatusLabel($issue['status']);
        $issue['priority_label'] = $this->getPriorityLabel($issue['priority']);

        return response()->json($issue, 201);
    }

    /**
     * Get single issue
     */
    public function show(Request $request, $buildId, $issueId)
    {
        if (!$this->checkBuildAccess($request, $buildId)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $issues = $this->supabase->select('build_issues', ['*'], [
            'id' => $issueId,
            'build_id' => $buildId
        ]);

        if (empty($issues)) {
            return response()->json(['error' => 'Issue not found'], 404);
        }

        $issue = $issues[0];

        // Get creator name
        if ($issue['created_by']) {
            $users = $this->supabase->select('users', ['name'], ['id' => $issue['created_by']]);
            $issue['creator_name'] = $users[0]['name'] ?? 'Unknown';
        }

        $issue['status_color'] = $this->getStatusColor($issue['status']);
        $issue['priority_color'] = $this->getPriorityColor($issue['priority']);
        $issue['status_label'] = $this->getStatusLabel($issue['status']);
        $issue['priority_label'] = $this->getPriorityLabel($issue['priority']);

        return response()->json($issue);
    }

    /**
     * Update issue
     */
    public function update(Request $request, $buildId, $issueId)
    {
        if (!$this->checkBuildAccess($request, $buildId)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string',
            'priority' => 'sometimes|in:low,medium,high,critical',
            'status' => 'sometimes|in:open,in_progress,resolved,closed',
        ]);

        // Check if issue exists
        $issues = $this->supabase->select('build_issues', ['*'], [
            'id' => $issueId,
            'build_id' => $buildId
        ]);

        if (empty($issues)) {
            return response()->json(['error' => 'Issue not found'], 404);
        }

        $count = $this->supabase->update('build_issues', $validated, [
            'id' => $issueId,
            'build_id' => $buildId
        ]);

        if ($count === 0) {
            return response()->json(['error' => 'Failed to update issue'], 500);
        }

        // Get updated issue
        $issues = $this->supabase->select('build_issues', ['*'], ['id' => $issueId]);
        $issue = $issues[0];

        // Get creator name
        if ($issue['created_by']) {
            $users = $this->supabase->select('users', ['name'], ['id' => $issue['created_by']]);
            $issue['creator_name'] = $users[0]['name'] ?? 'Unknown';
        }

        $issue['status_color'] = $this->getStatusColor($issue['status']);
        $issue['priority_color'] = $this->getPriorityColor($issue['priority']);
        $issue['status_label'] = $this->getStatusLabel($issue['status']);
        $issue['priority_label'] = $this->getPriorityLabel($issue['priority']);

        return response()->json($issue);
    }

    /**
     * Delete issue
     */
    public function destroy(Request $request, $buildId, $issueId)
    {
        if (!$this->checkBuildAccess($request, $buildId)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        // Check if issue exists
        $issues = $this->supabase->select('build_issues', ['*'], [
            'id' => $issueId,
            'build_id' => $buildId
        ]);

        if (empty($issues)) {
            return response()->json(['error' => 'Issue not found'], 404);
        }

        $deleted = $this->supabase->delete('build_issues', [
            'id' => $issueId,
            'build_id' => $buildId
        ]);

        if (!$deleted) {
            return response()->json(['error' => 'Failed to delete issue'], 500);
        }

        return response()->json(['message' => 'Issue deleted successfully']);
    }

    /**
     * Update issue status only
     */
    public function updateStatus(Request $request, $buildId, $issueId)
    {
        if (!$this->checkBuildAccess($request, $buildId)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'status' => 'required|in:open,in_progress,resolved,closed',
        ]);

        $count = $this->supabase->update('build_issues', $validated, [
            'id' => $issueId,
            'build_id' => $buildId
        ]);

        if ($count === 0) {
            return response()->json(['error' => 'Issue not found'], 404);
        }

        return response()->json(['status' => $validated['status']]);
    }

    /**
     * Helper: Get status color
     */
    protected function getStatusColor(string $status): string
    {
        return match($status) {
            'open' => '#ef4444',
            'in_progress' => '#eab308',
            'resolved' => '#22c55e',
            'closed' => '#6b7280',
            default => '#6b7280',
        };
    }

    /**
     * Helper: Get priority color
     */
    protected function getPriorityColor(string $priority): string
    {
        return match($priority) {
            'critical' => '#dc2626',
            'high' => '#f97316',
            'medium' => '#eab308',
            'low' => '#22c55e',
            default => '#6b7280',
        };
    }

    /**
     * Helper: Get status label
     */
    protected function getStatusLabel(string $status): string
    {
        return match($status) {
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            default => ucfirst($status),
        };
    }

    /**
     * Helper: Get priority label
     */
    protected function getPriorityLabel(string $priority): string
    {
        return match($priority) {
            'critical' => 'Critical',
            'high' => 'High',
            'medium' => 'Medium',
            'low' => 'Low',
            default => ucfirst($priority),
        };
    }
}
