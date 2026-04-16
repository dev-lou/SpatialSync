<?php

use App\Http\Controllers\Api\BuildPartController;
use App\Http\Controllers\Api\PartPresetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (for now, since we're using Supabase)
Route::get('/builds/{buildId}/parts', [BuildPartController::class, 'allParts']);
Route::get('/builds/{buildId}/parts/floor', [BuildPartController::class, 'index']);
Route::post('/builds/{buildId}/parts', [BuildPartController::class, 'store']);
Route::put('/builds/{buildId}/parts/{partId}', [BuildPartController::class, 'update']);
Route::delete('/builds/{buildId}/parts/{partId}', [BuildPartController::class, 'destroy']);

// Part presets (public)
Route::get('/part-presets', [PartPresetController::class, 'index']);
