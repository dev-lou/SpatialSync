<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseUserService
{
    private string $url;

    private string $serviceKey;

    public function __construct()
    {
        $this->url = config('supabase.url');
        $this->serviceKey = config('supabase.service_key');
    }

    private function serviceHeaders(): array
    {
        return [
            'apikey' => $this->serviceKey,
            'Authorization' => "Bearer {$this->serviceKey}",
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ];
    }

    /**
     * Find user by email
     */
    public function findByEmail(string $email): ?array
    {
        $url = "{$this->url}/rest/v1/users?email=eq.{$email}";

        try {
            $response = Http::withHeaders($this->serviceHeaders())->get($url);
            if ($response->successful()) {
                $data = $response->json();

                return $data[0] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Supabase findByEmail error: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Find user by ID
     */
    public function findById(string $id): ?array
    {
        $url = "{$this->url}/rest/v1/users?id=eq.{$id}";

        try {
            $response = Http::withHeaders($this->serviceHeaders())->get($url);
            if ($response->successful()) {
                $data = $response->json();

                return $data[0] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error("Supabase findById error: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Create new user
     */
    public function create(array $data): ?array
    {
        $url = "{$this->url}/rest/v1/users";

        try {
            $response = Http::withHeaders($this->serviceHeaders())->post($url, $data);
            if ($response->successful()) {
                return $response->json()[0] ?? null;
            }
            Log::error("Supabase create user failed: {$response->body()}");

            return null;
        } catch (\Exception $e) {
            Log::error("Supabase create user error: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Update user
     */
    public function update(string $id, array $data): bool
    {
        $url = "{$this->url}/rest/v1/users?id=eq.{$id}";

        try {
            $response = Http::withHeaders($this->serviceHeaders())->patch($url, $data);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Supabase update user error: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Verify password
     */
    public function verifyPassword(string $email, string $password): ?array
    {
        $user = $this->findByEmail($email);

        if (! $user) {
            return null;
        }

        // For now, we'll store password as plain text in Supabase
        // and compare directly. In production, you'd use Supabase Auth.
        if (isset($user['password']) && $user['password'] === $password) {
            return $user;
        }

        return null;
    }

    /**
     * Get all users
     */
    public function all(): array
    {
        $url = "{$this->url}/rest/v1/users?select=*";

        try {
            $response = Http::withHeaders($this->serviceHeaders())->get($url);

            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error("Supabase get all users error: {$e->getMessage()}");

            return [];
        }
    }

    /**
     * Search users by email or name
     */
    public function search(string $query): array
    {
        $url = "{$this->url}/rest/v1/users?or=(email.ilike.%{$query}%,name.ilike.%{$query}%)";

        try {
            $response = Http::withHeaders($this->serviceHeaders())->get($url);

            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error("Supabase search users error: {$e->getMessage()}");

            return [];
        }
    }
}
