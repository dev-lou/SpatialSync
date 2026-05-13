<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * Show the user profile page.
     */
    public function show(Request $request, \App\Services\SupabaseUserService $supabaseUserService)
    {
        $userId = $request->auth_user_id;
        $userRecord = $supabaseUserService->findById($userId);
        $hasBiometrics = !empty($userRecord['biometric_data']);

        // Data is already merged into the request by SupabaseAuthenticate middleware
        return view('profile.show', [
            'user' => (object)[
                'id' => $userId,
                'name' => $request->auth_user_name,
                'email' => $request->auth_user_email,
                'plan' => $request->auth_user_plan,
                'is_admin' => $request->auth_user_admin,
                'avatar_url' => $request->auth_user_avatar,
                'has_biometrics' => $hasBiometrics,
            ]
        ]);
    }

    /**
     * Update the user profile (display name).
     */
    public function update(Request $request, \App\Services\SupabaseUserService $supabaseUserService)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $userId = $request->session()->get('supabase_user_id');
        if ($userId) {
            $supabaseUserService->update($userId, ['name' => $validated['name']]);
        }

        $request->session()->put('supabase_user_name', $validated['name']);
        return back()->with('success', 'Profile updated successfully.');
    }

    /**
     * Upload profile avatar
     */
    public function uploadAvatar(Request $request, \App\Services\SupabaseClient $supabaseClient, \App\Services\SupabaseUserService $supabaseUserService)
    {
        $request->validate([
            'avatar' => 'required|image|max:5120', // 5MB max
        ]);

        $userId = $request->session()->get('supabase_user_id');
        if (!$userId) return back()->with('error', 'Unauthenticated');

        $file = $request->file('avatar');
        $filename = "{$userId}_" . time() . '.' . $file->getClientOriginalExtension();
        $path = "avatars/{$filename}";
        
        $uploadedPath = $supabaseClient->uploadFile(
            'storage', 
            $path, 
            file_get_contents($file->getRealPath()), 
            $file->getMimeType()
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
    public function updatePassword(Request $request, \App\Services\SupabaseUserService $supabaseUserService)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|min:8|confirmed',
        ]);

        $email = $request->session()->get('supabase_user_email');
        $userId = $request->session()->get('supabase_user_id');

        // Verify current password
        $user = $supabaseUserService->verifyPassword($email, $request->current_password);
        
        if (!$user) {
            return back()->withErrors(['current_password' => 'The provided password does not match our records.']);
        }

        // Update password (hashing it as we do in registration)
        $supabaseUserService->update($userId, [
            'password' => \Illuminate\Support\Facades\Hash::make($request->password)
        ]);

        return back()->with('success', 'Password updated successfully.');
    }

    /**
     * Save biometric descriptor for Face ID login
     */
    public function saveBiometrics(Request $request, \App\Services\SupabaseClient $supabaseClient)
    {
        $request->validate([
            'descriptor' => 'required|array',
        ]);

        $userId = $request->session()->get('supabase_user_id');
        
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Save to Supabase using biometric_data column
        $success = $supabaseClient->update('users', ['biometric_data' => json_encode($request->descriptor)], ['id' => $userId]);

        if ($success) {
            // Update session so UI knows it's setup
            $request->session()->put('supabase_user_has_biometrics', true);
            return response()->json(['message' => 'Face fingerprint saved successfully.']);
        }

        return response()->json(['message' => 'Failed to save biometric data.'], 500);
    }

    /**
     * Delete biometric descriptor
     */
    public function deleteBiometrics(Request $request, \App\Services\SupabaseClient $supabaseClient)
    {
        $userId = $request->session()->get('supabase_user_id');
        
        if (!$userId) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }

        // Set biometric_data to null in Supabase
        $success = $supabaseClient->update('users', ['biometric_data' => null], ['id' => $userId]);

        if ($success) {
            $request->session()->forget('supabase_user_has_biometrics');
            return response()->json(['message' => 'Face fingerprint removed successfully.']);
        }

        return response()->json(['message' => 'Failed to remove biometric data.'], 500);
    }
}
