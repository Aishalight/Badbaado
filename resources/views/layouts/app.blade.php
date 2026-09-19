<!DOCTYPE html>
<html lang="en" class="h-full">
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
    <meta property="og:title" content="@yield('title', 'BADBAADO')">
    <meta property="og:description" content="Connecting Hospitals, Connecting Care. Information arrives before the patient.">
    <title>@yield('title', 'BADBAADO')</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/badbaado-logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/badbaado-logo.jpg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="h-full bg-[var(--m-bg)] font-sans antialiased">
    @yield('body')
</body>
</html>