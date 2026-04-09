<?php

namespace App\Providers;

use App\Models\Build;
use App\Policies\BuildPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        Build::class => BuildPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}
