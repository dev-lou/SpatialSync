@extends('layouts.auth')
@section('title', 'Sign In')

@section('content')
    <form method="POST" action="{{ route('login') }}" class="auth-form">
        @csrf

        <x-input
            label="Email"
            name="email"
            type="email"
            placeholder="you@company.com"
            :required="true"
            autocomplete="email"
        />

        <x-input
            label="Password"
            name="password"
            type="password"
            placeholder="Enter your password"
            :required="true"
            autocomplete="current-password"
        />

        <div class="flex--between">
            <label class="flex items-center gap-2 text-sm text-secondary cursor-pointer">
                <input type="checkbox" name="remember" class="w-4 h-4 rounded border-border text-accent focus:ring-accent">
                Remember me
            </label>
            <a href="#" class="text-sm text-accent hover:underline">Forgot password?</a>
        </div>

        <button type="submit" class="btn btn--primary btn--lg w-full">
            Sign in
        </button>
    </form>

    <div class="auth-footer">
        Don't have an account? <a href="{{ route('register') }}">Sign up</a>
    </div>
@endsection
