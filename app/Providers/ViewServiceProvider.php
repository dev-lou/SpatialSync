<?php

namespace App\Providers;

use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\URL;
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
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        // Share authenticated user data with all views
        view()->composer('*', function (View $view) {
            $userId = Session::get('supabase_user_id');
            $userName = Session::get('supabase_user_name');
            $userEmail = Session::get('supabase_user_email');
            $userPlan = Session::get('supabase_user_plan', 'free');
            $isAdmin = Session::get('supabase_user_admin', false);
            $userAvatar = Session::get('supabase_user_avatar');

            $view->with([
                'auth_user' => $userId ? (object) [
                    'id' => $userId,
                    'name' => $userName,
                    'email' => $userEmail,
                    'plan' => $userPlan,
                    'is_admin' => $isAdmin,
                    'avatar_url' => $userAvatar,
                ] : null,
                'auth_user_id' => $userId,
                'auth_user_name' => $userName,
                'auth_user_avatar' => $userAvatar,
            ]);
        });
    }
}
