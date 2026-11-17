@extends('layouts.public')

@section('title', "Therapist workspace | Maria's Spa")

@section('content')
    <section class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
        <div class="mb-8 flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-rose-500">Therapist workspace · Open access</p>
                <h1 class="mt-2 font-serif text-4xl text-[#173b3f]">Appointment schedule</h1>
                <p class="mt-3 text-sm text-[#35716b]">Review requests and keep appointment statuses up to date.</p>
            </div>
            <a href="{{ route('client.appointments.create') }}" class="rounded-full border border-[#173b3f]/20 px-5 py-3 text-sm font-semibold text-[#173b3f] hover:bg-white">New client booking</a>
        </div>

        @if (session('status'))
            <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-900" role="status">{{ session('status') }}</div>
        @endif

        @if ($appointments->isEmpty())
            <div class="rounded-3xl bg-white p-10 text-center shadow-sm">
                <h2 class="font-serif text-2xl">No appointments yet</h2>
                <p class="mt-2 text-sm text-[#35716b]">New client requests will appear here.</p>
            </div>
        @else
            <div class="overflow-hidden rounded-3xl bg-white shadow-xl shadow-[#173b3f]/5">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[760px] text-left text-sm">
                        <thead class="bg-[#e8f2f0] text-xs uppercase tracking-wider text-[#35716b]">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Client</th>
                                <th class="px-6 py-4 font-semibold">Massage</th>
                                <th class="px-6 py-4 font-semibold">Date and time</th>
                                <th class="px-6 py-4 font-semibold">Therapist</th>
                                <th class="px-6 py-4 font-semibold">Status</th>
                                <th class="px-6 py-4 font-semibold">Update</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#173b3f]/10">
                            @foreach ($appointments as $appointment)
                                <tr>
                                    <td class="px-6 py-5 font-semibold">{{ $appointment->client_name }}</td>
                                    <td class="px-6 py-5">
                                        <span class="block">{{ $services[$appointment->service]['name'] ?? $appointment->service }}</span>
                                        <span class="mt-1 block text-xs text-slate-500">{{ $services[$appointment->service]['duration'] ?? '' }}</span>
                                    </td>
                                    <td class="px-6 py-5">{{ $appointment->appointment_at->format('M j, Y · g:i A') }}</td>
                                    <td class="px-6 py-5">{{ $appointment->therapist?->name ?? 'Any available' }}</td>
                                    <td class="px-6 py-5">
                                        <span class="inline-flex rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-900">{{ $appointment->status->label() }}</span>
                                    </td>
                                    <td class="px-6 py-5">
                                        <form method="POST" action="{{ route('therapist.appointments.update-status', $appointment) }}" class="flex items-center gap-2">
                                            @csrf
                                            @method('PATCH')
                                            <label for="status-{{ $appointment->id }}" class="sr-only">Status for appointment {{ $appointment->id }}</label>
                                            <select id="status-{{ $appointment->id }}" name="status" class="rounded-lg border-[#d7e2dd] text-xs focus:border-[#35716b] focus:ring-[#35716b]">
                                                @foreach ($statuses as $status)
                                                    @unless ($status->value === 'pending')
                                                        <option value="{{ $status->value }}" @selected($appointment->status === $status)>{{ $status->label() }}</option>
                                                    @endunless
                                                @endforeach
                                            </select>
                                            <button type="submit" class="rounded-full bg-[#173b3f] px-3 py-2 text-xs font-semibold text-white hover:bg-[#24565a]">Save</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-6">{{ $appointments->links() }}</div>
        @endif
    </section>
@endsection
