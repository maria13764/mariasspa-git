<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', "Maria's Spa")</title>
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f8f5ef] font-sans text-[#173b3f] antialiased">
        <header class="border-b border-[#173b3f]/10 bg-[#f8f5ef]">
            <nav class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-6 py-5 lg:px-8">
                <a href="{{ route('home') }}" class="flex items-center gap-3 text-sm font-semibold tracking-[0.16em]">
                    <span class="flex h-10 w-10 items-center justify-center rounded-full bg-[#e5b8a6] font-serif text-xl text-[#173b3f]">M</span>
                    MARIA'S SPA
                </a>
                @auth
                    <div class="flex items-center gap-3 text-sm font-semibold">
                        <a href="{{ route('dashboard') }}" class="rounded-full px-4 py-2 text-[#35716b] hover:bg-[#e8f2f0]">Dashboard</a>
                        @can('book-appointments')
                            <a href="{{ route('client.appointments.create') }}" class="rounded-full bg-[#173b3f] px-4 py-2 text-white hover:bg-[#24565a]">Book appointment</a>
                        @endcan
                        @can('manage-therapist-appointments')
                            <a href="{{ route('therapist.appointments.index') }}" class="rounded-full bg-[#173b3f] px-4 py-2 text-white hover:bg-[#24565a]">Appointment schedule</a>
                        @endcan
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="rounded-full px-4 py-2 text-[#35716b] hover:bg-[#e8f2f0]">Sign out</button>
                        </form>
                    </div>
                @endauth
            </nav>
        </header>

        <main>
            @yield('content')
        </main>

        <footer class="mx-auto max-w-7xl px-6 py-8 text-center text-xs text-[#35716b] lg:px-8">
            Thoughtful care, one appointment at a time.
        </footer>
    </body>
</html>
