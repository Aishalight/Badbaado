<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#040B15">
    <script>
        (function () {
            var key = 'badbaado-theme';
            var saved = null;
            try { saved = localStorage.getItem(key); } catch (e) {}
            var light = saved ? saved === 'light' : false;
            if (saved === null && window.matchMedia) { light = window.matchMedia('(prefers-color-scheme: light)').matches; }
            document.documentElement.classList.toggle('theme-light', light);
            var meta = document.querySelector('meta[name="theme-color"]');
            if (meta) { meta.setAttribute('content', light ? '#e9f1fa' : '#040B15'); }
        })();
    </script>
    <meta property="og:site_name" content="BADBAADO">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'BADBAADO: Connecting Hospitals, Connecting Care')">
    <meta property="og:description" content="Information arrives before the patient. BADBAADO connects hospitals and coordinates referrals, emergency pre-alerts and transfers.">
    <meta property="og:image" content="{{ asset('images/badbaado-logo.jpg') }}">
    <title>@yield('title', 'BADBAADO: Connecting Hospitals, Connecting Care')</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/badbaado-logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/badbaado-logo.jpg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    <script>
        window.BADBAADO = { publicPage: true };
    </script>
</head>
<body class="public-shell font-sans antialiased">
    <div class="m-backdrop" aria-hidden="true">
        <span class="m-orb m-orb--1"></span>
        <span class="m-orb m-orb--2"></span>
        <span class="m-mark m-mark--1"></span>
        <span class="m-mark m-mark--2"></span>
        <span class="m-mark m-mark--3"></span>
        <span class="m-mark m-mark--4"></span>
        <span class="m-mark m-mark--5"></span>
        <span class="m-mark m-mark--6"></span>
        <span class="m-grain"></span>
    </div>

    <div class="m-progress" aria-hidden="true"><div class="m-progress__bar" data-progress></div></div>

    @include('partials.public-nav')

    @yield('public')

    <button type="button" data-scroll-top class="m-scroll-top" aria-label="Back to top">
        <svg viewBox="0 0 20 20" fill="none" class="h-4.5 w-4.5"><path d="M10 16V4M4.5 9.5 10 4l5.5 5.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
    </button>

    @include('partials.public-footer')

    @stack('scripts')
</body>
</html>