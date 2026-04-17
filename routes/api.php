<?php

use App\Http\Controllers\Api\BuildPartController;
use App\Http\Controllers\Api\PartPresetController;
use App\Http\Controllers\Api\PermissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (for now, since we're using Supabase)
Route::get('/builds/{buildId}/parts', [BuildPartController::class, 'allParts']);
Route::get('/builds/{buildId}/parts/floor', [BuildPartController::class, 'index']);

// Protected routes - require edit_geometry permission
Route::post('/builds/{buildId}/parts', [BuildPartController::class, 'store'])
    ->middleware('build.permission:edit_geometry');
Route::put('/builds/{buildId}/parts/{partId}', [BuildPartController::class, 'update'])
    ->middleware('build.permission:edit_geometry');
Route::delete('/builds/{buildId}/parts/{partId}', [BuildPartController::class, 'destroy'])
    ->middleware('build.permission:delete_parts');

// Permission API routes
Route::get('/builds/{buildId}/permissions', [PermissionController::class, 'getPermissions']);
Route::get('/builds/{buildId}/permissions/{permission}', [PermissionController::class, 'checkPermission']);

// Part presets (public)
Route::get('/part-presets', [PartPresetController::class, 'index']);
