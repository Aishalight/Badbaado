<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#FFFFFF">
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
<body class="h-full font-sans antialiased text-slate-800 bg-[#F6F8FB]">
    @yield('body')
</body>
</html>