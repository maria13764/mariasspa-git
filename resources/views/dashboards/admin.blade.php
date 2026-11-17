<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-500">Operations</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-900">Admin dashboard</h2>
            </div>
            <span class="rounded-full bg-rose-100 px-3 py-1 text-xs font-semibold text-rose-700">Admin</span>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
        <div class="rounded-3xl bg-[#173b3f] p-8 text-white shadow-xl shadow-[#173b3f]/10">
            <p class="text-sm text-emerald-100">Welcome back, {{ auth()->user()->name }}.</p>
            <h1 class="mt-3 max-w-xl font-serif text-4xl leading-tight">Keep every appointment and client detail in rhythm.</h1>
            <p class="mt-4 max-w-lg text-sm leading-6 text-emerald-50/75">Your admin workspace will bring scheduling, therapists, services, and customer care into one calm view.</p>
        </div>

        <section aria-labelledby="weather-heading" class="mt-6 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200 sm:p-8">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-500">Local weather</p>
                    <h2 id="weather-heading" class="mt-1 text-xl font-semibold text-slate-900">Weather in {{ $weatherLocation }}</h2>
                </div>
                <span class="text-xs font-medium text-slate-500">OpenWeatherMap</span>
            </div>

            @if ($weather)
                <dl class="mt-6 grid grid-cols-2 gap-x-6 gap-y-5 sm:grid-cols-3 lg:grid-cols-5">
                    <div>
                        <dt class="text-sm text-slate-500">City</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ $weather['city'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Temperature</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ number_format($weather['temperature'], 1) }} {{ $weather['temperature_unit'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Condition</dt>
                        <dd class="mt-1 font-semibold capitalize text-slate-900">{{ $weather['condition'] }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Humidity</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ number_format($weather['humidity']) }}%</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-slate-500">Wind speed</dt>
                        <dd class="mt-1 font-semibold text-slate-900">{{ number_format($weather['wind_speed'], 1) }} {{ $weather['wind_speed_unit'] }}</dd>
                    </div>
                </dl>
            @else
                <p role="status" class="mt-6 rounded-md border border-amber-200 bg-amber-50 px-4 py-3 text-sm font-medium text-amber-900">
                    Weather is currently unavailable. The rest of your dashboard is still available.
                </p>
            @endif
        </section>

        <div class="mt-6 flex flex-wrap items-center justify-between gap-4 rounded-2xl bg-white p-6 shadow-sm ring-1 ring-slate-200">
            <a href="{{ route('admin.users.index') }}" class="transition hover:underline">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-500">Account management</p>
            <h3 class="mt-2 text-lg font-semibold text-slate-900">Manage users</h3>
            <p class="mt-2 text-sm text-slate-500">Edit names and roles, or remove accounts. Login emails and passwords stay private.</p>
            </a>
            <a href="{{ route('admin.users.create') }}" class="rounded-md bg-[#173b3f] px-4 py-2 text-sm font-semibold text-white hover:bg-[#24595d]">Add user</a>
        </div>
    </div>
</x-app-layout>