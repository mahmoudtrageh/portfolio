@php
    $locale = app()->getLocale();
    $dir = config('portfolio.dir.'.$locale, 'ltr');
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <title>{{ __('Sign in') }}</title>

    <script>
        (function () {
            try {
                var stored = localStorage.getItem('theme');
                var dark = stored
                    ? stored === 'dark'
                    : window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', dark);
                document.documentElement.style.colorScheme = dark ? 'dark' : 'light';
            } catch (e) {}
        })();
    </script>

    <x-favicon />

    {{-- See resources/views/components/layout.blade.php: without this the
         built font files are never referenced by the page. --}}
    {{ Vite::fonts() }}

    @vite(['resources/css/app.css', 'resources/js/admin.js'])
</head>
<body class="min-h-dvh flex items-center justify-center p-5 antialiased"
      style="background: var(--bg-sunken);">

    <div class="w-full max-w-sm">
        <h1 class="font-display text-2xl text-center mb-6">{{ __('Sign in') }}</h1>

        <div class="card p-6 sm:p-7">
            @if ($errors->any())
                <div class="rounded-lg p-3 mb-5 text-sm"
                     style="background: oklch(0.6 0.2 25 / 0.12); color: oklch(0.5 0.2 25);"
                     role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium mb-2">
                        {{ __('Email') }}
                    </label>
                    <input type="email" id="email" name="email" required autofocus
                           value="{{ old('email') }}" autocomplete="username" dir="ltr"
                           class="w-full rounded-lg px-4 py-3 text-sm border outline-none"
                           style="background: var(--bg); border-color: var(--border); color: var(--text);">
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium mb-2">
                        {{ __('Password') }}
                    </label>
                    <input type="password" id="password" name="password" required
                           autocomplete="current-password" dir="ltr"
                           class="w-full rounded-lg px-4 py-3 text-sm border outline-none"
                           style="background: var(--bg); border-color: var(--border); color: var(--text);">
                </div>

                <label class="flex items-center gap-2 text-sm" style="color: var(--text-muted);">
                    <input type="checkbox" name="remember" value="1"
                           style="accent-color: var(--accent);">
                    {{ __('Remember me') }}
                </label>

                <button type="submit"
                        class="w-full px-6 py-3 rounded-full font-semibold transition-colors"
                        style="background: var(--accent-fill); color: var(--accent-text);">
                    {{ __('Sign in') }}
                </button>
            </form>
        </div>
    </div>

</body>
</html>
