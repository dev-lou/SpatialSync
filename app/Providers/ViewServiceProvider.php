<?php

namespace App\Providers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View;

class ViewServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Share authenticated user data with all views
        view()->composer('*', function (View $view) {
            $userId = Session::get('supabase_user_id');
            $userName = Session::get('supabase_user_name');
            $userEmail = Session::get('supabase_user_email');
            $isAdmin = Session::get('supabase_user_admin', false);

            $view->with([
                'auth_user' => $userId ? (object) [
                    'id' => $userId,
                    'name' => $userName,
                    'email' => $userEmail,
                    'is_admin' => $isAdmin,
                ] : null,
                'auth_user_id' => $userId,
                'auth_user_name' => $userName,
            ]);
        });
    }
}
