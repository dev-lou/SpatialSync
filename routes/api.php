<?php

use App\Http\Controllers\Api\BuildMessageController;
use App\Http\Controllers\Api\BuildPartController;
use App\Http\Controllers\Api\PartPresetController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Build parts
    Route::get('/builds/{build}/parts', [BuildPartController::class, 'allParts']);
    Route::get('/builds/{build}/parts/floor', [BuildPartController::class, 'index']);
    Route::post('/builds/{build}/parts', [BuildPartController::class, 'store']);
    Route::put('/builds/{build}/parts/{part}', [BuildPartController::class, 'update']);
    Route::delete('/builds/{build}/parts/{part}', [BuildPartController::class, 'destroy']);

    // Build messages
    Route::get('/builds/{build}/messages', [BuildMessageController::class, 'index']);
    Route::post('/builds/{build}/messages', [BuildMessageController::class, 'store']);

    // Part presets (public for authenticated users)
    Route::get('/part-presets', [PartPresetController::class, 'index']);
});
