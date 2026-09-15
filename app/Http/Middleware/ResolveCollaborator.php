<?php

namespace App\Http\Middleware;

use App\Http\AuthenticatedRequest;
use Closure;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the collaboration endpoints (issue pins and chat).
 *
 * Signed-in members are treated exactly as before: the same session keys are
 * merged onto the request that SupabaseAuthenticate would merge. A visitor who
 * arrived through a share link has no account, so their identity — the share
 * token, the build it belongs to, and the name they gave — is taken from the
 * session and merged instead. Controllers decide what a guest may do.
 */
class ResolveCollaborator
{
    public function handle(AuthenticatedRequest $request, Closure $next): Response
    {
        $session = $request->session();

        if ($session->has('supabase_user_id')) {
            $request->merge([
                'auth_user_id' => $session->get('supabase_user_id'),
                'auth_user_email' => $session->get('supabase_user_email'),
                'auth_user_name' => $session->get('supabase_user_name'),
                'auth_user_plan' => $session->get('supabase_user_plan', 'free'),
                'auth_user_admin' => $session->get('supabase_user_admin', false),
                'auth_user_avatar' => $session->get('supabase_user_avatar'),
            ]);

            /** @var Response $response */
            $response = $next($request);

            return $response;
        }

        $guest = $session->get('guest_review');
        $token = is_array($guest) ? ($guest['token'] ?? null) : null;
        $buildId = is_array($guest) ? ($guest['build_id'] ?? null) : null;
        $name = is_array($guest) ? ($guest['name'] ?? null) : null;

        if (is_string($token) && $token !== '' && is_string($buildId) && $buildId !== '') {
            $request->merge([
                'collab_guest_token' => $token,
                'collab_guest_build_id' => $buildId,
                'collab_guest_name' => is_string($name) ? $name : '',
            ]);

            /** @var Response $response */
            $response = $next($request);

            return $response;
        }

        if ($request->expectsJson()) {
            return response()->json(['error' => 'Open a share link to review this project.'], 401);
        }

        return redirect()->route('login');
    }
}
