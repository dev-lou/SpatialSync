<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Build;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BuildMessageController extends Controller
{
    public function index(Build $build)
    {
        $this->authorize('view', $build);

        $messages = $build->messages()
            ->with('user:id,name')
            ->latest()
            ->take(100)
            ->get()
            ->reverse()
            ->values();

        return response()->json($messages);
    }

    public function store(Request $request, Build $build)
    {
        $this->authorize('view', $build);

        $validated = $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $message = $build->messages()->create([
            'user_id' => Auth::id(),
            'message' => $validated['message'],
        ]);

        $message->load('user:id,name');

        return response()->json($message, 201);
    }
}
