@extends('layouts.app')
@section('title', 'This link is no longer active')
@section('description', 'This review link has expired or been revoked.')

@section('content')
<div class="container" style="max-width: 560px; padding: 96px 20px; text-align: center;">

    <div style="width: 64px; height: 64px; margin: 0 auto 20px; border-radius: 20px; background: var(--bg-secondary); display: grid; place-items: center; border: 1px solid var(--border-default);">
        <i data-lucide="link-2-off" style="width: 28px; height: 28px; color: var(--text-secondary);"></i>
    </div>

    <h1 style="font-size: 1.6rem; font-weight: 800; margin: 0 0 10px;">This review link isn't active</h1>

    <p style="color: var(--text-secondary); margin: 0 0 24px; font-size: 1rem;">
        Review links can expire, or the owner may have revoked this one.
        Ask whoever sent it to you for a fresh link.
    </p>

    <a href="{{ route('home') }}" class="btn btn--secondary btn--lg" style="justify-content: center;">
        Go to the homepage
    </a>

</div>
@endsection
