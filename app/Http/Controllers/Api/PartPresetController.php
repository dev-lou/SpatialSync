<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SupabaseClient;

class PartPresetController extends Controller
{
    protected SupabaseClient $supabase;

    public function __construct()
    {
        $this->supabase = app(SupabaseClient::class);
    }

    public function index()
    {
        $presets = $this->supabase->select('part_presets', ['*'], ['is_active' => 'true']);

        // Group by type
        $grouped = [];
        foreach ($presets as $preset) {
            $type = $preset['type'];
            if (! isset($grouped[$type])) {
                $grouped[$type] = [];
            }
            $grouped[$type][] = $preset;
        }

        // Sort each group by name
        foreach ($grouped as $type => $items) {
            usort($grouped[$type], function ($a, $b) {
                return ($a['name'] ?? '') <=> ($b['name'] ?? '');
            });
        }

        return response()->json($grouped);
    }
}
