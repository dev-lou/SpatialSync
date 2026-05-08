<?php

namespace App\Providers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\ServiceProvider;

class SupabaseAuthServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        //
    }
}

class SupabaseUser implements Authenticatable
{
    protected array $user;

    public function __construct(array $user)
    {
        $this->user = $user;
    }

    public function getAuthIdentifierName(): string
    {
        return 'id';
    }

    public function getAuthIdentifier(): mixed
    {
        return $this->user['id'] ?? null;
    }

    public function getAuthPassword(): string
    {
        return $this->user['password'] ?? '';
    }

    public function getAuthPasswordName(): string
    {
        return 'password';
    }

    public function getRememberToken(): ?string
    {
        return null;
    }

    public function setRememberToken($value): void {}

    public function getRememberTokenName(): string
    {
        return 'remember_token';
    }

    public function __get($key): mixed
    {
        return $this->user[$key] ?? null;
    }
}
