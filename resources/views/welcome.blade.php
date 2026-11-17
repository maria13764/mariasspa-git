@extends('layouts.public')

@section('title', "Maria's Spa | Appointments")

@section('content')
    <section class="mx-auto grid max-w-7xl gap-12 px-6 py-16 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:px-8 lg:py-24">
        <div>
            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-rose-500">Maria's Spa</p>
            <h1 class="mt-5 max-w-2xl font-serif text-5xl leading-tight text-[#173b3f] sm:text-6xl">A little more calm, starting with you.</h1>
            <p class="mt-6 max-w-xl text-base leading-7 text-[#35716b]">Choose a massage and request a time that works for you. No account or sign-in is needed.</p>
            <a href="{{ route('client.appointments.create') }}" class="mt-8 inline-flex rounded-full bg-[#173b3f] px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-[#24565a]">
                Book an appointment
            </a>
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="rounded-3xl bg-[#e8f2f0] p-7 sm:col-span-2">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#35716b]">For clients</p>
                <h2 class="mt-3 font-serif text-3xl">Book simply.</h2>
                <p class="mt-3 text-sm leading-6 text-[#35716b]">Tell us how to reach you, choose your massage, and send an appointment request.</p>
                <a href="{{ route('client.appointments.create') }}" class="mt-5 inline-flex text-sm font-semibold text-[#173b3f] underline decoration-[#e5b8a6] decoration-2 underline-offset-4">Make a booking</a>
            </div>
            <div class="rounded-3xl bg-[#f6eee4] p-7 sm:col-span-2">
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#7d5b43]">For therapists</p>
                <h2 class="mt-3 font-serif text-3xl">See the schedule.</h2>
                <p class="mt-3 text-sm leading-6 text-[#7d5b43]">Review appointment requests and update their status from the shared workspace.</p>
                <a href="{{ route('therapist.appointments.index') }}" class="mt-5 inline-flex text-sm font-semibold text-[#173b3f] underline decoration-[#e5b8a6] decoration-2 underline-offset-4">Open therapist workspace</a>
            </div>
        </div>
    </section>
@endsection
