<?php

namespace App\Http\Controllers\Api;

use App\Http\AuthenticatedRequest;
use App\Http\Controllers\Controller;
use App\Services\SupabaseClient;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BuildMessageController extends Controller
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
            'user_id' => $userId,
        ]);

        return $members !== [];
    }

    public function index(AuthenticatedRequest $request, string $buildId)
    {
        // Check access
        if (! $this->checkBuildAccess($request, $buildId)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $messages = $this->supabase->select('build_messages', ['*'], ['build_id' => $buildId]);

        $messages = array_slice(array_reverse($messages), 0, 100);

        $userIds = array_unique(array_filter(array_column($messages, 'user_id'), fn ($v) => $v !== null && $v !== ''));
        if ($userIds !== []) {
            $allUsers = $this->supabase->select('users', ['id', 'name'], []);
            $userMap = [];
            foreach ($allUsers as $u) {
                if (in_array($u['id'], $userIds, true)) {
                    $userMap[$u['id']] = $u;
                }
            }
            foreach ($messages as &$msg) {
                $uid = $msg['user_id'] ?? null;
                $msg['user'] = ['name' => $userMap[$uid]['name'] ?? 'Collaborator'];
            }
        }

        return response()->json(array_values($messages));
    }

    public function store(AuthenticatedRequest $request, string $buildId)
    {
        // Check access
        if (! $this->checkBuildAccess($request, $buildId)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userId = $request->auth_user_id;
        $userName = $request->auth_user_name ?? 'User';

        $messageData = [
            'id' => Str::uuid()->toString(),
            'build_id' => $buildId,
            'user_id' => $userId,
            'message' => $validated['message'],
            'created_at' => now()->toIso8601String(),
        ];

        $message = $this->supabase->insert('build_messages', $messageData);

        if (! $message) {
            Log::error('Failed to insert message to Supabase', ['data' => $messageData]);

            return response()->json(['error' => 'Failed to save message'], 500);
        }

        // Add virtual user object for UI compatibility
        $message['user'] = ['name' => $userName];

        return response()->json($message, 201);
    }
}
