@extends('layouts.auth')
@section('title', 'Create Account')

@section('content')
    <form method="POST" action="{{ route('register') }}" class="auth-form">
        @csrf

        <x-input
            label="Full Name"
            name="name"
            type="text"
            placeholder="John Smith"
            :required="true"
            autocomplete="name"
        />

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
            placeholder="Create a strong password"
            :required="true"
            autocomplete="new-password"
        />

        <x-input
            label="Confirm Password"
            name="password_confirmation"
            type="password"
            placeholder="Confirm your password"
            :required="true"
            autocomplete="new-password"
        />

        <button type="submit" class="btn btn--primary btn--lg w-full">
            Create account
        </button>
    </form>

    <div class="auth-footer">
        Already have an account? <a href="{{ route('login') }}">Sign in</a>
    </div>
@endsection
