@extends('layouts.public')

@section('title', "Book a massage | Maria's Spa")

@section('content')
    <section class="mx-auto max-w-5xl px-6 py-12 lg:px-8">
        <div class="mb-8 max-w-2xl">
            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-500">Client booking</p>
            <h1 class="mt-2 font-serif text-4xl text-[#173b3f]">Make time for yourself.</h1>
            <p class="mt-3 text-sm leading-6 text-[#35716b]">Choose your massage and a future time. We'll keep your request pending until a therapist confirms it.</p>
        </div>

        @if (session('booking_confirmation'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-5 text-emerald-900" role="status">
                <h2 class="font-semibold">Your appointment request is in.</h2>
                <p class="mt-2 text-sm">Reference #{{ session('booking_confirmation.reference') }} · {{ session('booking_confirmation.service') }} · {{ session('booking_confirmation.appointment_at') }}</p>
                <p class="mt-1 text-sm">Your request is pending therapist confirmation.</p>
            </div>
        @endif

        <form method="POST" action="{{ route('client.appointments.store') }}" class="grid gap-6 rounded-3xl bg-white p-6 shadow-xl shadow-[#173b3f]/5 sm:grid-cols-2 sm:p-9">
            @csrf

            <div>
                <label class="mb-2 block text-sm font-semibold">Booking as</label>
                <div class="rounded-xl border border-[#d7e2dd] bg-[#f8f5ef] px-4 py-2.5 text-sm">{{ auth()->user()->name }}</div>
            </div>

            <div>
                <label for="client_phone" class="mb-2 block text-sm font-semibold">Phone number</label>
                <input id="client_phone" name="client_phone" value="{{ old('client_phone') }}" required maxlength="30" autocomplete="tel" class="w-full rounded-xl border-[#d7e2dd] focus:border-[#35716b] focus:ring-[#35716b]" />
                @error('client_phone') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="mb-2 block text-sm font-semibold">Email</label>
                <div class="rounded-xl border border-[#d7e2dd] bg-[#f8f5ef] px-4 py-2.5 text-sm">{{ auth()->user()->email }}</div>
            </div>

            <div>
                <label for="service" class="mb-2 block text-sm font-semibold">Massage</label>
                <select id="service" name="service" required class="w-full rounded-xl border-[#d7e2dd] focus:border-[#35716b] focus:ring-[#35716b]">
                    <option value="">Choose a massage</option>
                    @foreach ($services as $key => $service)
                        <option value="{{ $key }}" @selected(old('service') === $key)>{{ $service['name'] }} · {{ $service['duration'] }}</option>
                    @endforeach
                </select>
                @error('service') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="therapist_id" class="mb-2 block text-sm font-semibold">Therapist</label>
                <select id="therapist_id" name="therapist_id" class="w-full rounded-xl border-[#d7e2dd] focus:border-[#35716b] focus:ring-[#35716b]">
                    <option value="">Any available therapist</option>
                    @foreach ($therapists as $therapist)
                        <option value="{{ $therapist->id }}" @selected((string) old('therapist_id') === (string) $therapist->id)>{{ $therapist->name }}</option>
                    @endforeach
                </select>
                @error('therapist_id') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="appointment_at" class="mb-2 block text-sm font-semibold">Preferred date and time</label>
                <input id="appointment_at" name="appointment_at" type="datetime-local" value="{{ old('appointment_at') }}" min="{{ $minimumAppointmentAt }}" required class="w-full rounded-xl border-[#d7e2dd] focus:border-[#35716b] focus:ring-[#35716b]" />
                @error('appointment_at') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <label for="notes" class="mb-2 block text-sm font-semibold">Anything else? <span class="font-normal text-slate-500">(optional)</span></label>
                <textarea id="notes" name="notes" rows="3" maxlength="1000" class="w-full rounded-xl border-[#d7e2dd] focus:border-[#35716b] focus:ring-[#35716b]">{{ old('notes') }}</textarea>
                @error('notes') <p class="mt-1 text-sm text-rose-700">{{ $message }}</p> @enderror
            </div>

            <div class="sm:col-span-2">
                <button type="submit" class="w-full rounded-full bg-[#173b3f] px-7 py-3.5 text-sm font-semibold text-white transition hover:bg-[#24565a] sm:w-auto">Request appointment</button>
            </div>
        </form>
    </section>
@endsection
