<?php

namespace App\Http\Middleware;

use Closure;
use App\Http\AuthenticatedRequest;
use Symfony\Component\HttpFoundation\Response;

class SupabaseAuthenticate
{
    public function handle(AuthenticatedRequest $request, Closure $next, ...$guards): Response
    {
        if (! $request->session()->has('supabase_user_id')) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->route('login');
        }

        // Set the authenticated user on the request
        $request->merge([
            'auth_user_id' => $request->session()->get('supabase_user_id'),
            'auth_user_email' => $request->session()->get('supabase_user_email'),
            'auth_user_name' => $request->session()->get('supabase_user_name'),
            'auth_user_plan' => $request->session()->get('supabase_user_plan', 'free'),
            'auth_user_admin' => $request->session()->get('supabase_user_admin', false),
            'auth_user_avatar' => $request->session()->get('supabase_user_avatar'),
        ]);

        /** @var Response $response */
        $response = $next($request);
        return $response;
    }
}
