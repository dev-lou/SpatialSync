<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupabaseClient;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

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

    public function index(Request $request, $buildId)
    {
        // Check access
        if (!$this->checkBuildAccess($request, $buildId)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $messages = $this->supabase->select('build_messages', ['*'], ['build_id' => $buildId]);

        $messages = array_slice(array_reverse($messages), 0, 100);

        return response()->json($messages);
    }

    public function store(Request $request, $buildId)
    {
        // Check access
        if (!$this->checkBuildAccess($request, $buildId)) {
            return response()->json(['error' => 'Access denied'], 403);
        }

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userId = $request->session()->get('supabase_user_id');
        $userName = $request->session()->get('supabase_user_name', 'User');

        $messageData = [
            'id' => Str::uuid()->toString(),
            'build_id' => $buildId,
            'user_id' => $userId,
            'message' => $validated['message'],
            'created_at' => now()->toIso8601String(),
        ];

        $message = $this->supabase->insert('build_messages', $messageData);
        
        if (!$message) {
            Log::error('Failed to insert message to Supabase', ['data' => $messageData]);
            return response()->json(['error' => 'Failed to save message'], 500);
        }
        
        // Add virtual user object for UI compatibility
        $message['user'] = ['name' => $userName];

        return response()->json($message, 201);
    }
}
