{{--
    Tenant-aware auth layout — used by /member/login, /register, /profile.
    Loads the tenant's theme CSS (Bootstrap, custom CSS) instead of the
    admin-panel Vite bundle so the page matches the tenant's visual identity.
--}}
@php
    use App\Models\SiteSetting;
    use App\Models\Theme;

    $tenant    = tenancy()->tenant ?? null;
    $theme     = $tenant?->theme_id ? Theme::find($tenant->theme_id) : null;
    $themeCss  = data_get($theme?->assets_json, 'css', []);
    $themeJs   = data_get($theme?->assets_json, 'js',  []);
    $siteName  = $tenant?->name ?? SiteSetting::get('site.title', config('cms.name', 'Site'));
    $logoUrl   = SiteSetting::get('design.logo_url', '');
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $siteName)</title>
    <meta name="robots" content="noindex, nofollow">

    {{-- Tenant theme CSS (Bootstrap, sliders, etc.) --}}
    @foreach($themeCss as $href)
        <link rel="stylesheet" href="{{ $href }}">
    @endforeach

    {{-- Minimal fallback styles when the theme has no CSS (dev/test) --}}
    @if(empty($themeCss))
        <style>
            *, *::before, *::after { box-sizing: border-box; }
            body { margin: 0; font-family: system-ui, sans-serif; background: #f4f5f7; color: #1a1a1a; }
            .auth-wrap { display: flex; min-height: 100vh; align-items: center; justify-content: center; padding: 1.5rem; }
            .auth-card { background: #fff; border-radius: 1rem; padding: 2rem; width: 100%; max-width: 420px; box-shadow: 0 2px 16px rgba(0,0,0,.08); }
            .auth-logo { text-align: center; margin-bottom: 1.5rem; }
            .auth-logo img { max-height: 48px; }
            .auth-logo span { font-size: 1.4rem; font-weight: 700; }
            .auth-title { font-size: 1.1rem; font-weight: 600; text-align: center; margin-bottom: 1.25rem; }
            .form-group { margin-bottom: 1rem; }
            label { display: block; font-size: .875rem; font-weight: 500; margin-bottom: .35rem; }
            input[type=text], input[type=email], input[type=password], input[type=tel] {
                width: 100%; padding: .55rem .85rem; border: 1px solid #d1d5db; border-radius: .5rem; font-size: .9rem; outline: none;
            }
            input:focus { border-color: #6366f1; box-shadow: 0 0 0 3px rgba(99,102,241,.15); }
            .btn-primary { display: block; width: 100%; padding: .65rem; background: #6366f1; color: #fff; border: none; border-radius: .5rem; font-size: .95rem; font-weight: 600; cursor: pointer; }
            .btn-primary:hover { background: #4f46e5; }
            .alert { padding: .75rem 1rem; border-radius: .5rem; font-size: .875rem; margin-bottom: 1rem; }
            .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #991b1b; }
            .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; }
            .auth-footer { text-align: center; font-size: .85rem; color: #6b7280; margin-top: 1.25rem; }
            .auth-footer a { color: #6366f1; text-decoration: none; }
        </style>
    @endif

    @stack('head')
</head>
<body>
    <div class="auth-wrap" style="min-height:100vh; display:flex; align-items:center; justify-content:center; padding:1.5rem;">
        <div class="auth-card" style="width:100%; max-width:440px;">

            {{-- Site logo / name --}}
            <div class="auth-logo" style="text-align:center; margin-bottom:1.5rem;">
                @if($logoUrl)
                    <a href="/" style="display:inline-block;">
                        <img src="{{ $logoUrl }}" alt="{{ $siteName }}" style="max-height:52px; width:auto;">
                    </a>
                @else
                    <a href="/" style="font-size:1.4rem; font-weight:700; color:inherit; text-decoration:none;">
                        {{ $siteName }}
                    </a>
                @endif
            </div>

            {{-- Flash messages --}}
            @if(session('error'))
                <div class="alert alert-error">{{ session('error') }}</div>
            @endif
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            {{-- Page content (login form, register form, profile, etc.) --}}
            @yield('content')

        </div>
    </div>

    {{-- Tenant theme JS — sequential, DOMContentLoaded-safe --}}
    @if(!empty($themeJs))
        <script>
        (function() {
            var scripts = @json($themeJs);
            var idx = 0;
            function loadNext() {
                if (idx >= scripts.length) return;
                var el = document.createElement('script');
                el.src = scripts[idx++];
                el.onload = el.onerror = loadNext;
                document.body.appendChild(el);
            }
            loadNext();
        })();
        </script>
    @endif

    @stack('scripts')
</body>
</html>
