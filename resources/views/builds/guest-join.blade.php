@extends('layouts.app')
@section('title', 'Review '.$build->name)
@section('description', 'Open this project in 3D and leave comments — no account needed.')

@section('content')
<div class="container" style="max-width: 620px; padding: 72px 20px 96px;">

    <div style="text-align: center; margin-bottom: 32px;">
        <div style="width: 64px; height: 64px; margin: 0 auto 20px; border-radius: 20px; background: var(--accent); display: grid; place-items: center; box-shadow: 0 12px 30px rgba(59, 130, 246, 0.25);">
            <i data-lucide="eye" style="width: 30px; height: 30px; color: #fff;"></i>
        </div>
        <p style="font-size: 0.75rem; font-weight: 800; letter-spacing: 0.14em; text-transform: uppercase; color: var(--accent); margin: 0 0 8px;">
            You have been invited to review
        </p>
        <h1 style="font-size: 2rem; font-weight: 800; margin: 0 0 10px; letter-spacing: -0.02em;">
            {{ $build->name }}
        </h1>
        @if(! empty($build->description))
            <p style="color: var(--text-secondary); margin: 0; font-size: 1rem;">
                {{ $build->description }}
            </p>
        @endif
    </div>

    <div class="card" style="padding: 28px;">
        <h2 style="font-size: 1.05rem; font-weight: 700; margin: 0 0 6px;">What's your name?</h2>
        <p style="color: var(--text-secondary); font-size: 0.9rem; margin: 0 0 20px;">
            So the project owner knows who left which comment. No account, no password, no email.
        </p>

        <form method="POST" action="{{ route('share.join', ['token' => $token]) }}">
            @csrf
            <div class="input-group">
                <label class="input-label" for="name">Your name</label>
                <input class="input" type="text" id="name" name="name" maxlength="60"
                       placeholder="e.g. Maria from the client side" required autofocus
                       value="{{ old('name') }}">
            </div>

            @error('name')
                <p style="color: #dc2626; font-size: 0.85rem; margin: 8px 0 0;">{{ $message }}</p>
            @enderror

            <button type="submit" class="btn btn--primary btn--lg w-full"
                    style="justify-content: center; margin-top: 20px; gap: 8px;">
                <i data-lucide="move-3d" style="width: 18px; height: 18px;"></i>
                Open the model
            </button>
        </form>
    </div>

    <ul style="list-style: none; padding: 0; margin: 24px 0 0; display: grid; gap: 10px; color: var(--text-secondary); font-size: 0.9rem;">
        <li style="display: flex; gap: 10px; align-items: center;">
            <i data-lucide="mouse-pointer-click" style="width: 16px; height: 16px; color: var(--accent);"></i>
            Spin, zoom and walk around the design in 3D
        </li>
        <li style="display: flex; gap: 10px; align-items: center;">
            <i data-lucide="message-square" style="width: 16px; height: 16px; color: var(--accent);"></i>
            Pin a comment straight onto a wall, door or window
        </li>
        <li style="display: flex; gap: 10px; align-items: center;">
            <i data-lucide="lock" style="width: 16px; height: 16px; color: var(--accent);"></i>
            View and comment only — you cannot change the design
        </li>
    </ul>

</div>
@endsection
