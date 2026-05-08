<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $isAdmin = $request->session()->get('supabase_user_admin', false);

        if (! $isAdmin) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return $next($request);
    }
}
