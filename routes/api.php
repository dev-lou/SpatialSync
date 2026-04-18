<?php

use App\Http\Controllers\Api\BuildIssueController;
use App\Http\Controllers\Api\BuildPartController;
use App\Http\Controllers\Api\PartPresetController;
use App\Http\Controllers\Api\PermissionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Part presets (public)
Route::get('/part-presets', [PartPresetController::class, 'index']);
