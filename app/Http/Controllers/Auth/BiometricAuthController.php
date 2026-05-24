<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\SupabaseClient;
use App\Services\SupabaseUserService;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class BiometricAuthController extends Controller
{
    protected SupabaseClient $supabase;

    protected SupabaseUserService $userService;

    public function __construct()
    {
        $this->supabase = app(SupabaseClient::class);
        $this->userService = app(SupabaseUserService::class);
    }

    /**
     * Authenticate user via facial biometric descriptor
     */
    public function login(Request $request)
    {
        $request->validate([
            'descriptor' => 'required|array',
        ]);

        $liveDescriptor = (array) $request->descriptor;

        // 1. Fetch all users who have biometric data enrolled
        // In a massive app, you'd filter by email or use a vector database,
        // but for this scale, a direct comparison is high-performance.
        $allUsers = $this->userService->all();
        $match = null;
        $minDistance = 1.0; // Max possible distance
        $threshold = 0.45; // Standard sensitivity threshold for face-api.js

        foreach ($allUsers as $userData) {
            if (($userData['biometric_data'] ?? null) === null) {
                continue;
            }

            $rawData = $userData['biometric_data'];
            $storedDescriptor = null;

            // Handle 3 possible storage formats:
            // 1. Encrypted string (new format via Crypt::encryptString)
            if (is_string($rawData) && str_starts_with($rawData, 'ey')) {
                try {
                    $decrypted = Crypt::decryptString($rawData);
                    $storedDescriptor = json_decode($decrypted, true);
                } catch (DecryptException $e) {
                    // Not encrypted data — fall through
                }
            }

            // 2. JSON-encoded string (old ProfileController format)
            if ($storedDescriptor === null && is_string($rawData)) {
                $storedDescriptor = json_decode($rawData, true);
            }

            // 3. Native array (old AdminController format)
            if ($storedDescriptor === null && is_array($rawData)) {
                $storedDescriptor = $rawData;
            }

            if (! is_array($storedDescriptor) || $storedDescriptor === []) {
                continue;
            }

            $distance = $this->calculateEuclideanDistance($liveDescriptor, $storedDescriptor);

            if ($distance < $threshold && $distance < $minDistance) {
                $minDistance = $distance;
                $match = $userData;
            }
        }

        if ($match) {
            // 2. Successful Match - Initialize Session
            $user = (object) $match;

            // Set session data as per project standard (SupabaseAuthenticate middleware relies on these)
            session([
                'supabase_user_id' => $user->id,
                'supabase_user_email' => $user->email,
                'supabase_user_name' => $user->name,
                'supabase_user_avatar' => $user->avatar_url ?? '',
                'supabase_user_plan' => $user->plan ?? 'free',
                'supabase_user_admin' => $user->is_admin ?? false,
            ]);

            return response()->json([
                'success' => true,
                'name' => $user->name,
                'avatar_url' => $user->avatar_url ?? '',
                'message' => 'Identity verified. Welcome back, '.(string) $user->name,
                'redirect' => route('dashboard'),
            ]);
        }

        return response()->json([
            'message' => 'Identity verification failed. No match found.',
        ], 401);
    }

    /**
     * Calculate Euclidean Distance between two feature vectors
     */
    private function calculateEuclideanDistance(array $query, array $stored): float
    {
        if (count($query) !== count($stored)) {
            return 1.0;
        }

        $sum = 0.0;
        for ($i = 0; $i < count($query); $i++) {
            $sum += pow($query[$i] - $stored[$i], 2);
        }

        return sqrt($sum);
    }
}
