<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'ConstructHub')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="stylesheet" href="https://fonts.bunny.net/css?family=plus-jakarta-sans:300,400,500,600,700|instrument-serif:400">

    <!-- Design Tokens -->
    <link rel="stylesheet" href="{{ asset('css/tokens.css') }}">
    <link rel="stylesheet" href="{{ asset('css/components.css') }}">

    <!-- Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>

    <style>
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--bg-secondary);
            padding: var(--space-4);
        }

        .auth-container {
            width: 100%;
            max-width: 420px;
        }

        .auth-card {
            background: var(--surface);
            border-radius: var(--radius-xl);
            padding: var(--space-8);
            box-shadow: var(--shadow-lg);
        }

        .auth-header {
            text-align: center;
            margin-bottom: var(--space-8);
        }

        .auth-logo {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 3rem;
            height: 3rem;
            background: var(--accent);
            border-radius: var(--radius-lg);
            color: var(--text-inverse);
            margin-bottom: var(--space-4);
            text-decoration: none;
        }

        .auth-logo:hover {
            opacity: 0.9;
        }

        .auth-title {
            font-size: var(--text-2xl);
            font-weight: 700;
            color: var(--text-primary);
            margin-bottom: var(--space-2);
        }

        .auth-subtitle {
            font-size: var(--text-sm);
            color: var(--text-secondary);
        }

        .auth-form {
            display: flex;
            flex-direction: column;
            gap: var(--space-4);
        }

        .auth-footer {
            text-align: center;
            margin-top: var(--space-6);
            padding-top: var(--space-6);
            border-top: 1px solid var(--border-default);
            font-size: var(--text-sm);
            color: var(--text-secondary);
        }

        .auth-footer a {
            color: var(--accent);
            font-weight: 500;
            text-decoration: none;
        }

        .auth-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-header">
                <a href="{{ route('home') }}" class="auth-logo">
                    <i data-lucide="layout" class="w-6 h-6"></i>
                </a>
                <h1 class="auth-title">@yield('title', 'Welcome Back')</h1>
            </div>

            @yield('content')
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            lucide.createIcons();
        });
    </script>
</body>
</html>
