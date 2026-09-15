<?php

namespace App\Http\Controllers\Api;

use App\Http\AuthenticatedRequest;
use App\Http\Concerns\ResolvesCollaboratorAccess;
use App\Http\Controllers\Controller;
use App\Services\SupabaseClient;
use App\Support\GuestReview;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class BuildMessageController extends Controller
{
    use ResolvesCollaboratorAccess;

    protected SupabaseClient $supabase;

    public function __construct()
    {
        $this->supabase = app(SupabaseClient::class);
    }

    /**
     * Check if the caller may read and post messages on this build. Members of
     * any role qualify, and so does a guest holding a valid share link.
     */
    protected function checkBuildAccess(AuthenticatedRequest $request, string $buildId): bool
    {
        return $this->collaboratorCanComment($request, $buildId);
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
        $userMap = [];
        if ($userIds !== []) {
            $allUsers = $this->supabase->select('users', ['id', 'name'], []);
            foreach ($allUsers as $u) {
                if (in_array($u['id'], $userIds, true)) {
                    $userMap[$u['id']] = $u;
                }
            }
        }

        $messages = array_map(function (mixed $msg) use ($userMap): array {
            $row = (array) $msg;
            $uid = $row['user_id'] ?? null;

            if ($uid === null) {
                // Guest reviewers have no user row: the name is carried at the
                // start of the message text.
                [$reviewer, $body] = GuestReview::splitMessage(
                    is_string($row['message'] ?? null) ? $row['message'] : null
                );
                $row['message'] = $body;
                $row['user'] = ['name' => $reviewer.' (client)'];
            } else {
                $row['user'] = ['name' => $userMap[$uid]['name'] ?? 'Collaborator'];
            }

            return $row;
        }, $messages);

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
        $isGuest = $this->isGuestReviewer($request);
        $requestedName = $request->auth_user_name;
        $userName = $isGuest
            ? $this->guestNameOrFallback($request)
            : (is_string($requestedName) ? $requestedName : 'User');
        $messageBody = is_string($validated['message'] ?? null) ? $validated['message'] : '';

        $messageData = [
            'id' => Str::uuid()->toString(),
            'build_id' => $buildId,
            'user_id' => $userId,
            'message' => $isGuest
                ? GuestReview::message($userName, $messageBody)
                : $messageBody,
            'created_at' => now()->toIso8601String(),
        ];

        $message = $this->supabase->insert('build_messages', $messageData);

        if (! $message) {
            Log::error('Failed to insert message to Supabase', ['data' => $messageData]);

            return response()->json(['error' => 'Failed to save message'], 500);
        }

        // Add virtual user object for UI compatibility
        $message['user'] = ['name' => $isGuest ? $userName.' (client)' : $userName];

        return response()->json($message, 201);
    }
}
