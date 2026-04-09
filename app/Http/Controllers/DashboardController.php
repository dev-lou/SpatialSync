<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $builds = $user->buildMemberships()
            ->with('creator')
            ->latest()
            ->get();

        return view('dashboard', compact('builds'));
    }
}
