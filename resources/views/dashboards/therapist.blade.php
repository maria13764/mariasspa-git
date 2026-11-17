<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-500">Your practice</p>
                <h2 class="mt-1 text-2xl font-semibold text-slate-900">Therapist dashboard</h2>
            </div>
            <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">Spa therapist</span>
        </div>
    </x-slot>

    <div class="mx-auto max-w-7xl px-6 py-10 lg:px-8">
        <div class="rounded-3xl bg-[#f6eee4] p-8 shadow-xl shadow-[#7d5b43]/10">
            <p class="text-sm text-[#7d5b43]">Good to see you, {{ auth()->user()->name }}.</p>
            <h1 class="mt-3 max-w-xl font-serif text-4xl leading-tight text-[#173b3f]">A clearer day starts with a calmer schedule.</h1>
            <p class="mt-4 max-w-lg text-sm leading-6 text-[#7d5b43]">Review client requests and keep appointment statuses up to date in your therapist workspace.</p>
            <a href="{{ route('therapist.appointments.index') }}" class="mt-6 inline-flex rounded-full bg-[#173b3f] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#24565a]">View appointment schedule</a>
        </div>
    </div>
</x-app-layout>