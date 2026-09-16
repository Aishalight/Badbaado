<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#081F38">
    <meta property="og:site_name" content="BADBAADO">
    <meta property="og:type" content="website">
    <meta property="og:title" content="@yield('title', 'BADBAADO — Connecting Hospitals, Connecting Care')">
    <meta property="og:description" content="Information arrives before the patient. BADBAADO connects hospitals and coordinates referrals, emergency pre-alerts and transfers.">
    <meta property="og:image" content="{{ asset('images/badbaado-logo.jpg') }}">
    <title>@yield('title', 'BADBAADO — Connecting Hospitals, Connecting Care')</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/badbaado-logo.jpg') }}">
    <link rel="apple-touch-icon" href="{{ asset('images/badbaado-logo.jpg') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    <script>
        window.BADBAADO = { publicPage: true };
    </script>
</head>
<body class="bg-[#F6F8FB] font-sans text-slate-700 antialiased">
    @include('partials.public-nav')

    @yield('public')

    @include('partials.public-footer')
</body>
</html>