<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfSupabaseAuthenticated
{
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        if ($request->session()->has('supabase_user_id')) {
            return redirect()->route('builds.index');
        }

        return $next($request);
    }
}
