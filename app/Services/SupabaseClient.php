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
            $keyStr = (string) $key;
            if (is_array($value)) {
                if (isset($value['op'])) {
                    $url .= '&'.urlencode($keyStr).'='.$value['op'].'.'.urlencode((string) $value['value']);
                } else {
                    $vals = implode(',', array_map(function ($v) {
                        return urlencode((string) $v);
                    }, $value));
                    $url .= '&'.urlencode($keyStr).'=in.('.$vals.')';
                }
            } else {
                $url .= '&'.urlencode($keyStr).'=eq.'.urlencode((string) $value);
            }
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
     * Select with ilike filter for text search
     */
    public function selectLike(string $table, array $columns, string $column, string $query, int $limit = 5): array
    {
        $url = "{$this->url}/rest/v1/{$table}?select=".implode(',', $columns);
        $url .= '&'.urlencode($column).'=ilike.'.'*'.urlencode($query).'*';
        $url .= '&limit='.$limit;

        try {
            $response = Http::withHeaders($this->serviceHeaders())->timeout(30)->get($url);

            return $response->successful() ? $response->json() : [];
        } catch (\Exception $e) {
            Log::error("Supabase selectLike error: {$e->getMessage()}");

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
            $url .= ($first ? '?' : '&').urlencode($key).'=eq.'.urlencode($value);
            $first = false;
        }

        try {
            $response = Http::withHeaders($this->serviceHeaders())->timeout(30)->patch($url, $data);

            if (! $response->successful()) {
                Log::error("Supabase update failed [{$response->status()}]: {$response->body()}");

                return 0;
            }

            $json = $response->json();

            return is_array($json) && count($json) > 0 ? count($json) : 1;
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
            $url .= ($first ? '?' : '&').urlencode($key).'=eq.'.urlencode($value);
            $first = false;
        }

        try {
            $response = Http::withHeaders($this->serviceHeaders())->timeout(30)->delete($url);

            if (! $response->successful()) {
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

    /**
     * Upload a file to Supabase Storage
     */
    public function uploadFile(string $bucket, string $path, $fileContents, string $mimeType): ?string
    {
        $url = "{$this->url}/storage/v1/object/{$bucket}/{$path}";

        $headers = [
            'apikey' => $this->serviceKey,
            'Authorization' => "Bearer {$this->serviceKey}",
            'Content-Type' => $mimeType,
            'x-upsert' => 'true',
        ];

        try {
            $response = Http::withHeaders($headers)
                ->timeout(60)
                ->send('POST', $url, [
                    'body' => $fileContents,
                ]);

            if ($response->successful()) {
                // Return the path so we can construct public URL
                return $path;
            }

            Log::error("Supabase file upload failed [{$response->status()}]: {$response->body()}");

            return null;
        } catch (\Exception $e) {
            Log::error("Supabase file upload error: {$e->getMessage()}");

            return null;
        }
    }

    /**
     * Get the public URL for a storage object
     */
    public function getPublicUrl(string $bucket, string $path): string
    {
        return "{$this->url}/storage/v1/object/public/{$bucket}/{$path}";
    }
}
