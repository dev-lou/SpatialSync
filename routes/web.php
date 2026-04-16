<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\BuildController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Public marketing pages
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/features', [PageController::class, 'features'])->name('features');
Route::get('/pricing', [PageController::class, 'pricing'])->name('pricing');
Route::get('/about', [PageController::class, 'about'])->name('about');

// Guest routes (auth)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);
});

// Logout
Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout')->middleware('auth');

// Authenticated routes
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Build routes
    Route::get('/builds', [BuildController::class, 'index'])->name('builds.index');
    Route::get('/builds/create', [BuildController::class, 'create'])->name('builds.create');
    Route::post('/builds', [BuildController::class, 'store'])->name('builds.store');
    Route::get('/builds/{build}', [BuildController::class, 'show'])->name('builds.show');
    Route::put('/builds/{build}', [BuildController::class, 'update'])->name('builds.update');
    Route::post('/builds/{build}/duplicate', [BuildController::class, 'duplicate'])->name('builds.duplicate');
    Route::delete('/builds/{build}', [BuildController::class, 'destroy'])->name('builds.destroy');
    Route::post('/builds/{build}/members', [BuildController::class, 'addMember'])->name('builds.members.add');
    Route::delete('/builds/{build}/members/{user}', [BuildController::class, 'removeMember'])->name('builds.members.remove');
    Route::patch('/builds/{build}/members/{user}', [BuildController::class, 'updateMemberRole'])->name('builds.members.updateRole');
    Route::get('/users/search', [BuildController::class, 'searchUsers'])->name('users.search');
    Route::post('/builds/{build}/share', [BuildController::class, 'createShare'])->name('builds.share.create');
    Route::get('/builds/{build}/export/{format}', [BuildController::class, 'export'])->name('builds.export');

    // Chat API
    Route::get('/api/builds/{build}/messages', [\App\Http\Controllers\Api\BuildMessageController::class, 'index'])->name('api.builds.messages.index');
    Route::post('/api/builds/{build}/messages', [\App\Http\Controllers\Api\BuildMessageController::class, 'store'])->name('api.builds.messages.store');

    // Shared build view
    Route::get('/builds/{build}/shared/{token}', [BuildController::class, 'shared'])->name('builds.shared');

    // Admin routes
    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
        Route::get('/builds', [AdminController::class, 'builds'])->name('admin.builds');
        Route::delete('/users/{user}', [AdminController::class, 'deleteUser'])->name('admin.users.delete');
        Route::delete('/builds/{build}', [AdminController::class, 'deleteBuild'])->name('admin.builds.delete');
    });
});
