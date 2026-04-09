<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PartPreset;

class PartPresetController extends Controller
{
    public function index()
    {
        $presets = PartPreset::active()
            ->orderBy('type')
            ->orderBy('name')
            ->get()
            ->groupBy('type');

        return response()->json($presets);
    }
}
