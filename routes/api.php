<?php

use App\Http\Controllers\Api\PartPresetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Part presets (public)
Route::get('/part-presets', [PartPresetController::class, 'index']);
