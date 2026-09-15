<?php

namespace App\Http\Controllers;

use App\Http\AuthenticatedRequest;
use App\Http\Requests\UpdatePasswordRequest;
use App\Http\Requests\UpdateProfileRequest;
use App\Http\Requests\UploadAvatarRequest;
use App\Services\SupabaseClient;
use App\Services\SupabaseUserService;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Show the user profile page.
     */
    public function show(AuthenticatedRequest $request, SupabaseUserService $supabaseUserService)
    {
        $userId = $request->auth_user_id;

        // Data is already merged into the request by SupabaseAuthenticate middleware
        return view('profile.show', [
            'user' => (object) [
                'id' => $userId,
                'name' => $request->auth_user_name,
                'email' => $request->auth_user_email,
                'plan' => $request->auth_user_plan,
                'is_admin' => $request->auth_user_admin,
                'avatar_url' => $request->auth_user_avatar,
            ],
        ]);
    }

    /**
     * Update the user profile (display name).
     */
    public function update(UpdateProfileRequest $request, SupabaseUserService $supabaseUserService)
    {
        $validated = $request->validated();

        $userId = $request->auth_user_id;
        if ($userId !== null) {
            $supabaseUserService->update($userId, ['name' => $validated['name']]);
        }

        $request->session()->put('supabase_user_name', $validated['name']);

        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Upload profile avatar
     */
    public function uploadAvatar(UploadAvatarRequest $request, SupabaseClient $supabaseClient, SupabaseUserService $supabaseUserService)
    {
        $userId = $request->auth_user_id;
        if ($userId === null) {
            return back()->with('error', 'Unauthenticated');
        }

        $file = $request->file('avatar');
        $filename = "{$userId}_".time().'.'.($file ? $file->getClientOriginalExtension() : 'jpg');
        $path = "avatars/{$filename}";

        $uploadedPath = $supabaseClient->uploadFile(
            'storage',
            $path,
            $file ? file_get_contents((string) $file->getRealPath()) : '',
            $file ? (string) $file->getMimeType() : 'image/jpeg'
        );

        if ($uploadedPath) {
            $publicUrl = $supabaseClient->getPublicUrl('storage', $uploadedPath);

            // Update user in Supabase
            $supabaseUserService->update($userId, ['avatar_url' => $publicUrl]);

            // Update session
            $request->session()->put('supabase_user_avatar', $publicUrl);

            return back()->with('success', 'Avatar updated successfully.');
        }

        return back()->with('error', 'Failed to upload avatar.');
    }

    /**
     * Update password
     */
    public function updatePassword(UpdatePasswordRequest $request, SupabaseUserService $supabaseUserService)
    {
        $validated = $request->validated();

        $email = (string) ($request->auth_user_email ?? '');
        $userId = $request->auth_user_id;

        // Verify current password
        $user = $supabaseUserService->verifyPassword($email, (string) $request->current_password);

        if ($user === null) {
            return back()->withErrors(['current_password' => 'The provided password does not match our records.']);
        }

        // Update password (hashing it as we do in registration)
        $supabaseUserService->update((string) $userId, [
            'password' => Hash::make((string) $validated['password']),
        ]);

        return back()->with('success', 'Password updated successfully.');
    }
}
