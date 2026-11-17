<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>Maria's Spa | Sign in</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|playfair-display:500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="bg-[#f8f5ef] font-sans text-[#173b3f] antialiased">
        <div class="grid min-h-screen lg:grid-cols-[0.9fr_1.1fr]">
            <aside class="relative hidden overflow-hidden bg-[#173b3f] px-10 py-12 text-white lg:flex lg:flex-col lg:justify-between">
                <div class="absolute -right-24 -top-24 h-72 w-72 rounded-full border border-emerald-100/20"></div>
                <div class="absolute -bottom-32 -left-16 h-80 w-80 rounded-full border border-rose-200/20"></div>
                <a href="/" class="relative flex items-center gap-3 text-sm font-semibold tracking-[0.16em]">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#e5b8a6] font-serif text-xl text-[#173b3f]">M</span>
                    MARIA'S SPA
                </a>
                <div class="relative max-w-md">
                    <p class="mb-5 text-xs font-semibold uppercase tracking-[0.25em] text-[#e5b8a6]">A softer way to manage care</p>
                    <h1 class="font-serif text-6xl leading-[1.05]">Your time, beautifully cared for.</h1>
                    <p class="mt-6 max-w-sm text-sm leading-6 text-emerald-50/70">A thoughtful space for appointments, therapists, and every client who walks through our doors.</p>
                </div>
                <p class="relative text-xs text-emerald-50/50">Appointments made personal.</p>
            </aside>

            <main class="flex items-center justify-center px-6 py-12 sm:px-10">
                <div class="w-full max-w-md">
                    <div class="mb-10 lg:hidden">
                        <a href="/" class="flex items-center gap-3 text-sm font-semibold tracking-[0.16em]">
                            <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#e5b8a6] font-serif text-xl">M</span>
                            MARIA'S SPA
                        </a>
                    </div>
                    {{ $slot }}
                </div>
            </main>
        </div>
    </body>
</html>
