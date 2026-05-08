<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseClient
{
    private string $url;

    private string $anonKey;

    private string $serviceKey;

    public function __construct()
    {
        $this->url = config('supabase.url');
        $this->anonKey = config('supabase.anon_key');
        $this->serviceKey = config('supabase.service_key');
    }

    /**
     * Get headers for service role (admin)
     */
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
     * Get headers for anon (public)
     */
    private function anonHeaders(): array
    {
        return [
            'apikey' => $this->anonKey,
            'Authorization' => "Bearer {$this->anonKey}",
            'Content-Type' => 'application/json',
            'Prefer' => 'return=representation',
        ];
    }

    /**
     * Select from table
     */
    public function select(string $table, array $columns = ['*'], array $filters = []): array
    {
        $url = "{$this->url}/rest/v1/{$table}?select=".implode(',', $columns);

        foreach ($filters as $key => $value) {
            $url .= '&'.urlencode($key).'=eq.'.urlencode($value);
        }

        try {
            $response = Http::withHeaders($this->serviceHeaders())->timeout(30)->get($url);

            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error("Supabase select error: {$e->getMessage()}");

            return [];
        }
    }

    /**
     * Insert into table
     */
    public function insert(string $table, array $data): ?array
    {
        $url = "{$this->url}/rest/v1/{$table}";

        try {
            $response = Http::withHeaders($this->serviceHeaders())->timeout(30)->post($url, $data);
            if ($response->successful()) {
                return $response->json()[0] ?? null;
            }
            Log::error("Supabase insert failed: {$response->body()}");

            return null;
        } catch (\Exception $e) {
            Log::error("Supabase insert error: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Insert multiple records
     */
    public function insertMany(string $table, array $data): bool
    {
        $url = "{$this->url}/rest/v1/{$table}";

        try {
            $response = Http::withHeaders($this->serviceHeaders())->timeout(30)->post($url, $data);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Supabase insertMany error: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Update records
     */
    public function update(string $table, array $data, array $filters): int
    {
        $url = "{$this->url}/rest/v1/{$table}";

        $first = true;
        foreach ($filters as $key => $value) {
            $url .= ($first ? '?' : '&') . urlencode($key).'=eq.'.urlencode($value);
            $first = false;
        }

        try {
            $response = Http::withHeaders($this->serviceHeaders())->timeout(30)->patch($url, $data);

            if (!$response->successful()) {
                Log::error("Supabase update failed [{$response->status()}]: {$response->body()}");
            }

            return $response->successful() ? count($response->json() ?? []) : 0;
        } catch (\Exception $e) {
            Log::error("Supabase update error: {$e->getMessage()}");

            return 0;
        }
    }

    /**
     * Delete records
     */
    public function delete(string $table, array $filters): bool
    {
        $url = "{$this->url}/rest/v1/{$table}";

        $first = true;
        foreach ($filters as $key => $value) {
            $url .= ($first ? '?' : '&') . urlencode($key).'=eq.'.urlencode($value);
            $first = false;
        }

        try {
            $response = Http::withHeaders($this->serviceHeaders())->timeout(30)->delete($url);

            if (!$response->successful()) {
                Log::error("Supabase delete failed [{$response->status()}]: {$response->body()}");
            }

            return $response->successful();
        } catch (\Exception $e) {
            Log::error("Supabase delete error: {$e->getMessage()}");

            return false;
        }
    }

    /**
     * Test connection
     */
    public function ping(): bool
    {
        try {
            $response = Http::withHeaders($this->anonHeaders())
                ->timeout(30)
                ->get("{$this->url}/rest/v1/builds?select=id&limit=1");

            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }
}
